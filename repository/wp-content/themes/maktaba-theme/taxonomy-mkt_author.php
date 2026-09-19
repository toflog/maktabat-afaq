<?php
/**
 * ملف جذر إلزامي (WordPress Template Hierarchy: taxonomy-{taxonomy}.php
 * يُكتشف فقط في جذر الـ Theme). المحتوى الفعلي مشترك مع صفحة الناشر.
 */

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

require get_stylesheet_directory() . "/templates/taxonomy-term-books.php";
