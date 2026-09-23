<?php
/**
 * Admin\Import\PublisherPackImporter
 *
 * انظر: دليل-تجميع-حزمة-دار-نشر.md (القسم 6)
 *
 * يستورد "حزمة ناشر" (ملف zip يحتوي manifest.json + مجلد covers/)
 * دفعة واحدة: ينشئ منتجاً لكل كتاب، يربط التصنيفات، يرفع صورة الغلاف
 * إن وُجدت، ويحسب السعر بالعملتين تلقائياً عبر PriceRecalculator.
 *
 * مكانه في الطبقات: Admin/ (وليس Domain/ ولا Integrations/) لأنه يجمع
 * بين BookRepository (Domain) + PriceRecalculator (Admin) + دوال ملفات
 * ووسائط ووردبريس الأساسية (wp_insert_attachment، ZipArchive) — بالضبط
 * نفس مبرر PriceRecalculator نفسه (انظر تعليقه التوضيحي).
 *
 * الاستدعاء (من سكربت WP-CLI):
 *   wp eval "\Maktaba\Core\Admin\Import\PublisherPackImporter::importFromZip('/path/to/pack.zip');"
 */

namespace Maktaba\Core\Admin\Import;

use Maktaba\Core\Domain\Book\BookRepository;
use Maktaba\Core\Admin\Pricing\PriceRecalculator;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class PublisherPackImporter {

    /**
     * @param string $zip_path مسار الملف المضغوط (على القرص، داخل الحاوية)
     * @return array{created:int, updated:int, errors:string[]}
     */
    public static function importFromZip( $zip_path ) {
        $result = array( "created" => 0, "updated" => 0, "errors" => array() );

        if ( ! file_exists( $zip_path ) ) {
            $result["errors"][] = "الملف غير موجود: {$zip_path}";
            return $result;
        }

        if ( ! class_exists( "ZipArchive" ) ) {
            $result["errors"][] = "امتداد ZipArchive غير متوفر في PHP — لا يمكن فك الضغط.";
            return $result;
        }

        $extract_dir = self::makeTempDir();
        if ( ! $extract_dir ) {
            $result["errors"][] = "تعذّر إنشاء مجلد مؤقت لفك الضغط.";
            return $result;
        }

        $zip = new \ZipArchive();
        if ( true !== $zip->open( $zip_path ) ) {
            $result["errors"][] = "تعذّر فتح الملف المضغوط — تأكد أنه ملف zip صالح.";
            return $result;
        }
        $zip->extractTo( $extract_dir );
        $zip->close();

        $manifest_path = self::findManifest( $extract_dir );
        if ( ! $manifest_path ) {
            $result["errors"][] = "لم يُعثَر على manifest.json داخل الحزمة.";
            self::cleanup( $extract_dir );
            return $result;
        }

        $manifest = json_decode( file_get_contents( $manifest_path ), true );
        if ( ! is_array( $manifest ) || empty( $manifest["books"] ) || ! is_array( $manifest["books"] ) ) {
            $result["errors"][] = "manifest.json غير صالح، أو لا يحتوي مصفوفة \"books\".";
            self::cleanup( $extract_dir );
            return $result;
        }

        $publisher   = isset( $manifest["publisher"] ) ? trim( (string) $manifest["publisher"] ) : "";
        $manifest_dir = dirname( $manifest_path );

        foreach ( $manifest["books"] as $index => $book ) {
            $label = isset( $book["title"] ) ? $book["title"] : "كتاب رقم " . ( $index + 1 );

            $missing = self::validateRequiredFields( $book, $publisher );
            if ( $missing ) {
                $result["errors"][] = "«{$label}» تم تجاوزه — حقول ناقصة: " . implode( "، ", $missing );
                continue;
            }

            try {
                $outcome = self::importOneBook( $book, $publisher, $manifest_dir );
                if ( "updated" === $outcome ) {
                    $result["updated"]++;
                } else {
                    $result["created"]++;
                }
            } catch ( \Exception $e ) {
                $result["errors"][] = "«{$label}» فشل الاستيراد: " . $e->getMessage();
            }
        }

        self::cleanup( $extract_dir );

        return $result;
    }

    /**
     * @return string[] أسماء الحقول الإلزامية الناقصة (فارغة المصفوفة = كل شيء موجود)
     */
    private static function validateRequiredFields( $book, $publisher ) {
        $missing = array();

        if ( "" === $publisher ) {
            $missing[] = "publisher (أعلى الملف)";
        }
        foreach ( array( "title", "author", "category", "description" ) as $field ) {
            if ( empty( $book[ $field ] ) ) {
                $missing[] = $field;
            }
        }

        return $missing;
    }

    /**
     * @return string "created" أو "updated"
     */
    private static function importOneBook( $book, $publisher, $manifest_dir ) {
        $isbn = isset( $book["isbn"] ) ? trim( (string) $book["isbn"] ) : "";

        // منع التكرار: كتاب له نفس ISBN موجود مسبقاً → تحديث بدل إنشاء.
        $existing_id = $isbn ? self::findByIsbn( $isbn ) : 0;

        if ( $existing_id ) {
            $book_id = $existing_id;
            wp_update_post( array(
                "ID"           => $book_id,
                "post_title"   => $book["title"],
                "post_content" => $book["description"],
            ) );
            $outcome = "updated";
        } else {
            $book_id = wp_insert_post( array(
                "post_type"    => "product",
                "post_status"  => "publish",
                "post_title"   => $book["title"],
                "post_content" => $book["description"],
            ), true );

            if ( is_wp_error( $book_id ) ) {
                throw new \Exception( $book_id->get_error_message() );
            }
            $outcome = "created";
        }

        wp_set_object_terms( $book_id, $book["author"], "mkt_author" );
        wp_set_object_terms( $book_id, $publisher, "mkt_publisher" );
        wp_set_object_terms( $book_id, $book["category"], "product_cat" );

        update_post_meta( $book_id, "_stock_status", "instock" );
        update_post_meta( $book_id, "_manage_stock", "no" );
        update_post_meta( $book_id, "_sold_individually", "no" );
        update_post_meta( $book_id, "_virtual", "no" );
        update_post_meta( $book_id, "_downloadable", "no" );

        BookRepository::setIsbn( $book_id, $isbn );
        BookRepository::setLanguage( $book_id, isset( $book["language"] ) ? $book["language"] : "ar" );
        BookRepository::setPageCount( $book_id, isset( $book["page_count"] ) ? (int) $book["page_count"] : 0 );
        BookRepository::setPublicationYear( $book_id, isset( $book["publication_year"] ) ? (int) $book["publication_year"] : 0 );

        $official_price_egp = isset( $book["official_price_egp"] ) ? (float) $book["official_price_egp"] : 0;
        BookRepository::setOfficialPriceEgp( $book_id, $official_price_egp );
        if ( $official_price_egp > 0 ) {
            PriceRecalculator::recalculateOne( $book_id );
        }

        if ( ! empty( $book["cover_image"] ) ) {
            $image_path = $manifest_dir . "/" . ltrim( $book["cover_image"], "/" );
            if ( file_exists( $image_path ) ) {
                self::attachCoverImage( $book_id, $image_path );
            } else {
                throw new \Exception( "صورة الغلاف المحدَّدة غير موجودة في الحزمة: " . $book["cover_image"] );
            }
        }

        return $outcome;
    }

    /**
     * @return int معرّف المنتج إن وُجد بنفس ISBN، أو 0
     */
    private static function findByIsbn( $isbn ) {
        $query = new \WP_Query( array(
            "post_type"      => "product",
            "post_status"    => "any",
            "posts_per_page" => 1,
            "fields"         => "ids",
            "no_found_rows"  => true,
            "meta_query"     => array(
                array( "key" => "_mkt_isbn", "value" => $isbn ),
            ),
        ) );

        return $query->posts ? (int) $query->posts[0] : 0;
    }

    private static function attachCoverImage( $book_id, $image_path ) {
        require_once ABSPATH . "wp-admin/includes/image.php";
        require_once ABSPATH . "wp-admin/includes/file.php";
        require_once ABSPATH . "wp-admin/includes/media.php";

        $filename = basename( $image_path );
        $upload   = wp_upload_bits( $filename, null, file_get_contents( $image_path ) );

        if ( ! empty( $upload["error"] ) ) {
            throw new \Exception( "فشل رفع صورة الغلاف: " . $upload["error"] );
        }

        $filetype   = wp_check_filetype( $filename, null );
        $attachment = array(
            "post_mime_type" => $filetype["type"],
            "post_title"     => sanitize_file_name( $filename ),
            "post_content"   => "",
            "post_status"    => "inherit",
        );

        $attach_id = wp_insert_attachment( $attachment, $upload["file"], $book_id );
        $attach_data = wp_generate_attachment_metadata( $attach_id, $upload["file"] );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        set_post_thumbnail( $book_id, $attach_id );
    }

    private static function findManifest( $dir ) {
        if ( file_exists( $dir . "/manifest.json" ) ) {
            return $dir . "/manifest.json";
        }
        // بعض أدوات الضغط تُنشئ مجلداً فرعياً بنفس اسم الحزمة داخلها.
        foreach ( glob( $dir . "/*", GLOB_ONLYDIR ) as $subdir ) {
            if ( file_exists( $subdir . "/manifest.json" ) ) {
                return $subdir . "/manifest.json";
            }
        }
        return null;
    }

    private static function makeTempDir() {
        $base = sys_get_temp_dir() . "/mkt-pack-import-" . uniqid();
        if ( wp_mkdir_p( $base ) ) {
            return $base;
        }
        return null;
    }

    private static function cleanup( $dir ) {
        if ( ! $dir || ! is_dir( $dir ) ) {
            return;
        }
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS ),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ( $files as $file ) {
            $file->isDir() ? rmdir( $file->getRealPath() ) : unlink( $file->getRealPath() );
        }
        rmdir( $dir );
    }
}
