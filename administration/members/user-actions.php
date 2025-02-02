<?php
namespace PHPFusion\Administration\Members;
/**
 * Class Members_Action
 * All function are in the form of multiples user_id
 *
 * @package Administration\Members\Sub_Controllers
 */
class UserActions extends UserList {

    private $action_user_id = [];

    private $action = 0;

    private $users = [];

    private $cancel_link = '';

    /**
     * Action Script Configurations
     *
     * @var array
     */
    private $action_map = [
        parent::USER_BAN          => [
            'check_operator'       => '!==', // Make it fluid.
            'check_value'          => self::USER_BAN,
            'title'                => 'ME_500',
            'a_message'            => 'ME_550',
            'user_status_change'   => parent::USER_BAN,
            'user_status_log_func' => 'suspend_log',
            'reason'               => TRUE,
            'email'                => TRUE,
            'email_title'          => 'email_ban_subject',
            'email_message'        => 'email_ban_message',
        ],
        // OK
        parent::USER_REINSTATE    => [
            'check_operator'       => '>',
            'check_value'          => parent::USER_MEMBER,
            'title'                => 'ME_501',
            'a_message'            => 'ME_551',
            'user_status_change'   => parent::USER_MEMBER,
            'reason'               => TRUE,
            'user_status_log_func' => 'unsuspend_log',
            'email'                => TRUE,
            'email_title'          => 'email_activate_subject',
            'email_message'        => 'email_activate_message',
        ],
        // OK
        parent::USER_SUSPEND      => [
            'check_operator'       => '!==',
            'check_value'          => self::USER_SUSPEND,
            'title'                => 'ME_503',
            'a_message'            => 'ME_553',
            'user_status_change'   => parent::USER_SUSPEND,
            'action_time'          => TRUE,
            'reason'               => TRUE,
            'user_status_log_func' => 'suspend_log',
            'email'                => TRUE,
            'email_title'          => 'email_suspend_subject',
            'email_message'        => 'email_suspend_message',
        ],
        // OK
        parent::USER_SECURITY_BAN => [
            'check_operator'       => '!==',
            'check_value'          => parent::USER_SECURITY_BAN,
            'title'                => 'ME_504',
            'a_message'            => 'ME_554',
            'user_status_change'   => parent::USER_SECURITY_BAN,
            'user_status_log_func' => 'suspend_log',
            'email'                => TRUE,
            'email_title'          => 'email_secban_subject',
            'email_message'        => 'email_secban_message',
        ],
        // OK
        parent::USER_CANCEL       => [
            'check_operator'       => '!==',
            'check_value'          => parent::USER_CANCEL,
            'title'                => 'ME_505',
            'a_message'            => 'ME_555',
            'user_status_change'   => parent::USER_CANCEL,
            'action_time'          => TRUE,
            'user_status_log_func' => 'suspend_log',
        ],
        // OK
        parent::USER_ANON         => [
            'check_operator'       => '!==',
            'check_value'          => parent::USER_ANON,
            'title'                => 'ME_506',
            'a_message'            => 'ME_556',
            'user_status_change'   => parent::USER_ANON,
            'action_time'          => TRUE,
            'user_status_log_func' => 'suspend_log'
        ],
        // OK
        parent::USER_DEACTIVATE   => [
            'check_operator'       => '!==',
            'check_value'          => parent::USER_DEACTIVATE,
            'title'                => 'ME_502',
            'a_message'            => 'ME_552',
            'user_status_change'   => parent::USER_DEACTIVATE,
            'user_status_log_func' => 'suspend_log',
            'action_time'          => TRUE,
            'email'                => TRUE,
            'email_title'          => 'email_deactivate_subject',
            'email_message'        => 'email_deactivate_message',
        ]
    ];

    /**
     * Set a user id
     * @param array $value
     */
    public function set_userID(array $value = []) {
        $user_id = [];
        foreach ($value as $id) {
            if (isnum($id)) {
                $user_id[$id] = $id;
            }
        }
        $this->action_user_id = $user_id;
    }

    /**
     * Set an action for this user class - 1 for ban, etc
     * @param $value
     */
    public function set_action($value) {
        $this->action = $value;
    }

    /**
     * Set an abort link
     * @param $value
     */
    public function setCancelLink($value) {
        $this->cancel_link = $value;
    }

    /**
     * Checks user status against action map check value
     * @param $var1
     * @param $var2
     * @param $case
     *
     * @return bool
     */
    public function isUser($var1, $var2, $case) {
        switch ($case) {
            case '>':
                return ($var1 > $var2);
                break;
            case '<':
                return ($var1 < $var2);
                break;
            case '==':
                return ($var1 == $var2);
                break;
            case '!==':
                return ($var1 !== $var2);
                break;
        }

        return FALSE;
    }

    public function getActionArr() {
        return $this->action_map;
    }

    // this is the mapping for all other actions except delete
    public function execute() {
        $locale = fusion_get_locale();
        $form = '';
        $users_list = '';
        if (post('cancel')) {
            redirect( $this->cancel_link ?: FUSION_REQUEST );
        }

        // Cache affected users
        $query = "SELECT user_id, user_name, user_avatar, user_email, user_level, user_password, user_status FROM ".DB_USERS." WHERE user_id IN (".implode(',', $this->action_user_id).") AND user_level > ".USER_LEVEL_SUPER_ADMIN." GROUP BY user_id";
        $result = dbquery($query);
        if (dbrows($result)) {
            while ($u_data = dbarray($result)) {
                if ($this->isUser($u_data['user_status'], $this->action_map[$this->action]['check_value'], $this->action_map[$this->action]['check_operator'])) {
                    $this->users[$u_data['user_id']] = $u_data;
                }
            }
        }

        if (!empty($this->users)) {
            $u_name = [];

            if (post('post_action')) {

                $settings = fusion_get_settings();
                $userdata = fusion_get_userdata();
                $reason = '';

                if (!empty($this->action_map[$this->action]['reason'])) {
                    $reason = sanitizer('reason', '', 'reason');
                }

                $duration = 0;
                if (!empty($this->action_map[$this->action]['action_time'])) {
                    $duration = sanitizer('duration', 1, 'duration');
                    $duration = ($duration * 86400) + TIME;
                }

                if (\Defender::safe()) {

                    foreach ($this->users as $user_id => $u_data) {

                        dbquery("UPDATE ".DB_USERS." SET user_status=:user_status, user_actiontime=:action_time WHERE user_id=:user_id", [
                            ':user_status' => $this->action_map[$this->action]['user_status_change'],
                            ':action_time' => $duration,
                            ':user_id'     => $user_id
                        ]);

                        // Executes log
                        if (!empty($this->action_map[$this->action]['user_status_log_func'])) {
                            $log_value = ($this->action_map[$this->action]['user_status_log_func'] == 'suspend_log' ? $this->action : $u_data['user_status']);
                            $this->action_map[$this->action]['user_status_log_func']($user_id, $log_value, $reason);
                        }

                        // Email users
                        if (!empty($this->action_map[$this->action]['email'])) {
                            $email_locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/members_email.php');
                            $subject = strtr($email_locale[$this->action_map[$this->action]['email_title']],
                                [
                                    '[SITENAME]' => $settings['sitename']
                                ]
                            );
                            $message = strtr($email_locale[$this->action_map[$this->action]['email_message']],
                                [
                                    '[USER_NAME]'           => $u_data['user_name'],
                                    '[REASON]'              => $reason,
                                    '[SITENAME]'            => $settings['sitename'],
                                    '[ADMIN_USERNAME]'      => $userdata['user_name'],
                                    '[SITEUSERNAME]'        => $settings['siteusername'],
                                    '[DATE]'                => showdate('longdate', $duration),
                                    '[DEACTIVATION_PERIOD]' => $settings['deactivation_period'],
                                    '[REACTIVATION_LINK]'   => $settings['siteurl']."reactivate.php?user_id=".$u_data['user_id']."&code=".md5($duration.$u_data['user_password'])
                                ]
                            );

                            sendemail($u_data['user_name'], $u_data['user_email'], $settings['siteusername'], $settings['siteemail'], $subject, $message);
                        }

                        $u_name[] = $u_data['user_name'];
                    }

                    addNotice('success', sprintf($locale['ME_432'], implode(', ', $u_name), $locale[$this->action_map[$this->action]['a_message']]), 'all');

                    redirect(FUSION_REQUEST);
                }
            }

            if (!post('post_action') || !\Defender::safe()) {
                $height = '45px';
                foreach ($this->users as $user_data) {
                    $users_list .= strtr($this->user_block_template(),
                        [
                            '{%user_avatar%}' => display_avatar($user_data, $height, '', '', ''),
                            '{%height%}'      => $height,
                            '{%user_name%}'   => $user_data['user_name']
                        ]
                    );
                }
                if (isset($this->action_map[$this->action]['action_time'])) {
                    $form .= form_text('duration', $locale['ME_435'], '', ['type' => 'number', 'append' => TRUE, 'append_value' => $locale['ME_436'], 'required' => TRUE, 'inner_width' => '120px']);
                }
                if (!empty($this->action_map[$this->action]['reason'])) {
                    $form .= form_textarea('reason', $locale['ME_433'], '', ['required' => TRUE, 'placeholder' => $locale['ME_434']]);
                }
                $form .= form_hidden('action', '', $this->action);
                // the user id is multiple
                foreach ($this->action_user_id as $user_id) {
                    $form .= form_hidden('user_id[]', '', $user_id);
                }
                $form .= form_button('post_action', $locale['update'], $this->action, ['class' => 'btn-primary']);
                $form .= form_button('cancel', $locale['cancel'], 'cancel');

                $modal = openmodal('uAdmin_modal', $locale[$this->action_map[$this->action]['title']].$locale['ME_413'], ['static' => TRUE]);
                $modal .= openform('uAdmin_frm', 'post', FUSION_REQUEST);
                $modal .= strtr($this->action_form_template(), [
                    '{%message%}'    => sprintf($locale['ME_431'], $locale[$this->action_map[$this->action]['a_message']]),
                    '{%users_list%}' => $users_list,
                    '{%form%}'       => $form,
                ]);
                $modal .= closeform();
                $modal .= closemodal();
                add_to_footer($modal);
            }

        } else {
            // addNotice('danger', $locale['ME_430']);
            redirect( clean_request('', ['step', 'uid', 'user_id'], FALSE) );
        }
    }

    private function action_form_template() {
        return "
        <p><strong>{%message%}</strong></p>
        {%users_list%}
        <hr/>
        {%form%}
        ";
    }

    private function user_block_template() {
        return "
        <div class='display-inline-block panel panel-default panel-body p-0'>\n
        <div class='pull-left m-r-10'>{%user_avatar%}</div>\n
        <div class='overflow-hide'>\n
        <span class='va' style='height:{%height%};'></span>\n
        <span class='va p-r-15'>\n<strong>{%user_name%}</strong>\n</span>\n
        </div>\n
        </div>\n
        ";
    }

}
