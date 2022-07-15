<?php
add_action( 'wp_ajax_nopriv_stm_get_courses_users_interview', 'stm_get_courses_users_interview' );
add_action( 'wp_ajax_stm_get_courses_users_interview', 'stm_get_courses_users_interview' );
function stm_get_courses_users_interview()
{
    check_ajax_referer( 'getUserInterviewsNonce', 'nonce' );

    $courses = [];
    $students = [];
    $args = array(
        'post_type' => 'stm-courses',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    );
    $query = new WP_Query( $args );
    if(!empty($query->posts)) {
        foreach ($query->posts as $my_post) {
            $courses[$my_post->ID] = $my_post->post_title;
        }
    }

    $args = array(
        'role' => 'subscriber',
    );
    $userStudents = get_users( $args );
    if(!empty($userStudents)) {
        foreach ($userStudents as $user) {
            $students[$user->data->ID] = $user->data->user_login;
        }
    }

    $response = [
        'courses' => $courses,
        'students' => $students
    ];

    wp_send_json($response);
}