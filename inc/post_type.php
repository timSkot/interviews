<?php
if (!defined('ABSPATH')) exit; //Exit if accessed directly

function custom_post_type() {

    $labels = array(
        'name'                => _x( 'Interviews', 'Post Type General Name', 'masterstudy-child' ),
        'singular_name'       => _x( 'Interview', 'Post Type Singular Name', 'masterstudy-child' ),
        'menu_name'           => __( 'Interviews', 'masterstudy-child' ),
        'parent_item_colon'   => __( 'Parent Interview', 'masterstudy-child' ),
        'all_items'           => __( 'All Interviews', 'masterstudy-child' ),
        'view_item'           => __( 'View Interview', 'masterstudy-child' ),
        'add_new_item'        => __( 'Add New Interview', 'masterstudy-child' ),
        'add_new'             => __( 'Add New', 'masterstudy-child' ),
        'edit_item'           => __( 'Edit Interview', 'masterstudy-child' ),
        'update_item'         => __( 'Update Interview', 'masterstudy-child' ),
        'search_items'        => __( 'Search Interview', 'masterstudy-child' ),
        'not_found'           => __( 'Not Found', 'masterstudy-child' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'masterstudy-child' ),
    );

    $args = array(
        'label'               => __( 'interview', 'masterstudy-child' ),
        'description'         => __( 'Interviews', 'masterstudy-child' ),
        'labels'              => $labels,
        'public' => false,
        'exclude_from_search' => true,
        'publicly_queryable' => false,
        'show_in_menu' => 'admin.php?page=stm-lms-settings',
        'supports' => array('title', 'editor', 'thumbnail', 'revisions', 'author'),
        'capability_type' => 'post',
        'hierarchical'        => false,
        'show_ui'             => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 5,
        'can_export'          => true,
        'has_archive'         => true,
        'show_in_rest' => true,
    );

    // Registering your Custom Post Type
    register_post_type( 'stm-interviews', $args );

    $post_type_data = get_post_type_object('stm-interviews');

    add_submenu_page(
        'stm-lms-settings',
        $post_type_data->label,
        $post_type_data->label,
        'manage_options',
        '/edit.php?post_type=stm-interviews'
    );

}

add_action( 'init', 'custom_post_type', 0 );

add_filter(
    'stm_wpcfto_boxes',
    function ( $boxes ) {

        $data_boxes = array(
            'stm_interviews'   => array(
                'post_type' => array( 'stm-interviews' ),
                'label'     => esc_html__( 'Interviews Settings', 'masterstudy-child' ),
            ),
        );

        $boxes = array_merge( $data_boxes, $boxes );

        return $boxes;
    }
);

add_filter(
    'stm_wpcfto_fields',
    function ( $fields ) {
        $userInstructors = getUsersByRole('stm_lms_instructor');
        $userStudents = getUsersByRole('subscriber');
        $instructors = [];
        $students = [];
        $courses = [];

        $args = array(
            'post_type' => 'stm-courses',
            'posts_per_page' => -1,
        );
        $query = new WP_Query( $args );
        if(!empty($query->posts)) {
            foreach ($query->posts as $my_post) {
                $courses[$my_post->ID] = $my_post->post_title;
            }
        }

        if(!empty($userInstructors)) {
            foreach ($userInstructors as $user) {
                $instructors[$user->data->ID] = $user->data->user_login;
            }
        }

        if(!empty($userStudents)) {
            foreach ($userStudents as $user) {
                $students[$user->data->ID] = $user->data->user_login;
            }
        }

        $data_fields = array(
            'stm_interviews'      => array(
                'section_interviews_settings' => array(
                    'name'   => esc_html__( 'Interviews Settings', 'masterstudy-child' ),
                    'fields' => array(
                        'instructor'   => array(
                            'type'     => 'select',
                            'label'    => esc_html__( 'Instructor', 'masterstudy-child' ),
                            'options' => $instructors,
                            'value'   => '',
                        ),
                        'student'         => array(
                            'type'     => 'select',
                            'label'    => esc_html__( 'Student', 'masterstudy-child' ),
                            'options' => $students,
                            'value'   => '',
                        ),
                        'course' => array(
                            'type'     => 'select',
                            'label'    => esc_html__( 'Course', 'masterstudy-child' ),
                            'options' => $courses,
                            'value'   => '',
                        ),
                        'date'   => array(
                            'type'       => 'date',
                            'label'      => esc_html__( 'Date', 'masterstudy-child' ),
                        ),
                        'vote'    => array(
                            'type'     => 'select',
                            'label' => esc_html__( 'Vote', 'masterstudy-child' ),
                            'options' => array(
                                '10' => '10',
                                '9' => '9',
                                '8' => '8',
                                '7' => '7',
                                '6' => '6',
                                '5' => '5',
                                '4' => '4',
                                '3' => '3',
                                '2' => '2',
                                '1' => '1',
                            ),
                            'value'   => '1',
                        ),
                        'status' => array(
                            'type'  => 'checkbox',
                            'label' => esc_html__( 'Status (Passed or not)', 'masterstudy-child' ),
                        ),
                        'comment' => array(
                            'type'  => 'text',
                            'label' => esc_html__( 'Comment', 'masterstudy-child' ),
                        ),
                    ),
                ),
            ),
        );

        $fields = array_merge( $data_fields, $fields );

        return $fields;
    }
);

function getUsersByRole($role)
{
    $args = array(
        'role'    => $role,
    );
    return get_users( $args );
}