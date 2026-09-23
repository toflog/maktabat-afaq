<?php
/**
 * ملف جذر إلزامي (WordPress Template Hierarchy لا يكتشف front-page.php
 * إلا في جذر الـ Theme مباشرة) — المحتوى الفعلي منظَّم في templates/
 * لتفادي ازدحام الجذر مع نمو عدد القوالب (Section 6/7 من المخطط).
 */

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

require get_stylesheet_directory() . "/templates/front-page.php";
