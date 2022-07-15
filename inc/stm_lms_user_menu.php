<?php

class STM_LMS_User_Child extends STM_LMS_User_Menu
{
    public static function stm_lms_user_menu_display( $current_user, $lms_template_current, $object_id ) {
        $menu_items                  = self::user_menu_float_items( $current_user, $lms_template_current, $object_id );
        $is_instructor               = STM_LMS_Instructor::is_instructor( $current_user['id'] );
        $sorting_menu                = self::get_menu_options( STM_LMS_Options::get_option( 'sorting_the_menu' ) );
        $sorting_float_menu_main     = self::get_menu_options( STM_LMS_Options::get_option( 'sorting_float_menu_main' ) );
        $sorting_float_menu_learning = self::get_menu_options( STM_LMS_Options::get_option( 'sorting_float_menu_learning' ) );
        $sorting_the_menu_student    = self::get_menu_options( STM_LMS_Options::get_option( 'sorting_the_menu_student' ) );

        /* If user is instructor*/
        if ( $is_instructor ) {
            $menu_items = self::remove_settings_from_menu( $menu_items );

            if ( self::float_menu_enabled() ) {
                if ( $sorting_float_menu_main ) {
                    foreach ( $menu_items as $menu_item ) {
                        if ( isset( $menu_item['id'] ) && 'divider' === $menu_item['id'] ) {
                            $divider = $menu_item;
                        }
                    }

                    $menu_items = apply_filters( 'stm_lms_sorted_menu', $sorting_float_menu_main, 'sorting_float_menu_main', $current_user, $lms_template_current, $object_id );

                    array_push( $menu_items, $divider );

                    $sorting_float_menu_learning = apply_filters( 'stm_lms_sorted_menu', $sorting_float_menu_learning, 'sorting_float_menu_learning', $current_user, $lms_template_current, $object_id );

                    $menu_items = array_merge( $menu_items, $sorting_float_menu_learning );
                }
            } else {
                if ( $sorting_menu ) {
                    $menu_items = apply_filters( 'stm_lms_sorted_menu', $sorting_menu, 'sorting_the_menu', $current_user, $lms_template_current, $object_id );
                }
            }
        } else {
            /* If float side menu is off*/
            if ( ! self::float_menu_enabled() && $sorting_the_menu_student || ! self::float_menu_enabled() && is_array( $sorting_the_menu_student ) ) {
                $menu_items = apply_filters( 'stm_lms_sorted_menu', $sorting_the_menu_student, 'sorting_the_menu_student', $current_user, $lms_template_current, $object_id );
            }
            /* If float side menu is on*/
            if ( self::float_menu_enabled() && $sorting_float_menu_learning || self::float_menu_enabled() && is_array( $sorting_float_menu_learning ) ) {
                $menu_items = apply_filters( 'stm_lms_sorted_menu', $sorting_float_menu_learning, 'sorting_float_menu_learning', $current_user, $lms_template_current, $object_id );
            }
        }

        return $menu_items;
    }

    public static function user_menu_float_items( $current_user, $lms_template_current, $object_id ) {
        $settings      = get_option( 'stm_lms_settings', array() );
        $is_instructor = STM_LMS_Instructor::is_instructor( $current_user['id'] );

        $menus = array();

        /*Instructor fields*/
        if ( $is_instructor ) {
            $menus[] = array(
                'order'                => 10,
                'id'                   => 'dashboard',
                'current_user'         => $current_user,
                'lms_template_current' => $lms_template_current,
                'lms_template'         => 'stm-lms-user',
                'menu_title'           => esc_html__( 'Dashboard', 'masterstudy-child' ),
                'label'                => esc_html__( 'Dashboard', 'masterstudy-child' ),
                'menu_icon'            => 'fa-tachometer-alt',
                'menu_url'             => STM_LMS_User::login_page_url(),
                'is_active'            => ( ! empty( $settings['user_url'] ) ) ? $settings['user_url'] : '',
                'menu_place'           => 'main',
            );

            $menus[] = array(
                'id'    => 'divider',
                'order' => 90,
                'type'  => 'divider',
                'title' => esc_html__( 'Learning area', 'masterstudy-child' ),
            );
        }

        $menus[] = array(
            'order'                => 100,
            'id'                   => 'enrolled_courses',
            'current_user'         => $current_user,
            'lms_template_current' => $lms_template_current,
            'lms_template'         => 'stm-lms-user-courses',
            'menu_title'           => esc_html__( 'Enrolled Courses', 'masterstudy-child' ),
            'label'                => esc_html__( 'Enrolled Courses', 'masterstudy-child' ),
            'menu_icon'            => 'fa-book',
            'menu_url'             => STM_LMS_User::enrolled_courses_url(),
            'is_active'            => ( ! $is_instructor && ( ! empty( $settings['user_url'] && ! empty( $object_id ) && $settings['user_url'] == $object_id ) ) ),
            'menu_place'           => 'learning',
        );

        $menus[] = array(
            'order'                => ( $is_instructor ) ? 90 : 100 ,
            'id'                   => 'interviews',
            'current_user'         => $current_user,
            'lms_template_current' => $lms_template_current,
            'lms_template'         => 'stm-lms-user-interview',
            'menu_title'           => esc_html__( 'Interviews', 'masterstudy-child' ),
            'label'                => esc_html__( 'Interviews', 'masterstudy-child' ),
            'menu_icon'            => 'fa-comment-dots',
            'menu_url'             => STM_LMS_Page_Router_Child::interviews_url(),
            'is_active'            => ( ! $is_instructor && ( ! empty( $settings['user_url'] && ! empty( $object_id ) && $settings['user_url'] == $object_id ) ) ),
            'menu_place'           => 'learning',
        );

        if ( ! self::float_menu_enabled() ) {
            $menus[] = array(
                'order'                => 110,
                'id'                   => 'settings',
                'current_user'         => $current_user,
                'lms_template_current' => $lms_template_current,
                'lms_template'         => 'stm-lms-user-settings',
                'menu_title'           => esc_html__( 'Settings', 'masterstudy-child' ),
                'label'                => esc_html__( 'Settings', 'masterstudy-child' ),
                'menu_icon'            => 'fa-cog',
                'menu_url'             => STM_LMS_User::settings_url(),
                'menu_place'           => 'main',
            );
        }

        if ( apply_filters( 'float_menu_item_enabled', true ) ) {
            $menus[] = array(
                'order'                => 120,
                'id'                   => 'messages',
                'current_user'         => $current_user,
                'lms_template_current' => $lms_template_current,
                'lms_template'         => 'stm-lms-user-chats',
                'menu_title'           => esc_html__( 'Messages', 'masterstudy-child' ),
                'label'                => esc_html__( 'Messages', 'masterstudy-child' ),
                'menu_icon'            => 'fa-envelope',
                'menu_url'             => STM_LMS_Chat::chat_url(),
                'badge_count'          => STM_LMS_Chat::user_new_messages( $current_user['id'] ),
                'menu_place'           => 'learning',
            );
        }

        $menus[] = array(
            'order'                => 130,
            'id'                   => 'favorite_courses',
            'current_user'         => $current_user,
            'lms_template_current' => $lms_template_current,
            'lms_template'         => 'stm-lms-wishlist',
            'menu_title'           => esc_html__( 'Favorite Courses', 'masterstudy-child' ),
            'label'                => esc_html__( 'Favorite Courses', 'masterstudy-child' ),
            'menu_icon'            => 'fa-star',
            'menu_url'             => STM_LMS_User::wishlist_url(),
            'is_active'            => ( ! empty( $settings['wishlist_url'] ) ) ? $settings['wishlist_url'] : '',
            'menu_place'           => 'learning',
        );
        $menus[] = array(
            'order'                => 140,
            'id'                   => 'enrolled_quizzes',
            'current_user'         => $current_user,
            'lms_template_current' => $lms_template_current,
            'lms_template'         => 'stm-lms-user-quizzes',
            'menu_title'           => esc_html__( 'Enrolled Quizzes', 'masterstudy-child' ),
            'label'                => esc_html__( 'Enrolled Quizzes', 'masterstudy-child' ),
            'menu_icon'            => 'fa-question',
            'menu_url'             => STM_LMS_User::enrolled_quizzes_url(),
            'menu_place'           => 'learning',
        );
        $menus[] = array(
            'order'                => 150,
            'id'                   => 'my_orders',
            'current_user'         => $current_user,
            'lms_template_current' => $lms_template_current,
            'lms_template'         => 'stm-lms-user-orders',
            'menu_title'           => esc_html__( 'My Orders', 'masterstudy-child' ),
            'label'                => esc_html__( 'My Orders', 'masterstudy-child' ),
            'menu_icon'            => 'fa-shopping-basket',
            'menu_url'             => STM_LMS_User::my_orders_url(),
            'menu_place'           => 'learning',
        );

        if ( STM_LMS_Subscriptions::subscription_enabled() ) {
            $menus[] = array(
                'order'                => 125,
                'id'                   => 'memberships',
                'current_user'         => $current_user,
                'lms_template_current' => $lms_template_current,
                'lms_template'         => 'stm-lms-user-pmp',
                'menu_title'           => esc_html__( 'Memberships', 'masterstudy-child' ),
                'label'                => esc_html__( 'Memberships', 'masterstudy-child' ),
                'menu_icon'            => 'fa-address-card',
                'menu_url'             => STM_LMS_User::my_pmpro_url(),
                'menu_place'           => 'learning',
            );
        }

        $menus = apply_filters( 'stm_lms_float_menu_items', $menus, $current_user, $lms_template_current, $object_id );

        array_multisort( array_column( $menus, 'order' ), SORT_ASC, $menus );

        return $menus;
    }
}