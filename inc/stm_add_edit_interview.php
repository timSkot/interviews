<?php
add_action( 'wp_ajax_nopriv_stm_add_interview', 'stm_add_interview' );
add_action( 'wp_ajax_stm_add_interview', 'stm_add_interview' );
function stm_add_interview()
{
    check_ajax_referer( 'getUserInterviewsNonce', 'nonce' );
    $data = json_decode(stripslashes($_POST['data']), true);

    $courseName = $data['course']['label'];
    $courseId = $data['course']['code'];
    $studentName = $data['student']['label'];
    $studentId = $data['student']['code'];
    $interviewTitle = $data['title'];
    $date = strtotime($data['date']) * 1000;
    $vote = $data['vote'];
    $status = $data['status'];
    $comment = $data['comment'];
    $instructorId = $_POST['userId'];
    // if postId exist then edit post
    $postId = $data['postId'];

    $post_data = array(
        'post_title'    => $interviewTitle,
        'post_content'  => '',
        'post_status'   => 'publish',
        'post_type'     => 'stm-interviews',
        'post_author'   => $instructorId,
        'meta_input'   => array(
            'instructor' => $instructorId,
            'student' => $studentId,
            'course' => $courseId,
            'date' => $date,
            'vote' => $vote,
            'status' => $status,
            'comment' => $comment,
        ),
    );

    if($postId) {
        $post_data['ID'] = $postId;
        $updated_post = wp_update_post( wp_slash( $post_data ) );

        $r = array(
            'status'  => 'success',
            'message' => esc_html__( 'Interview updated successfully.', 'masterstudy-child' ),
        );

        if ( empty( $updated_post ) ) {
            $r['status']  = 'error';
            $r['message'] = esc_html__( 'Something went wrong.', 'masterstudy-child' );
            wp_send_json( $r );
        }

        wp_send_json( $r );
    } else {
        $post_id = wp_insert_post( $post_data );

        $r = array(
            'status'  => 'success',
            'message' => esc_html__( 'Interview added successfully.', 'masterstudy-child' ),
        );

        if ( empty( $post_id ) ) {
            $r['status']  = 'error';
            $r['message'] = esc_html__( 'Something went wrong.', 'masterstudy-child' );
            wp_send_json( $r );
        }

        wp_send_json( $r );
    }
}