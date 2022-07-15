<?php
    const STM_THEME_VERSION_Child = 1.1;
	add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
	function theme_enqueue_styles() {
	    // Styles
		// Theme main stylesheet
		wp_enqueue_style( 'theme-style', get_stylesheet_uri(), null, STM_THEME_VERSION, 'all' );
	}

$inc_path = get_stylesheet_directory() . '/inc';

require_once $inc_path .'/stm_lms_user_menu.php';
require_once $inc_path .'/page_routes_child.php';
require_once $inc_path .'/stm_lms_interviews.php';
require_once $inc_path .'/post_type.php';
require_once $inc_path .'/stm_get_courses_users.php';
require_once $inc_path .'/stm_add_edit_interview.php';