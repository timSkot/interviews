<?php if (!defined('ABSPATH')) exit; //Exit if accessed directly ?>

<?php get_header();
$lms_current_user = STM_LMS_User::get_current_user('', true, true);
$is_instructor = STM_LMS_Instructor::is_instructor( $lms_current_user['id'] );
do_action('stm_lms_template_main');
stm_lms_register_style('user_info_top');
wp_enqueue_script( 'interviews', get_stylesheet_directory_uri().'/assets/js/interviews.js', array( 'vue.js', 'vue-resource.js' ) );

wp_enqueue_style( 'vue-select', 'https://unpkg.com/vue-select@latest/dist/vue-select.css', null, STM_THEME_VERSION, 'all' );
wp_enqueue_style( 'vue-datepicker', 'https://unpkg.com/vue2-datepicker/index.css', null, STM_THEME_VERSION, 'all' );

wp_enqueue_script( 'vue-select', 'https://unpkg.com/vue-select@latest', array( 'vue.js', 'vue-resource.js' ) );
wp_enqueue_script( 'vue-datepicker', 'https://unpkg.com/vue2-datepicker/index.min.js', array( 'vue.js', 'vue-resource.js' ) );

STM_LMS_Templates::show_lms_template('manage_course/forms/js/editor');

wp_add_inline_script( 'interviews', 'const userData = ' . json_encode( [
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'userId' => $lms_current_user['id'],
        'nonce' => wp_create_nonce('getUserInterviewsNonce'),
        'isInstructor' => $is_instructor
    ] ) );
?>
    <div class="stm-lms-wrapper" id="stm_lms_interviews">
        <div class="container">

			<?php do_action('stm_lms_admin_after_wrapper_start', STM_LMS_User::get_current_user()); ?>
            <div v-if="showList" class="stm-lms-interviews_list">
                <div class="stm-lms-interviews_top">
                    <h2><?php esc_html_e( 'Interviews', 'masterstudy-child' ); ?></h2>

                    <?php if($is_instructor): ?>
                        <button class="btn btn-default create-interviews-btn" @click="createInterview">
                            <i class="fa fa-plus"></i> <?php esc_html_e( 'Add New Interview', 'masterstudy-child' ); ?>
                        </button>
                    <?php endif; ?>
                </div>
                <div class="stm-lms-interviews_wr">
                    <div class="stm-lms-interviews_wr__top">
                        <div class="stm_lms_interviews_wr__sort">
                            <span class="sort_label heading_font"><?php esc_html_e( 'Sort By:', 'masterstudy-child' ); ?></span>
                            <select class="form-control disable-select" @change="interviewsSort(interviews)">
                                <option value="status_passed"><?php esc_html_e( 'Status: Passed', 'masterstudy-child' ); ?></option>
                                <option value="status_nonpassed"><?php esc_html_e( 'Status: Non Passed', 'masterstudy-child' ); ?></option>
                                <option value="status_pending"><?php esc_html_e( 'Status: Pending', 'masterstudy-child' ); ?></option>
                            </select>
                        </div>
                        <div class="stm-lms-interviews_wr__statics">
                            <div class="stm-lms-interviews_wr__item">
                                <i class="fa fa-list"></i>
                                <span class="label">
                                    <?php esc_html_e( 'Total', 'masterstudy-child' ); ?>: {{ interviews.length }}
                                </span>
                            </div>
                            <div class="stm-lms-interviews_wr__item">
                                <i class="fa fa-times non-passed"></i>
                                <span class="label">
                                    <?php esc_html_e( 'Not passed', 'masterstudy-child' ); ?>: {{ notPassedCount }}
                                </span>
                            </div>
                            <div class="stm-lms-interviews_wr__item">
                                <i class="fa fa-check passed"></i>
                                <span class="label">
                                   <?php esc_html_e( 'Passed', 'masterstudy-child' ); ?>: {{ passedCount }}
                                </span>
                            </div>
                            <div class="stm-lms-interviews_wr__item">
                                <i class="fa fa-clock pending"></i>
                                <span class="label">
                                    <?php esc_html_e( 'Pending', 'masterstudy-child' ); ?>: {{ pendingCount }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="stm-lms-interviews_wr__table">
                        <table class="table">
                        <thead>
                            <tr>
                                <th v-if="!userData.isInstructor"><?php esc_html_e( 'Instructor', 'masterstudy-child' ); ?></th>
                                <th v-else><?php esc_html_e( 'Student', 'masterstudy-child' ); ?></th>
                                <th><?php esc_html_e( 'Course', 'masterstudy-child' ); ?></th>
                                <th><?php esc_html_e( 'Interview', 'masterstudy-child' ); ?></th>
                                <th><?php esc_html_e( 'Date', 'masterstudy-child' ); ?></th>
                                <th><?php esc_html_e( 'Status', 'masterstudy-child' ); ?></th>
                                <th><?php esc_html_e( 'Vote', 'masterstudy-child' ); ?></th>
                            </tr>
                        </thead>
                        <tbody :class="{'loading' : loading}">
                            <tr v-for="interview in interviews" :key="interview.id" @click="openInterview(interview.id)">
                                <th v-if="userData.isInstructor">
                                    {{ interview.student }}
                                </th>
                                <th v-else>
                                    {{ interview.instructorName }}
                                </th>
                                <td>{{ interview.course }}</td>
                                <td>{{ interview.title }}</td>
                                <td>{{ interview.date }}</td>
                                <td><i class="fa"
                                       :class="generateClass(interview.status)"
                                    ></i>
                                </td>
                                <td>{{ interview.vote }}</td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
            <?php if($is_instructor): ?>
                <div v-if="showForm" class="stm-lms-interviews_form" id="lms-interviews-form">
                    <div class="stm-lms-interviews_form_top">
                        <h2 v-html="form.title"><?php esc_html_e( 'Title interview', 'masterstudy-child' ); ?></h2>
                        <button class="btn btn-default create-interviews-btn" @click="cancelCreateInterview">
                            <?php esc_html_e( 'Cancel', 'masterstudy-child' ); ?>
                        </button>
                    </div>
                    <div class="stm-lms-interviews_form_wr">
                        <div class="stm-lms-interviews_form_form">
                            <div class="form-group">
                                <label for="course"><?php esc_html_e( 'Course', 'masterstudy-child' ); ?><span class="required">*</span></label>
                                <v-select v-model="form.course" class="course-search" :options="courses"></v-select>
                            </div>
                            <div class="form-group">
                                <label for="student"><?php esc_html_e( 'Student', 'masterstudy-child' ); ?><span class="required">*</span></label>
                                <div class="stm-lms-interviews_search_student">
                                    <i class="fa fa-user-plus float_menu_item__icon"></i>
                                    <v-select v-model="form.student" :options="students"></v-select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="interview"><?php esc_html_e( 'Interview', 'masterstudy-child' ); ?><span class="required">*</span></label>
                                <input v-model="form.title" name="interview" id="interview" type="text" placeholder="<?php esc_html_e( 'Title Interview', 'masterstudy-child' ); ?>">
                            </div>
                            <div class="form-group">
                                <label for="interview"><?php esc_html_e( 'Date', 'masterstudy-child' ); ?><span class="required">*</span></label>
                                <date-picker v-model="form.date"></date-picker>
                            </div>
                            <div class="form-group">
                                <label for="vote"><?php esc_html_e( 'Vote', 'masterstudy-child' ); ?></label>
                                <v-select v-model="form.vote" class="course-search" :options="votes"></v-select>
                            </div>
                            <div class="form-group">
                                <label class="stm_lms_styled_checkbox">
                                    <?php esc_html_e( 'Status (Passed Or Not)', 'masterstudy-child' ); ?>
                                    <span class="stm_lms_styled_checkbox__inner">
                                        <input v-model="form.status" type="checkbox" name="status" id="status">
                                        <span><i class="fa fa-check"></i></span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="stm-lms-interviews_form_comment">
                        <h3>
                            <?php esc_html_e( 'Your comment', 'masterstudy-child' ); ?>
                        </h3>
                        <textarea v-model="form.comment"></textarea>
                        <div class="stm-lms-interviews_status">
                            <button v-if="!form.postId" class="btn btn-default" @click="addInterview" :class="{'loading' : loading}">
                                <span><?php esc_html_e( 'Add Interview', 'masterstudy-child' ); ?></span>
                            </button>
                            <button v-else class="btn btn-default" @click="addInterview" :class="{'loading' : loading}">
                                <span><?php esc_html_e( 'Edit Interview', 'masterstudy-child' ); ?></span>
                            </button>
                        </div>
                        <div class="stm-lms-message" style="display: none;"></div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="stm-lms-interview_current" v-if="showCurrent">
                <button class="btn current-back" @click="openInterview">
                    <i class="fa fa-angle-left"></i>
                </button>
                <div class="stm-lms-interview_current_top">
                    <h2>{{ currentInterview.title }}</h2>
                    <i class="fa " :class="[currentInterview.status ? 'fa-check' : 'fa-times']"></i>
                </div>
                <div class="stm-lms-interviews_form_wr">
                    <div class="stm-lms-interviews_form_form">
                        <div class="stm-lms-interview_current_item">
                            <strong><?php esc_html_e( 'Name:', 'masterstudy-child' ); ?></strong> {{ currentInterview.student }}
                        </div>
                        <div class="stm-lms-interview_current_item">
                            <strong><?php esc_html_e( 'Course:', 'masterstudy-child' ); ?></strong> {{ currentInterview.course }}
                        </div>
                        <div class="stm-lms-interview_current_item">
                            <strong><?php esc_html_e( 'Interview:', 'masterstudy-child' ); ?></strong> {{ currentInterview.title }}
                        </div>
                        <div class="stm-lms-interview_current_item">
                            <strong><?php esc_html_e( 'Date:', 'masterstudy-child' ); ?></strong> {{ currentInterview.date }}
                        </div>
                        <div class="stm-lms-interview_current_item">
                            <strong><?php esc_html_e( 'Status:', 'masterstudy-child' ); ?></strong>
                            <span v-if="currentInterview.status"><?php esc_html_e( 'Passed', 'masterstudy-child' ); ?></span>
                            <span v-else><?php esc_html_e( 'Not Passed', 'masterstudy-child' ); ?></span>
                        </div>
                        <div class="stm-lms-interview_current_item">
                            <strong><?php esc_html_e( 'Vote:', 'masterstudy-child' ); ?></strong> {{ currentInterview.vote }}
                        </div>
                    </div>
                </div>
                <div class="stm-lms-interviews_form_comment">
                    <p>
                        {{ currentInterview.comment }}
                    </p>
                    <button v-if="userData.isInstructor" class="btn btn-default create-interviews-btn" @click="editInterview(currentInterview.id)">
                        <?php esc_html_e( 'Edit', 'masterstudy-child' ); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

<?php get_footer(); ?>
