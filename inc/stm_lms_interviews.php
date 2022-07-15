<?php
add_action( 'wp_ajax_nopriv_stm_lms_get_interviews', 'stm_lms_get_interviews' );
add_action( 'wp_ajax_stm_lms_get_interviews', 'stm_lms_get_interviews' );
function stm_lms_get_interviews()
{
    check_ajax_referer( 'getUserInterviewsNonce', 'nonce' );

    $userId = $_POST['userId'];
    $isInstructor = $_POST['isInstructor'];

    if($isInstructor == "true") {
        $instructorOrStudent = 'instructor';
    } else {
        $instructorOrStudent = 'student';
    }

    $args = array(
        'post_type' => 'stm-interviews',
        'posts_per_page' => -1,
        'meta_key' => $instructorOrStudent,
        'meta_query' => array(
            array(
                'key' => $instructorOrStudent,
                'value' => $userId,
                'compare' => '=',
            )
        )
    );
    $interviews = [];
    $query = new WP_Query( $args );
    if(!empty($query->posts)) {
        foreach ($query->posts as $my_post) {
            $studentId = get_post_meta($my_post->ID, 'student', true);
            $instructorId = get_post_meta($my_post->ID, 'instructor', true);
            $courseId = get_post_meta($my_post->ID, 'course', true);
            $dateUnix = get_post_meta($my_post->ID, 'date', true);
            $comment = get_post_meta($my_post->ID, 'comment', true);

            $studentName = get_user_meta($studentId)["nickname"][0];
            $instructorName = get_user_meta($instructorId)["nickname"][0];
            $courseName = get_the_title($courseId);
            $vote = get_post_meta($my_post->ID, 'vote', true);
            $status = get_post_meta($my_post->ID, 'status', true);

            if($status === 'on' || $status === '1') {
                $status = true;
            } else {
                $status = false;
            }

            $date = date("d F Y", $dateUnix / 1000);

            $object = new stdClass();
            $object->id = $my_post->ID;
            $object->title = $my_post->post_title;
            $object->student = $studentName;
            $object->instructorName = $instructorName;
            $object->date = $date;
            $object->status = $status;
            $object->vote = $vote;
            $object->course = $courseName;
            $object->comment = $comment;

            $interviews[] = $object;
        }
    }

    wp_send_json( $interviews );
}