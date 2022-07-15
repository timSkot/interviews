<?php

class STM_LMS_Page_Router_Child {
    public static function interviews_url(): string
    {
        $pages_config = STM_LMS_Page_Router::pages_config();

        return STM_LMS_User::login_page_url() . $pages_config['user_url']['sub_pages']['interviews_url']['url'];
    }
}