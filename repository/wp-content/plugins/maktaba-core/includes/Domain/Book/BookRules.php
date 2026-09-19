<?php
/**
 * Domain\Book\BookRules
 *
 * قواعد تحقّق (Validation) قبل حفظ/نشر كتاب — لا تلمس القاعدة أو WordPress
 * مباشرة، تُستدعى من Integrations/WooCommerce/ProductAsBookBridge.php
 * عند حفظ المنتج.
 */

namespace Maktaba\Core\Domain\Book;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class BookRules {

    /**
     * @param array $data حقول الكتاب المُدخلة (title, isbn, selling_price, ...)
     * @return string[] قائمة أخطاء التحقق — مصفوفة فارغة تعني صلاحية البيانات
     */
    public static function validate( array $data ) {
        $errors = array();

        if ( empty( $data["title"] ) ) {
            $errors[] = "عنوان الكتاب إلزامي.";
        }

        if ( empty( $data["author_id"] ) ) {
            $errors[] = "المؤلف إلزامي لكل كتاب.";
        }

        if ( empty( $data["category_id"] ) ) {
            $errors[] = "التصنيف إلزامي لكل كتاب.";
        }

        if ( isset( $data["selling_price"] ) && (float) $data["selling_price"] <= 0 ) {
            $errors[] = "سعر البيع يجب أن يكون أكبر من صفر.";
        }

        // قاعدة عمل: لا يُنشر كتاب بسعر بيع أقل من التكلفة الفعلية إن كانت معروفة
        // (تحذير تشغيلي وليس منعاً تقنياً صارماً — يُترك القرار النهائي للإدارة)
        if ( isset( $data["selling_price"], $data["actual_cost"] )
            && (float) $data["actual_cost"] > 0
            && (float) $data["selling_price"] < (float) $data["actual_cost"]
        ) {
            $errors[] = "تنبيه: سعر البيع أقل من التكلفة الفعلية — تحقق قبل النشر.";
        }

        return $errors;
    }

    public static function isValid( array $data ) {
        return empty( self::validate( $data ) );
    }
}
