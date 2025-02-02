<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: members.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';
require_once __DIR__.'/members/user-list.php';
require_once __DIR__.'/members/user-signups.php';
require_once __DIR__.'/members/user-forms.php';
require_once __DIR__.'/members/user-actions.php';
require_once __DIR__.'/members/user-helper.php';
pageAccess('M');

$aidlink = fusion_get_aidlink();
$admin = \PHPFusion\Admins::getInstance();
$admin->addAdminPage('M', 'All Users', 'M1', ADMIN.'members.php'.$aidlink);
$admin->addAdminPage('M', 'Add User', 'M2', ADMIN.'members.php'.$aidlink.'&amp;action=add');
$admin->addAdminPage('M', 'Manage Signups', 'M3', ADMIN.'members.php'.$aidlink.'&amp;action=signup');
$admin->addAdminPage('M', 'Administrators', 'M4', ADMIN.'administrators.php'.$aidlink);
$admin->addAdminPage('M', 'User Fields', 'M5', ADMIN.'user_fields.php'.$aidlink);
$admin->addAdminPage('M5', "Public Fields", "UF-1", ADMIN.'user_fields.php'.fusion_get_aidlink());
$admin->addAdminPage('M5', "Preference Fields", "UF-2", ADMIN.'user_fields.php'.fusion_get_aidlink().'&amp;ref=preference');
$admin->addAdminPage('M5', "Security Fields", "UF-3", ADMIN.'user_fields.php'.fusion_get_aidlink().'&amp;ref=security');

class Members_Administration {

    protected static $locale = [];
    protected static $settings = [];
    protected static $rowstart = 0;
    protected static $sortby = 'all';
    protected static $status = 0;
    protected static $usr_mysql_status = 0;
    protected static $user_id = 0;
    protected static $user_data = [];

    /*
     * Status filter links
     */
    protected static $exit_link = '';
    protected static $is_admin = FALSE;
    protected static $time_overdue = 0;
    protected static $response_required = 0;
    protected static $deactivation_period = 0;
    private static $instance = NULL;

    public function __construct() {

        $aidlink = fusion_get_aidlink();
        $settings = fusion_get_settings();
        $locale = fusion_get_locale('', [
            LOCALE.LOCALESET."admin/members.php",
            LOCALE.LOCALESET.'admin/members_include.php',
            LOCALE.LOCALESET.'admin/members_email.php',
            LOCALE.LOCALESET."user_fields.php"
        ]);


        $time_overdue = TIME - (86400 * $settings['deactivation_period']);
        $response_required = TIME + (86400 * $settings['deactivation_response']);
        $deactivation_period = $settings['deactivation_period'];

        /*
         * LOCALE
         */
        self::$rowstart = get('rowstart', FILTER_SANITIZE_NUMBER_INT); //(isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? $_GET['rowstart'] : 0);
        self::$sortby = get('sortby') ?: 'all'; //(isset($_GET['sortby']) ? stripinput($_GET['sortby']) : "all");
        $status = get('status', FILTER_VALIDATE_INT);
        if ($status < 9) {
            self::$status = $status;
        }
        $usr_mysql_status = get('usr_mysql_status', FILTER_VALIDATE_INT);
        if ($usr_mysql_status < 9) {
            self::$usr_mysql_status = $usr_mysql_status;
        }
        if ($settings['enable_deactivation'] == 1) {
            if (self::$status == 0) {
                self::$usr_mysql_status = "0' AND user_lastvisit > '".self::$time_overdue."' AND user_actiontime='0";
            } elseif (self::$status == 8) {
                self::$usr_mysql_status = "0' AND user_lastvisit < '".self::$time_overdue."' AND user_actiontime='0";
            }
        }

        self::$exit_link = FUSION_SELF.$aidlink."&sortby=".self::$sortby."&status=".self::$status."&rowstart=".self::$rowstart;

        // self::$status_uri = [
        //     self::USER_MEMBER       => $base_url."&amp;status=".self::USER_MEMBER,
        //     self::USER_UNACTIVATED  => $base_url."&amp;status=".self::USER_UNACTIVATED,
        //     self::USER_BAN          => $base_url."&amp;status=".self::USER_BAN,
        //     self::USER_SUSPEND      => $base_url."&amp;status=".self::USER_SUSPEND,
        //     self::USER_SECURITY_BAN => $base_url."&amp;status=".self::USER_SECURITY_BAN,
        //     self::USER_CANCEL       => $base_url."&amp;status=".self::USER_CANCEL,
        //     self::USER_ANON         => $base_url."&amp;status=".self::USER_ANON,
        //     self::USER_DEACTIVATE   => $base_url."&amp;status=".self::USER_DEACTIVATE,
        //     'add_user'              => $base_url.'&amp;ref=add',
        //     'view'                  => $base_url.'&amp;ref=view&amp;lookup=',
        //     'edit'                  => $base_url.'&amp;ref=edit&amp;lookup=',
        //     'delete'                => $base_url.'&amp;ref=delete&amp;lookup=',
        //     'inactive'              => $base_url.'&amp;ref=inactive',
        // ];

        $lookup = get('lookup', FILTER_VALIDATE_INT);
        if (!empty($lookup)) {
            if (dbcount('(user_id)', DB_USERS, 'user_id=:uid', [':uid' => $lookup])) {
                self::$user_id = $lookup;
            }
        }

        if (dbcount("(user_id)", DB_USERS, "user_id=:uid AND user_level<:ulv", [':uid' => self::$user_id, ':ulv' => USER_LEVEL_MEMBER])) {
            self::$is_admin = TRUE;
        }

        if (post('cancel')) {
            redirect(self::$exit_link);
        }
        add_breadcrumb(['link' => ADMIN.'members.php'.$aidlink, 'title' => $locale['ME_400']]);
    }

    /**
     * @return Members_Administration
     */
    public static function getInstance() {
        if (empty(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function display() {
        $aidlink = fusion_get_aidlink();
        $action = get('action');

        // add user fields sections to this part.
        if (!empty($action)) {
            switch ($action) {
                case 'log': // Show Logs
                    if (!self::$is_admin) {
                        display_suspend_log(self::$user_id, "all", self::$rowstart);
                    }
                    break;
                case 'inactive':
                    if (!self::$user_id && self::$settings['enable_deactivation'] && self::$is_admin) {

                        $inactive = dbcount("(user_id)", DB_USERS,
                            "user_status='0' AND user_level>".USER_LEVEL_SUPER_ADMIN." AND user_lastvisit <:last_visited AND user_actiontime=:action_time",
                            [
                                ':last_visited' => self::$time_overdue,
                                ':action_time'  => 0,
                            ]
                        );

                        $button = self::$locale['ME_502'].format_word($inactive, self::$locale['fmt_user']);

                        if (!$inactive) {
                            addNotice('success', self::$locale['ME_460']);
                            redirect(FUSION_SELF.$aidlink);
                        }

                        if (post('deactivate_users') && \Defender::safe()) {

                            require_once INCLUDES."sendmail_include.php";

                            $result = dbquery("SELECT user_id, user_name, user_email, user_password FROM ".DB_USERS."
                                        WHERE user_level>".USER_LEVEL_SUPER_ADMIN." AND user_lastvisit<'".self::$time_overdue."' AND user_actiontime='0' AND user_status='0'");

                            $rows = dbrows($result);

                            if ($rows != '0') {

                                while ($data = dbarray($result)) {

                                    $message = strtr(self::$locale['email_deactivate_message'], [
                                            '[CODE]'         => md5(self::$response_required.$data['user_password']),
                                            '[SITENAME]'     => self::$settings['sitename'],
                                            '[SITEUSERNAME]' => self::$settings['siteusername'],
                                            '[USER_NAME]'    => $data['user_name'],
                                            '[USER_ID]'      => $data['user_id'],
                                        ]
                                    );

                                    if (sendemail($data['user_name'], $data['user_email'], self::$settings['siteusername'], self::$settings['siteemail'], self::$locale['email_deactivate_subject'], $message)) {
                                        dbquery("UPDATE ".DB_USERS." SET user_status='7', user_actiontime='".self::$response_required."' WHERE user_id='".$data['user_id']."'");
                                        suspend_log($data['user_id'], self::USER_DEACTIVATE, self::$locale['ME_468']);
                                    }
                                }
                                addNotice('success', sprintf(self::$locale['ME_461'], format_word($rows, self::$locale['fmt_user'])));
                                redirect(FUSION_SELF.fusion_get_aidlink());
                            }
                        }

                        // Put this into view.
                        add_breadcrumb(['link' => self::$status_uri['inactive'], 'title' => self::$locale['ME_462']]);

                        opentable(self::$locale['ME_462']);

                        if ($inactive > 50) {
                            addNotice('info', sprintf(self::$locale['ME_463'], floor($inactive / 50)));
                        }

                        echo "<div>\n";

                        $action = self::$settings['deactivation_action'] == 0 ? self::$locale['ME_556'] : self::$locale['ME_557'];

                        $text = sprintf(self::$locale['ME_464'], $inactive, self::$settings['deactivation_period'], self::$settings['deactivation_response'], $action);

                        echo str_replace(["[strong]", "[/strong]"], ["<strong>", "</strong>"], $text );

                        if (self::$settings['deactivation_action'] == 1) {
                            echo "<br />\n".self::$locale['ME_465'];
                            echo "</div>\n<div class='admin-message alert alert-warning m-t-10'><strong>".self::$locale['ME_454']."</strong>\n".self::$locale['ME_466']."\n";
                            if (checkrights('S9')) {
                                echo "<a href='".ADMIN."settings_users.php".$aidlink."'>".self::$locale['ME_467']."</a>";
                            }
                        }

                        echo "</div>\n<div class='text-center'>\n";
                        echo openform('member_form', 'post', self::$status_uri['inactive']);
                        echo form_button('deactivate_users', $button, $button, ['class' => 'btn-primary m-r-10']);
                        echo form_button('cancel', self::$locale['cancel'], self::$locale['cancel']);
                        echo closeform();
                        echo "</div>\n";
                        closetable();

                    }
                    break;
                case 'view':
                    if (!empty(self::$user_id)) {
                        $query = "SELECT u.*, s.suspend_reason  FROM ".DB_USERS." u LEFT JOIN ".DB_SUSPENDS." s ON u.user_id=s.suspended_user
                                WHERE u.user_id=:user_id GROUP BY u.user_id ORDER BY s.suspend_date DESC";
                        $bind = [
                            ':user_id' => self::$user_id
                        ];
                        self::$user_data = dbarray(dbquery($query, $bind));
                        $title = sprintf(self::$locale['ME_451'], self::$user_data['user_name']);
                        add_breadcrumb(['link' => self::$status_uri['view'].get('lookup'), 'title' => $title]);
                        opentable($title);
                        // Members_Profile::display_user_profile();
                        closetable();
                    } else {
                        redirect(FUSION_SELF.fusion_get_aidlink());
                    }
                    break;
                case 'signup':
                    $this->userSignUpsList();
                    break;
                case 'add':
                    $this->addUser();
                    break;
                case 'edit':
                    $this->editUser();
                    break;
                case 'delete':
                    if (get('newuser')) {

                        opentable(sprintf(self::$locale['ME_453'], get('lookup')));
                        Members_Profile::delete_unactivated_user();
                        closetable();

                    } else if (!empty(self::$user_id)) {

                        self::$user_data = dbarray(dbquery("SELECT * FROM ".DB_USERS." WHERE user_id=:uid", [':uid' => self::$user_id]));
                        if (empty(self::$user_data) || self::$user_data['user_level'] <= USER_LEVEL_SUPER_ADMIN) {
                            redirect(FUSION_SELF.$aidlink);
                        }

                        opentable(sprintf(self::$locale['ME_453'], self::$user_data['user_name']));
                        Members_Profile::delete_user();
                        closetable();

                    } else {
                        redirect(FUSION_SELF.$aidlink);
                    }
                    break;
            }
        } else {
            $this->userList();
        }
    }

    /**
     * Edit user
     */
    private function editUser() {
        $aidlink = fusion_get_aidlink();
        $locale = fusion_get_locale();
        if (!empty(self::$user_id)) {
            self::$user_data = dbarray(dbquery("SELECT * FROM ".DB_USERS." WHERE user_id=:uid", [
                    ':uid' => (int) self::$user_id
                ]
            ));
            if (empty(self::$user_data) || fusion_get_userdata('user_level') > self::$user_data['user_level']) {
                redirect(FUSION_SELF.$aidlink);
            }
            $title = sprintf($locale['ME_452'], self::$user_data['user_name']);
            $uri = FUSION_SELF.$aidlink.'&amp;ref=view&amp;lookup=';
            add_breadcrumb(['link' => $uri.self::$user_data['user_id'], 'title' => $title]);
            opentable($title);
            $input = new \PHPFusion\Administration\Members\UserForms();
            $input->user_data = self::$user_data;
            echo $input->adminEdit();
            closetable();
        } else {
            redirect(FUSION_SELF.$aidlink);
        }
    }

    private function addUser() {
        $locale = fusion_get_locale();
        $aidlink = fusion_get_aidlink();
        add_breadcrumb(['link' => FUSION_SELF.$aidlink.'&action=add', 'title' => $locale['ME_450']]);
        opentable($locale['ME_450']);
        $input = new \PHPFusion\Administration\Members\UserForms();
        echo $input->adminAdd();
        closetable();
    }

    private function userList() {
        opentable('Members <a class="btn btn-default m-l-10" href="'.FUSION_SELF.fusion_get_aidlink().'&action=add">Add User</a>');
        new \PHPFusion\Tables(new \PHPFusion\Administration\Members\UserList());
        closetable();
    }

    private function userSignUpsList() {
        opentable('Members');
        new \PHPFusion\Tables(new \PHPFusion\Administration\Members\UserSignUps(new \PHPFusion\Administration\Members\UserForms()));
        closetable();
    }

    public function checkUserStatus($data) {
        return getsuspension($data[':user_status']);
    }

    public function diplayRealName($data) {
        return $data[':user_firstname'].($data[':user_lastname'] ? ' '.$data[':user_lastname'] : '');
    }

}

Members_Administration::getInstance()->display();

require_once THEMES.'templates/footer.php';
