<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Viewthread.php
| Author: Chan (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Forums\Threads;

use PHPFusion\Forums\Moderator;

class ViewThread extends ForumThreads {
    
	private $thread_info = array();
    
    private $thread_data = array();

    public function display_thread() {

        if (isset($_GET['action'])) {

            $poll = new Poll($this->thread_info);

            switch($_GET['action']) {
                case 'editpoll':
                    $poll->render_poll_form(true);
                    break;
                case 'deletepoll':
                    $poll->delete_poll();
                    break;
                case 'newpoll':
                    $poll->render_poll_form();
                    break;
                case 'edit':
                    $this->render_edit_form();
                    break;
                case 'reply':
                    $this->render_reply_form();
                    break;
                default:
                    redirect(clean_request('', array('action'), false));
            }

        } else {
            $info = $this->get_thread_data();
            // +1 threadviews
            $this->increment_thread_views($info['thread']['thread_id']);
            // +1 see who is viewing thread
            $this->set_thread_visitor();
            if ($info['thread']['forum_users'] == true) {
                $info['thread_users'] = $this->get_participated_users($info);
            }
            render_thread($info);
        }
    }

	/**
	 * Thread Class constructor - This builds all essential data on load.
	 */
	public function __construct() {

        if (!isset($_GET['thread_id']) && !isnum($_GET['thread_id'])) redirect(INFUSIONS.'forum/index.php');

        $forum_settings = $this->get_forum_settings();

        $locale = fusion_get_locale("", FORUM_LOCALE);

        $userdata = fusion_get_userdata();

		$this->thread_data = self::get_thread($_GET['thread_id']); // fetch query and define iMOD

		if (!empty($this->thread_data)) {

			$thread_stat = self::get_thread_stats($_GET['thread_id']); // get post_count, lastpost_id, first_post_id.

            if ($this->thread_data['forum_type'] == 1) {
                if (fusion_get_settings("site_seo")) {
                    redirect(fusion_get_settings("siteurl")."infusions/forum/index.php");
                }
                redirect(INFUSIONS.'forum/index.php');
            }

			if ($thread_stat['post_count'] < 1) {
                if (fusion_get_settings("site_seo")) {
                    redirect(fusion_get_settings("siteurl")."infusions/forum/index.php");
                }
                redirect(INFUSIONS.'forum/index.php');
            }

			// Set meta
            add_to_title($this->thread_data['thread_subject']);
            add_to_meta($locale['forum_0000']);
			if ($this->thread_data['forum_description'] !== '') add_to_meta('description', $this->thread_data['forum_description']);
			if ($this->thread_data['forum_meta'] !== '') add_to_meta('keywords', $this->thread_data['forum_meta']);

			// Set Forum Breadcrumbs
			$this->forum_index = dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat');
			add_breadcrumb(array('link' => INFUSIONS.'forum/index.php', 'title' => $locale['forum_0000']));
			forum_breadcrumbs($this->forum_index, $this->thread_data['forum_id']);
			add_breadcrumb(array('link' => INFUSIONS.'forum/viewthread.php?forum_id='.$this->thread_data['forum_id'].'&amp;thread_id='.$this->thread_data['thread_id'],
							   'title' => $this->thread_data['thread_subject']));

			$this->setThreadPermission();

			// Sanitizes $_GETs
			$_GET['forum_id'] = intval($this->thread_data['forum_id']);

			/**
			 * Generate User Tracked Buttons
			 */
			$this->thread_info['buttons']['notify'] = array();

			if ($this->getThreadPermission("can_access")) {
				// only member can track the thread
				if ($this->thread_data['user_tracked']) {
					$this->thread_info['buttons']['notify'] = array(
						'link' => INFUSIONS."forum/postify.php?post=off&amp;forum_id=".$this->thread_data['forum_id']."&amp;thread_id=".$this->thread_data['thread_id'],
						'title' => $locale['forum_0174']
					);
				} else {
					$this->thread_info['buttons']['notify'] = array(
						'link' => INFUSIONS."forum/postify.php?post=on&amp;forum_id=".$this->thread_data['forum_id']."&amp;thread_id=".$this->thread_data['thread_id'],
						'title' => $locale['forum_0175']
					);
				}
			}

            $this->thread_info['thread'] = $this->thread_data;

			/**
			 * Generate Quick Reply Form
			 */
			$qr_form = "";
			if ($this->getThreadPermission("can_reply") == TRUE && $this->thread_data['forum_quick_edit'] == TRUE) {

				$qr_form = "<!--sub_forum_thread-->\n";
                $form_url = INFUSIONS."forum/viewthread.php?thread_id=".$this->thread_data['thread_id'];
				$qr_form .= openform('quick_reply_form', 'post', $form_url, array('class' => 'm-b-20 m-t-20'));
				$qr_form .= "<h4 class='m-t-20 pull-left'>".$locale['forum_0168']."</h4>\n";
				$qr_form .= form_textarea('post_message', $locale['forum_0601'], '',
										  array('bbcode' => true,
											  'required' => true,
											  'autosize' => true,
											  'preview' => true,
											  'form_name' => 'quick_reply_form'
										  ));
				$qr_form .= "<div class='m-t-10 pull-right'>\n";
				$qr_form .= form_button('post_quick_reply', $locale['forum_0172'], $locale['forum_0172'], array('class' => 'btn-primary btn-sm m-r-10'));
				$qr_form .= "</div>\n";
				$qr_form .= "<div class='overflow-hide'>\n";
				$qr_form .= form_checkbox('post_smileys', $locale['forum_0169'], '', array('class' => 'm-b-0'));
				if (array_key_exists("user_sig", $userdata) && $userdata['user_sig']) {
					$qr_form .= form_checkbox('post_showsig', $locale['forum_0170'], '1', array('class' => 'm-b-0'));
				}
				if ($forum_settings['thread_notify']) {
					$qr_form .= form_checkbox('notify_me', $locale['forum_0171'], $this->thread_data['user_tracked'], array('class' => 'm-b-0'));
				}
				$qr_form .= "</div>\n";
				$qr_form .= closeform();
			}

			/**
			 * Generate Poll Form
			 */
            $poll = new Poll($this->thread_info);
            $poll_form = $poll->generate_poll($this->thread_data);

			/**
			 * Generate Attachment
			 */
            $attach = new Attachment($this->thread_info);
            $attachments = $attach::get_attachments($this->thread_data);

			/**
			 * Generate Mod Form
			 */
            $mod = new Moderator();

			if (iMOD) {

                $mod->setForumId($this->thread_data['forum_id']);
				$mod->setThreadId($this->thread_data['thread_id']);
				$mod->set_modActions();

				/**
				 * Thread moderation form template
				 */
                $addition = isset($_GET['rowstart']) ? "&amp;rowstart=".intval($_GET['rowstart']) : "";
                $this->thread_info['form_action'] = INFUSIONS."forum/viewthread.php?thread_id=".intval($this->thread_data['thread_id']).$addition;
                $this->thread_info['open_post_form'] = openform('moderator_menu', 'post', $this->thread_info['form_action']);

                $this->thread_info['mod_options'] = array(
                    'renew' => $locale['forum_0207'],
					'delete' => $locale['forum_0201'],
					$this->thread_data['thread_locked'] ? "unlock" : "lock" => $this->thread_data['thread_locked'] ? $locale['forum_0203'] : $locale['forum_0202'],
					$this->thread_data['thread_sticky'] ? "nonsticky" : "sticky" => $this->thread_data['thread_sticky'] ? $locale['forum_0205'] : $locale['forum_0204'],
                    'move' => $locale['forum_0206']
                );

				$this->thread_info['close_post_form'] = closeform();

                $this->thread_info['mod_form'] = "
				<div class='list-group-item'>\n
					<div class='btn-group m-r-10'>\n
						".form_button("check_all", $locale['forum_0080'], $locale['forum_0080'], array('class' => 'btn-default btn-sm', "type"=>"button"))."
						".form_button("check_none", $locale['forum_0081'], $locale['forum_0080'], array('class' => 'btn-default btn-sm', "type"=>"button"))."
					</div>\n
					".form_button('move_posts', $locale['forum_0176'], $locale['forum_0176'], array('class' => 'btn-default btn-sm m-r-10'))."
					".form_button('delete_posts', $locale['forum_0177'], $locale['forum_0177'], array('class' => 'btn-default btn-sm'))."
					<div class='pull-right'>
						".form_button('go', $locale['forum_0208'], $locale['forum_0208'], array('class' => 'btn-default pull-right btn-sm m-t-0 m-l-10'))."
						".form_select('step', '', '',
                                      array(
                                          'options' => $this->thread_info['mod_options'],
                                          'placeholder' => $locale['forum_0200'],
                                          'width' => '250px',
                                          'allowclear' => TRUE,
                                          'class' => 'm-b-0 m-t-5',
                                          'inline' => TRUE
                                      )
                    )."
					</div>\n
				</div>\n";
                add_to_jquery("
				$('#check_all').bind('click', function() {
				    var thread_posts = $('#moderator_menu input:checkbox').prop('checked', true);
				});
				$('#check_none').bind('click', function() {
				    var thread_posts = $('#moderator_menu input:checkbox').prop('checked', false); });
				");

			}

			$this->thread_info += array(
				"thread" => $this->thread_data,
				"thread_id" => $this->thread_data['thread_id'],
				"forum_id" => $this->thread_data['forum_id'],
				"forum_cat" => isset($_GET['forum_cat']) && verify_forum($_GET['forum_cat']) ? $_GET['forum_cat'] : 0,
				"forum_branch" => isset($_GET['forum_branch']) && verify_forum($_GET['forum_branch']) ? $_GET['forum_branch'] : 0,
				"forum_link" => array(
					"link" => INFUSIONS."forum/index.php?viewforum&amp;forum_id=".$this->thread_data['forum_id']."&amp;forum_cat=".$this->thread_data['forum_cat']."&amp;forum_branch=".$this->thread_data['forum_branch'],
					"title" => $this->thread_data['forum_name']
				),
                "thread_attachments" => $attachments,
				"post_id" => isset($_GET['post_id']) && verify_post($_GET['post_id']) ? $_GET['post_id'] : 0,
				"pid" => isset($_GET['pid']) && isnum($_GET['pid']) ? $_GET['pid'] : 0,
				"section" => isset($_GET['section']) ? $_GET['section'] : '',
				"forum_moderators" => $mod::parse_forum_mods($this->thread_data['forum_mods']),
				"max_post_items" => $thread_stat['post_count'],
				"post_firstpost" => $thread_stat['first_post_id'],
				"post_lastpost" => $thread_stat['last_post_id'],
				"posts_per_page" => $forum_settings['posts_per_page'],
				"threads_per_page" => $forum_settings['threads_per_page'],
				"lastvisited" => (isset($userdata['user_lastvisit']) && isnum($userdata['user_lastvisit'])) ? $userdata['user_lastvisit'] : time(),
				"allowed_post_filters" => array('oldest', 'latest', 'high'),
				"attachtypes" => explode(",", $forum_settings['forum_attachtypes']),
				"quick_reply_form" => $qr_form,
				"poll_form" => $poll_form,
				"post-filters" => "",
				'mod_options' => array(),
				'form_action' => '',
				'open_post_form' => '',
				'close_post_form' => '',
				'mod_form' => ''
			);

			/**
			 * Generate All Thread Buttons
			 */
			$this->thread_info['buttons'] += array(
				"print" => array(
                    "link" => BASEDIR."print.php?type=F&amp;item_id=".$this->thread_data['thread_id']."&amp;rowstart=".$_GET['rowstart'],
					"title" => $locale['forum_0178']
				),
				"newthread" => $this->getThreadPermission("can_post") == TRUE ?
						array(
							"link" => INFUSIONS."forum/newthread.php?forum_id=".$this->thread_data['forum_id'],
							"title" => $locale['forum_0264']
						) : array(),
				"reply" => $this->getThreadPermission("can_reply") == TRUE ?
						array(
							"link" => INFUSIONS."forum/viewthread.php?action=reply&amp;forum_id=".$this->thread_data['forum_id']."&amp;thread_id=".$this->thread_data['thread_id'],
							"title" => $locale['forum_0360']
						) : array(),
				"poll" => $this->getThreadPermission("can_create_poll") == TRUE ?
						array(
							"link" => INFUSIONS."forum/viewthread.php?action=newpoll&amp;forum_id=".$this->thread_data['forum_id']."&amp;thread_id=".$this->thread_data['thread_id'],
							"title" => $locale['forum_0366']
						) : array()
			);

			/**
			 * Generate Post Filters
			 */
			$this->thread_info['post-filters'][0] = array('value' => INFUSIONS.'forum/viewthread.php?thread_id='.$this->thread_data['thread_id'].'&amp;section=oldest',
				'locale' => $locale['forum_0180']);
			$this->thread_info['post-filters'][1] = array('value' => INFUSIONS.'forum/viewthread.php?thread_id='.$this->thread_data['thread_id'].'&amp;section=latest',
				'locale' => $locale['forum_0181']);
			if ($this->getThreadPermission("can_rate") == TRUE) {
				$this->thread_info['allowed-post-filters'][2] = 'high';
				$this->thread_info['post-filters'][2] = array('value' => INFUSIONS.'forum/viewthread.php?thread_id='.$this->thread_info['thread_id'].'&amp;section=high',
					'locale' => $locale['forum_0182']);
			}

			$this->handle_quick_reply();
			$this->get_thread_post();
			//self::set_ThreadJs();
			// execute in the end.

		} else {
			redirect(FORUM.'index.php');
		}
	}

	static function get_thread_stats($thread_id) {
		list($array['post_count'], $array['last_post_id'], $array['first_post_id']) = dbarraynum(dbquery("SELECT COUNT(post_id), MAX(post_id), MIN(post_id) FROM ".DB_FORUM_POSTS." WHERE thread_id='".intval($thread_id)."' AND post_hidden='0' GROUP BY thread_id"));
		if (!$array['post_count']) redirect(INFUSIONS.'forum/index.php'); // exit no.2
		$_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $array['last_post_id'] ? $_GET['rowstart'] : 0; // secure against XSS
		return $array;
	}

	/**
	 * Set in full extent of forum permissions and thread permissions
	 * @todo: Include - forum_lock and thread_locked
	 * @todo: Include - user rated on posts
	 * @todo: Include - user is poll starter
	 * @param $this->thread_data
	 */
	private function setThreadPermission() {

		// Access the forum
		$this->thread_info['permissions']['can_access'] = (iMOD || checkgroup($this->thread_data['forum_access'])) ? TRUE : FALSE;
		// Create another thread under the same forum
		$this->thread_info['permissions']['can_post'] = (iMOD || (checkgroup($this->thread_data['forum_post']) && $this->thread_data['forum_lock'] == FALSE)) ? TRUE : FALSE;
		// Upload an attachment in this thread
		$this->thread_info['permissions']['can_upload_attach'] = $this->thread_data['forum_allow_attach'] == TRUE &&
																 (iMOD || (checkgroup($this->thread_data['forum_attach'])
																					 && $this->thread_data['forum_lock'] == FALSE
																					 && $this->thread_data['thread_locked'] == FALSE
																 ))  ? TRUE : FALSE;
		// Download an attachment in this thread
		$this->thread_info['permissions']['can_download_attach'] = iMOD || ($this->thread_data['forum_allow_attach'] == TRUE && checkgroup($this->thread_data['forum_attach_download'])) ? TRUE : FALSE;
		// Post a reply in this thread
		$this->thread_info['permissions']['can_reply'] = $this->thread_data['thread_postcount'] > 0
														 && (iMOD || (checkgroup($this->thread_data['forum_reply'])
																  && $this->thread_data['forum_lock'] == FALSE
																  && $this->thread_data['thread_locked'] == FALSE
														)) ? TRUE : FALSE;
		// Create a poll
		$this->thread_info['permissions']['can_create_poll'] = $this->thread_data['thread_poll'] == FALSE // there are no existing poll.
															   && $this->thread_data['forum_allow_poll'] == TRUE &&
															   (iMOD || (checkgroup($this->thread_data['forum_poll'])
																		 && $this->thread_data['forum_lock'] == FALSE
																		 && $this->thread_data['thread_locked'] == FALSE
																   )) ? TRUE : FALSE;
		// Edit a poll (modify the poll)
		$this->thread_info['permissions']['can_edit_poll'] = $this->thread_data['thread_poll'] == TRUE &&
															 (iMOD || (checkgroup($this->thread_data['forum_poll'])
																	   && $this->thread_data['forum_lock'] == FALSE
																	   && $this->thread_data['thread_locked'] == FALSE
																	   && $this->thread_data['thread_author'] == fusion_get_userdata('user_id')
																 )) ? TRUE : FALSE;
		// Can vote a poll
		$this->thread_info['permissions']['can_vote_poll'] = $this->thread_data['poll_voted'] == FALSE
														&& (iMOD || (checkgroup($this->thread_data['forum_vote'])
																	 && $this->thread_data['forum_lock'] == FALSE
																	 && $this->thread_data['thread_locked'] == FALSE
			)) ? TRUE : FALSE;

		// Can vote in this thread
		$this->thread_info['permissions']['can_rate'] = $this->thread_data['forum_type'] == 4
														&& $this->thread_data['thread_rated'] == FALSE
														&& (iMOD || (checkgroup($this->thread_data['forum_post_ratings'])
																	 && $this->thread_data['forum_lock'] == FALSE
																	 && $this->thread_data['thread_locked'] == FALSE
			)) ? TRUE : FALSE;
	}

	/**
	 * Get the relevant permissions of the current thread permission configuration
	 * @param null $key
	 * @return null
	 */
	protected function getThreadPermission($key = NULL) {
		if (!empty($this->thread_info['permissions'])) {
			if (isset($this->thread_info['permissions'][$key])) {
				return $this->thread_info['permissions'][$key];
			}
			return $this->thread_info['permissions'];
		}
		return NULL;
	}

	/**
	 * Handle post of Quick Reply Form
	 */
	private function handle_quick_reply() {

		$forum_settings = $this->get_forum_settings();

        $locale = fusion_get_locale();

        $userdata = fusion_get_userdata();

		if (isset($_POST['post_quick_reply'])) {

			if ($this->getThreadPermission("can_reply") && \defender::safe()) {
				$this->thread_data = $this->thread_info['thread'];
				require_once INCLUDES."flood_include.php";
				if (!flood_control("post_datestamp", DB_FORUM_POSTS, "post_author='".$userdata['user_id']."'")) { // have notice
					$post_data = array(
						'post_id' => 0,
						'forum_id' => $this->thread_data['forum_id'],
						'thread_id' => $this->thread_data['thread_id'],
						'post_message' => form_sanitizer($_POST['post_message'], '', 'post_message'),
						'post_showsig' => isset($_POST['post_showsig']) ? 1 : 0,
						'post_smileys' => isset($_POST['post_smileys']) || preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $_POST['post_message']) ? 0 : 1,
						'post_author' => $userdata['user_id'],
						'post_datestamp' => time(),
						'post_ip' => USER_IP,
						'post_ip_type' => USER_IP_TYPE,
						'post_edituser' => 0,
						'post_edittime' => 0,
						'post_editreason' => '',
						'post_hidden' => 0,
						'post_locked' => $forum_settings['forum_edit_lock'] || isset($_POST['post_locked']) ? 1 : 0
					);

					if (\defender::safe()) { // post message is invalid or whatever is invalid

						$update_forum_lastpost = FALSE;

						// Prepare forum merging action
						$last_post_author = dbarray(dbquery("SELECT post_author FROM ".DB_FORUM_POSTS." WHERE thread_id='".$this->thread_data['thread_id']."' ORDER BY post_id DESC LIMIT 1"));
						if ($last_post_author['post_author'] == $post_data['post_author'] && $this->thread_data['forum_merge']) {
							$last_message = dbarray(dbquery("SELECT post_id, post_message FROM ".DB_FORUM_POSTS." WHERE thread_id='".$this->thread_data['thread_id']."' ORDER BY post_id DESC"));
							$post_data['post_id'] = $last_message['post_id'];
							$post_data['post_message'] = $last_message['post_message']."\n\n".$locale['forum_0640']." ".showdate("longdate", time()).":\n".$post_data['post_message'];
							dbquery_insert(DB_FORUM_POSTS, $post_data, 'update', array('primary_key' => 'post_id'));
						} else {
							$update_forum_lastpost = TRUE;
							dbquery_insert(DB_FORUM_POSTS, $post_data, 'save', array('primary_key' => 'post_id'));
							$post_data['post_id'] = dblastid();
							dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts+1 WHERE user_id='".$post_data['post_author']."'");
						}

						// Update stats in forum and threads
						if ($update_forum_lastpost) {
							// find all parents and update them
							$list_of_forums = get_all_parent(dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat'), $this->thread_data['forum_id']);
							if (!empty($list_of_forums)) {
                                foreach ($list_of_forums as $fid) {
                                    dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".time()."', forum_postcount=forum_postcount+1, forum_lastpostid='".$post_data['post_id']."', forum_lastuser='".$post_data['post_author']."' WHERE forum_id='".$fid."'");
                                }
                            }
							// update current forum
							dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".time()."', forum_postcount=forum_postcount+1, forum_lastpostid='".$post_data['post_id']."', forum_lastuser='".$post_data['post_author']."' WHERE forum_id='".$this->thread_data['forum_id']."'");
							// update current thread
							dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost='".time()."', thread_lastpostid='".$post_data['post_id']."', thread_postcount=thread_postcount+1, thread_lastuser='".$post_data['post_author']."' WHERE thread_id='".$this->thread_data['thread_id']."'");
						}
						// set notify
						if ($forum_settings['thread_notify'] == TRUE && isset($_POST['notify_me']) && $this->thread_data['thread_id']) {
							if (!dbcount("(thread_id)", DB_FORUM_THREAD_NOTIFY, "thread_id='".$this->thread_data['thread_id']."' AND notify_user='".$post_data['post_author']."'")) {
								dbquery("INSERT INTO ".DB_FORUM_THREAD_NOTIFY." (thread_id, notify_datestamp, notify_user, notify_status) VALUES('".$this->thread_data['thread_id']."', '".time()."', '".$post_data['post_author']."', '1')");
							}
						}
					}

                    redirect(INFUSIONS."forum/postify.php?post=reply&error=0&amp;forum_id=".intval($post_data['forum_id'])."&amp;thread_id=".intval($post_data['thread_id'])."&amp;post_id=".intval($post_data['post_id']));
				}
			}
		}
	}

	/**
	 * Get thread posts info
	 */
	private function get_thread_post() {

        $forum_settings = $this->get_forum_settings();

        $userdata = fusion_get_userdata();

        $locale = fusion_get_locale();

        $user_sig_module = \PHPFusion\UserFields::check_user_field('user_sig');
		$user_web_module = \PHPFusion\UserFields::check_user_field('user_web');
		$userid = isset($userdata['user_id']) ? (int) $userdata['user_id'] : 0;
		switch ($this->thread_info['section']) {
			case 'oldest':
				$sortCol = 'post_datestamp ASC';
				break;
			case 'latest':
				$sortCol = 'post_datestamp DESC';
				break;
			case 'high':
				$sortCol = 'vote_points DESC';
				break;
			default:
				$sortCol = 'post_datestamp ASC';
		}
		// @todo: where to calculate has voted without doing it in while loop?
		require_once INCLUDES."mimetypes_include.php";
		$result = dbquery("
					SELECT p.*,
					t.thread_id,
					u.user_id, u.user_name, u.user_status, u.user_avatar, u.user_level, u.user_posts, u.user_groups,
					u.user_joined, u.user_lastvisit, u.user_ip,
					".($user_sig_module ? " u.user_sig," : "").($user_web_module ? " u.user_web," : "")."
					u2.user_name AS edit_name, u2.user_status AS edit_status,
					count(a1.attach_id) 'attach_image_count',
					count(a2.attach_id) 'attach_files_count',
					SUM(v.vote_points) as vote_points, count(v2.thread_id) as has_voted
					FROM ".DB_FORUM_POSTS." p
					INNER JOIN ".DB_FORUM_THREADS." t ON t.thread_id = p.thread_id
					LEFT JOIN ".DB_FORUM_VOTES." v ON v.post_id = p.post_id
					LEFT JOIN ".DB_FORUM_VOTES." v2 on v2.thread_id = p.thread_id AND v2.vote_user = '".$userid."'
					LEFT JOIN ".DB_USERS." u ON p.post_author = u.user_id
					LEFT JOIN ".DB_USERS." u2 ON p.post_edituser = u2.user_id AND post_edituser > '0'
					LEFT JOIN ".DB_FORUM_ATTACHMENTS." a1 on a1.post_id = p.post_id AND a1.attach_mime IN ('".implode(",", img_mimeTypes() )."')
					LEFT JOIN ".DB_FORUM_ATTACHMENTS." a2 on a2.post_id = p.post_id AND a2.attach_mime NOT IN ('".implode(",", img_mimeTypes() )."')
					WHERE p.thread_id='".intval($_GET['thread_id'])."' AND post_hidden='0'
					".($this->thread_info['thread']['forum_type'] == '4' ? "OR p.post_id='".intval($this->thread_info['post_firstpost'])."'" : '')."
					GROUP by p.post_id
					ORDER BY $sortCol LIMIT ".intval($_GET['rowstart']).", ".intval($forum_settings['posts_per_page'])
		);

		$this->thread_info['post_rows'] = dbrows($result);

		if ($this->thread_info['post_rows'] > 0) {
			/* Set Threads Navigation */
			$this->thread_info['thread_posts'] = format_word($this->thread_info['post_rows'], $locale['fmt_post']);
			$this->thread_info['page_nav'] = '';
			if ($this->thread_info['max_post_items'] > $this->thread_info['posts_per_page']) {
                $this->thread_info['page_nav'] = "<div class='pull-right'>".makepagenav($_GET['rowstart'],
                                                                                        $this->thread_info['posts_per_page'],
                                                                                        $this->thread_info['max_post_items'],
                                                                                        3,
                                                                                        INFUSIONS."forum/viewthread.php?thread_id=".$this->thread_info['thread']['thread_id'].(isset($_GET['highlight']) ? "&amp;highlight=".urlencode($_GET['highlight']) : '')."&amp;")."</div>";
			}
			$i = 1;
			while ($pdata = dbarray($result)) {

				// Format Post Message
				$post_message = $pdata['post_smileys'] ? parsesmileys($pdata['post_message']) : $pdata['post_message'];
				$post_message = nl2br(parseubb($post_message));
				if (isset($_GET['highlight'])) $post_message = "<div class='search_result'>".$post_message."</div>\n";

				// Marker
				$marker = array(
					'link' => "#post_".$pdata['post_id'],
					"title" => "#".($i+$_GET['rowstart']),
					'id' => "post_".$pdata['post_id']
				);
				$post_marker = "<a class='marker' href='".$marker['link']."' id='".$marker['id']."'>".$marker['title']."</a>";
				$post_marker .= "<a title='".$locale['forum_0241']."' href='#top'><i class='entypo up-open'></i></a>\n";

				// Post Attachments
				$post_attachments = "";
				if ($pdata['attach_files_count'] || $pdata['attach_image_count']) {
					if ($this->getThreadPermission("can_download_attach")) {
						$attachResult = dbquery("SELECT * FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".intval($pdata['post_id'])."'");
						if (dbrows($attachResult)>0) {
							$aImage = ""; $aFiles = "";
							while ($attachData = dbarray($attachResult)) {
								if (in_array($attachData['attach_mime'], img_mimeTypes())) {
									$aImage .= display_image_attach($attachData['attach_name'], "50", "50", $pdata['post_id'])."\n";
								} else {
									$aFiles .= "<div class='display-inline-block'><i class='entypo attach'></i><a href='".INFUSIONS."forum/viewthread.php?thread_id=".$pdata['thread_id']."&amp;getfile=".$attachData['attach_id']."'>".$attachData['attach_name']."</a>&nbsp;";
									$aFiles .= "[<span class='small'>".parsebytesize(filesize(INFUSIONS."forum/attachments/".$attachData['attach_name']))." / ".$attachData['attach_count'].$locale['forum_0162']."</span>]</div>\n";
								}
							}
							if (!empty($aFiles)) {
								$post_attachments .= "<div class='emulated-fieldset'>\n";
								$post_attachments .= "<span class='emulated-legend'>".profile_link($pdata['user_id'], $pdata['user_name'], $pdata['user_status']).$locale['forum_0154'].($pdata['attach_files_count'] > 1 ? $locale['forum_0158'] : $locale['forum_0157'])."</span>\n";
								$post_attachments .= "<div class='attachments-list m-t-10'>".$aFiles."</div>\n";
								$post_attachments .= "</div>\n";
							}

							if (!empty($aImage)) {
								$post_attachments .= "<div class='emulated-fieldset'>\n";
								$post_attachments .= "<span class='emulated-legend'>".profile_link($pdata['user_id'], $pdata['user_name'], $pdata['user_status']).$locale['forum_0154'].($pdata['attach_image_count'] > 1 ? $locale['forum_0156'] : $locale['forum_0155'])."</span>\n";
								$post_attachments .= "<div class='attachments-list'>".$aImage."</div>\n";
								$post_attachments .= "</div>\n";
								if (!defined('COLORBOX')) {
									define('COLORBOX', TRUE);
									add_to_head("<link rel='stylesheet' href='".INCLUDES."jquery/colorbox/colorbox.css' type='text/css' media='screen' />");
									add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/colorbox/jquery.colorbox.js'></script>");
									add_to_jquery("$('a[rel^=\"attach\"]').colorbox({ current: '".$locale['forum_0159']." {current} ".$locale['forum_0160']." {total}',width:'80%',height:'80%'});");
								}
							}
						} else {
							$post_attachments = "Failed to fetch the attachment";
						}
					} else {
						$post_attachments = "<small><i class='fa fa-clipboard'></i> ".$locale['forum_0184']."</small>\n";
					}
				}


                $pdata['user_ip'] = ($forum_settings['forum_ips'] && iMOD) ? $locale['forum_0268'].' '.$pdata['post_ip'] : '';

				$pdata += array(
					"user_online" => $pdata['user_lastvisit'] >= time()-3600 ? TRUE : FALSE,
					"is_first_post" =>$pdata['post_id'] == $this->thread_info['post_firstpost'] ? TRUE : FALSE,
					"is_last_post" =>$pdata['post_id'] == $this->thread_info['post_lastpost'] ? TRUE : FALSE,
					"user_profile_link" => profile_link($pdata['user_id'], $pdata['user_name'], $pdata['user_status']),
					"user_avatar_image" => display_avatar($pdata, '40px', FALSE, FALSE, 'img-rounded'),
					"user_post_count" => format_word($pdata['user_posts'], $locale['fmt_post']),
					"print" =>	array(
                        'link' => BASEDIR."print.php?type=F&amp;item_id=".$_GET['thread_id']."&amp;post=".$pdata['post_id']."&amp;nr=".($i + $_GET['rowstart']),
						'title' => $locale['forum_0179']
					),
					"post_marker" => $post_marker,
					"marker" => $marker,
					"post_attachments" => $post_attachments,
				);
				$pdata['post_message'] = $post_message;

				/**
				 * User Stuffs, Sig, User Message, Web
				 */
				// Quote & Edit Link
				if ($this->getThreadPermission("can_reply")) {

					if (!$this->thread_info['thread']['thread_locked']) {
						$pdata['post_quote'] = array(
							'link' => INFUSIONS."forum/viewthread.php?action=reply&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id']."&amp;quote=".$pdata['post_id'],
							'title' => $locale['forum_0266']
						);
						if (iMOD || (
								(($forum_settings['forum_edit_lock'] == TRUE && $pdata['is_last_post'] || $forum_settings['forum_edit_lock'] == FALSE))
								&& ($userdata['user_id'] == $pdata['post_author'])
								&& ($forum_settings['forum_edit_timelimit'] <= 0 || time()-$forum_settings['forum_edit_timelimit']*60 < $pdata['post_datestamp'])
							)
						) {
							$pdata['post_edit'] = array(
								'link' => INFUSIONS."forum/viewthread.php?action=edit&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'],
								'title' => $locale['forum_0265']
							);
						}
						$pdata['post_reply'] = array(
							'link' => INFUSIONS."forum/viewthread.php?action=reply&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'],
							'title' => $locale['forum_0509']
						);
					} elseif (iMOD) {
						$pdata['post_edit'] = array(
							'link' => INFUSIONS."forum/viewthread.php?action=edit&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'],
							'title' => $locale['forum_0265']
						);
					}
				}

				// rank img
				if ($pdata['user_level'] <= USER_LEVEL_ADMIN) {
					if ($forum_settings['forum_ranks']) {
						$pdata['user_rank'] = show_forum_rank($pdata['user_posts'], $pdata['user_level'], $pdata['user_groups']); // in fact now is get forum rank
					} else {
						$pdata['user_rank'] = getuserlevel($pdata['user_level']);
					}
				} else {
					if ($forum_settings['forum_ranks']) {
						$pdata['user_rank'] = iMOD ? show_forum_rank($pdata['user_posts'], 104, $pdata['user_groups']) : show_forum_rank($pdata['user_posts'], $pdata['user_level'], $pdata['user_groups']);
					} else {
						$pdata['user_rank'] = iMOD ? $locale['userf1'] : getuserlevel($pdata['user_level']);
					}
				}

				// Website
				if ($pdata['user_web'] && (iADMIN || $pdata['user_status'] != 6 && $pdata['user_status'] != 5)) {
					$user_web_url = !preg_match("@^http(s)?\:\/\/@i", $pdata['user_web']) ? "http://".$pdata['user_web'] : $pdata['user_web'];
					$pdata['user_web'] = array(
						'link' => $user_web_url,
						'title' => $locale['forum_0364']);
				} else {
					$pdata['user_web'] = array('link' => '', 'title' => '');
				}

				// PM link
				$pdata['user_message'] = array('link' => '', 'title' => '');
				if (iMEMBER && $pdata['user_id'] != $userdata['user_id'] && (iADMIN || $pdata['user_status'] != 6 && $pdata['user_status'] != 5)) {
					$pdata['user_message'] = array('link' => BASEDIR.'messages.php?msg_send='.$pdata['user_id'],
						"title" => $locale['send_message']);
				}

				// User Sig
				if ($pdata['user_sig'] && isset($pdata['post_showsig']) && $pdata['user_status'] != 6 && $pdata['user_status'] != 5) {
					$pdata['user_sig'] = nl2br(parseubb(parsesmileys(stripslashes($pdata['user_sig'])), "b|i|u||center|small|url|mail|img|color"));
				} else {
					$pdata['user_sig'] = "";
				}

				// Voting - need up or down link - accessible to author also the vote
				// answered and on going questions.
				// Answer rating
				$pdata['vote_message'] = '';
				//echo $data['forum_type'] == 4 ? "<br/>\n".(number_format($data['thread_postcount']-1)).$locale['forum_0365']."" : ''; // answers
				// form components
				$pdata['post_checkbox'] = iMOD ? "<input type='checkbox' name='delete_post[]' value='".$pdata['post_id']."'/>" : '';
				$pdata['post_votebox'] = '';
				if ($this->thread_info['thread']['forum_type'] == 4) {
					if ($this->getThreadPermission("can_rate")) { // can vote.
						$pdata['vote_up'] = array('link' => INFUSIONS."forum/postify.php?post=voteup&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'],
							"title" => $locale['forum_0265']);
						$pdata['vote_down'] = array('link' => INFUSIONS."forum/postify.php?post=votedown&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'],
							"title" => $locale['forum_0265']);
						$pdata['post_votebox'] = "<div class='text-center'>\n";
						$pdata['post_votebox'] .= "<a href='".$pdata['vote_up']['link']."' class='btn btn-default btn-xs m-b-5 p-5' title='".$locale['forum_0265']."'>\n<i class='entypo up-dir icon-xs'></i></a>";
						$pdata['post_votebox'] .= "<h3 class='m-0'>".(!empty($pdata['vote_points']) ? $pdata['vote_points'] : 0)."</h3>\n";
						$pdata['post_votebox'] .= "<a href='".$pdata['vote_down']['link']."' class='btn btn-default btn-xs m-t-5 p-5' title='".$locale['forum_0265']."'>\n<i class='entypo down-dir icon-xs'></i></a>";
						$pdata['post_votebox'] .= "</div>\n";
					} else {
						$pdata['post_votebox'] = "<div class='text-center'>\n";
						$pdata['post_votebox'] .= "<h3 class='m-0'>".(!empty($pdata['vote_points']) ? $pdata['vote_points'] : 0)."</h3>\n";
						$pdata['post_votebox'] .= "</div>\n";
					}
				}

				// Edit Reason - NOT WORKING?
				$pdata['post_edit_reason'] = '';
				if ($pdata['post_edittime']) {
					$edit_reason = "<div class='edit_reason m-t-10'><small>".$locale['forum_0164'].profile_link($pdata['post_edituser'], $pdata['edit_name'], $pdata['edit_status']).$locale['forum_0167'].showdate("forumdate", $pdata['post_edittime'])."</small>\n";
					if ($pdata['post_editreason'] && iMEMBER) {
						$edit_reason .= "<br /><a id='reason_pid_".$pdata['post_id']."' rel='".$pdata['post_id']."' class='reason_button small' data-target='reason_div_pid_".$pdata['post_id']."'>";
						$edit_reason .= "<strong>".$locale['forum_0165']."</strong>";
						$edit_reason .= "</a>\n";
						$edit_reason .= "<div id='reason_div_pid_".$pdata['post_id']."' class='reason_div small'>".$pdata['post_editreason']."</div>\n";
					}
					$edit_reason .= "</div>\n";
					$pdata['post_edit_reason'] = $edit_reason;
					$this->edit_reason = TRUE;
				}

				// Custom Post Message Link/Buttons
				$pdata['post_links'] = '';
				$pdata['post_links'] .= !empty($pdata['post_quote']) ? "<a class='btn btn-xs btn-default' title='".$pdata['post_quote']["title"]."' href='".$pdata['post_quote']['link']."'>".$pdata['post_quote']['title']."</a>\n" : '';
				$pdata['post_links'] .= !empty($pdata['post_edit']) ? "<a class='btn btn-xs btn-default' title='".$pdata['post_edit']["title"]."' href='".$pdata['post_edit']['link']."'>".$pdata['post_edit']['title']."</a>\n" : '';
				$pdata['post_links'] .= !empty($pdata['print']) ? "<a class='btn btn-xs btn-default' title='".$pdata['print']["title"]."' href='".$pdata['print']['link']."'>".$pdata['print']['title']."</a>\n" : '';
				$pdata['post_links'] .= !empty($pdata['user_web']) ? "<a class='btn btn-xs btn-default' class='forum_user_actions' href='".$pdata['user_web']['link']."' target='_blank'>".$pdata['user_web']['title']."</a>\n" : '';
				$pdata['post_links'] .= !empty($pdata['user_message']) ? "<a class='btn btn-xs btn-default' href='".$pdata['user_message']['link']."' target='_blank'>".$pdata['user_message']['title']."</a>\n" : '';
				// Post Date
				$pdata['post_date'] = $locale['forum_0524']." ".timer($pdata['post_datestamp'])." - ".showdate('forumdate', $pdata['post_datestamp']);
				$pdata['post_shortdate'] = $locale['forum_0524']." ".timer($pdata['post_datestamp']);
				$pdata['post_longdate'] = $locale['forum_0524']." ".showdate('forumdate', $pdata['post_datestamp']);
				$this->thread_info['post_items'][$pdata['post_id']] = $pdata;
				$i++;
			}
		}
	}

	/**
	 * Validate whether a specific user has visited the thread.
	 * Duration : 7 days
	 * @param $thread_id
	 */
	static function increment_thread_views($thread_id) {
		$days_to_keep_session = 7;
		if (!isset($_SESSION['thread'][$thread_id])) {
			$_SESSION['thread'][$thread_id] = time();
			dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_views=thread_views+1 WHERE thread_id='".intval($thread_id)."'");
		} else {
			$time = $_SESSION['thread'][$thread_id];
			if ($time <= time()-($days_to_keep_session*3600*24)) {
				unset($_SESSION['thread'][$thread_id]);
			}
		}
	}

	public function get_thread_data() {
		return $this->thread_info;
	}

    /* Get participated users - parsing */
    public function get_participated_users($info) {
		$user = array();
		$result = dbquery("SELECT u.user_id, u.user_name, u.user_status, count(p.post_id) 'post_count'
                FROM ".DB_FORUM_POSTS." p
				INNER JOIN ".DB_USERS." u on (u.user_id=p.post_author)
				WHERE p.forum_id='".intval($info['thread']['forum_id'])."' AND p.thread_id='".intval($info['thread']['thread_id'])."' group by user_id");
		if (dbrows($result) > 0) {
			while ($data = dbarray($result)) {
				$user[$data['user_id']] = profile_link($data['user_id'], $data['user_name'], $data['user_status']);
			}
		}
		return $user;
	}

	/**
	 * New Status
	 */
	public function set_thread_visitor() {

		if (iMEMBER) {
            $userdata = fusion_get_userdata();

            $thread_match = $this->thread_info['thread_id']."\|".$this->thread_info['thread']['thread_lastpost']."\|".$this->thread_info['thread']['forum_id'];
			if (($this->thread_info['thread']['thread_lastpost'] > $this->thread_info['lastvisited']) && !preg_match("(^\.{$thread_match}$|\.{$thread_match}\.|\.{$thread_match}$)", $userdata['user_threads'])) {
				dbquery("UPDATE ".DB_USERS." SET user_threads='".$userdata['user_threads'].".".stripslashes($thread_match)."' WHERE user_id='".$userdata['user_id']."'");
			}
		}
	}

	public function render_reply_form() {

        $forum_settings = $this->get_forum_settings();

        $locale = fusion_get_locale("", FORUM_LOCALE);

        $userdata = fusion_get_userdata();

		$this->thread_data = $this->thread_info['thread'];
		if ((!iMOD or !iSUPERADMIN) && $this->thread_data['thread_locked']) redirect(INFUSIONS.'forum/index.php');
		if ($this->getThreadPermission("can_reply")) {
			add_to_title($locale['global_201'].$locale['forum_0503']);
			add_breadcrumb(array('link' => '', 'title' => $locale['forum_0503']));
			// field data
			$post_data = array(
				'post_id' => 0,
				'forum_id' => $this->thread_info['thread']['forum_id'],
				'thread_id' => $this->thread_info['thread']['thread_id'],
				'post_message' => isset($_POST['post_message']) ? form_sanitizer($_POST['post_message'], '', 'post_message') : '',
				'post_showsig' => isset($_POST['post_showsig']) ? 1 : 0,
				'post_smileys' => isset($_POST['post_smileys']) || isset($_POST['post_message']) && preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $_POST['post_message']) ? 1 : 0,
				'post_author' => $userdata['user_id'],
				'post_datestamp' => time(),
				'post_ip' => USER_IP,
				'post_ip_type' => USER_IP_TYPE,
				'post_edituser' => 0,
				'post_edittime' => 0,
				'post_editreason' => '',
				'post_hidden' => 0,
				'notify_me' => 0,
				'post_locked' => $forum_settings['forum_edit_lock'] || isset($_POST['post_locked']) ? 1 : 0,
			);

			// execute form post actions
			if (isset($_POST['post_reply'])) {

				require_once INCLUDES."flood_include.php";

				// all data is sanitized here.

                if (!flood_control("post_datestamp", DB_FORUM_POSTS, "post_author='".$userdata['user_id']."'")) { // have notice

                    // If you merge, the datestamp on all forum, threads, post will not be updated.
					$update_forum_lastpost = FALSE;

					if (\defender::safe()) {
						// Prepare forum merging action
						$last_post_author = dbarray(dbquery("
                        SELECT post_author FROM ".DB_FORUM_POSTS."
                        WHERE thread_id='".$this->thread_data['thread_id']."'
                        ORDER BY post_id DESC LIMIT 1
                        "));

                        if ($last_post_author['post_author'] == $post_data['post_author'] && $this->thread_data['forum_merge']) {

							$last_message = dbarray(dbquery("SELECT post_id, post_message, post_datestamp FROM ".DB_FORUM_POSTS." WHERE thread_id='".$this->thread_data['thread_id']."' ORDER BY post_id DESC"));
							$post_data['post_id'] = $last_message['post_id'];
							$post_data['post_message'] = $last_message['post_message']."\n\n".$locale['forum_0640']." ".showdate("longdate", time()).":\n".$post_data['post_message'];
                            $post_data['post_datestamp'] = $last_message['post_datestamp'];
                            // do not use new time


							dbquery_insert(DB_FORUM_POSTS, $post_data, 'update', array('primary_key' => 'post_id', 'keep_session' => TRUE));

                        } else {

							$update_forum_lastpost = TRUE;

							dbquery_insert(DB_FORUM_POSTS, $post_data, 'save', array('primary_key' => 'post_id', 'keep_session' => TRUE));
							$post_data['post_id'] = dblastid();
							if (!defined("FUSION_NULL")) dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts+1 WHERE user_id='".$post_data['post_author']."'");
						}

						// Attach files if permitted
						if (!empty($_FILES) && is_uploaded_file($_FILES['file_attachments']['tmp_name'][0]) && $this->getThreadPermission("can_upload_attach")) {
							$upload = form_sanitizer($_FILES['file_attachments'], '', 'file_attachments');
							if ($upload['error'] == 0) {
								foreach ($upload['target_file'] as $arr => $file_name) {
									$adata = array('thread_id' => $this->thread_data['thread_id'],
										'post_id' => $post_data['post_id'],
										'attach_name' => $file_name,
										'attach_mime' => $upload['type'][$arr],
										'attach_size' => $upload['source_size'][$arr],
										'attach_count' => 0, // downloaded times
									);
									dbquery_insert(DB_FORUM_ATTACHMENTS, $adata, "save", array('keep_session' => TRUE));
								}
							}
						}

						// Update stats in forum and threads
						if ($update_forum_lastpost == TRUE) {

							// find all parents and update them
							$list_of_forums = get_all_parent(dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat'), $this->thread_data['forum_id']);
							foreach ($list_of_forums as $fid) {
								dbquery("
								UPDATE ".DB_FORUMS." SET
								forum_lastpost = '".time()."',
								forum_postcount=forum_postcount+1,
								forum_lastpostid='".$post_data['post_id']."',
								forum_lastuser='".$post_data['post_author']."'
								WHERE forum_id='".$fid."'
								");
							}

							// update current forum
							dbquery("
							UPDATE ".DB_FORUMS." SET
							forum_lastpost='".time()."',
							forum_postcount=forum_postcount+1,
							forum_lastpostid='".$post_data['post_id']."',
							forum_lastuser='".$post_data['post_author']."'
							WHERE forum_id='".$this->thread_data['forum_id']."'
							");

							// update current thread
							dbquery("
							UPDATE ".DB_FORUM_THREADS." SET
							thread_lastpost='".time()."',
							thread_lastpostid='".$post_data['post_id']."',
							thread_postcount=thread_postcount+1,
							thread_lastuser='".$post_data['post_author']."',
							thread_lastpost= '".time()."'
							WHERE thread_id='".$this->thread_data['thread_id']."'
							");
						}

						if ($forum_settings['thread_notify'] && isset($_POST['notify_me'])) {
							if (!dbcount("(thread_id)", DB_FORUM_THREAD_NOTIFY, "thread_id='".$this->thread_data['thread_id']."' AND notify_user='".$post_data['post_author']."'")) {
								dbquery("INSERT INTO ".DB_FORUM_THREAD_NOTIFY." (thread_id, notify_datestamp, notify_user, notify_status) VALUES('".$this->thread_data['thread_id']."', NOW(), '".$post_data['post_author']."', '1')");
							}
						}
                        if (\defender::safe()) {
                            redirect(INFUSIONS."forum/postify.php?post=reply&error=0&amp;forum_id=".intval($post_data['forum_id'])."&amp;thread_id=".intval($post_data['thread_id'])."&amp;post_id=".intval($post_data['post_id']));
                        }
					}
				}
			}

			// template data
            $form_action = INFUSIONS."forum/viewthread.php?action=reply&amp;forum_id=".$this->thread_data['forum_id']."&amp;thread_id=".$this->thread_data['thread_id'];
			// Quote Get
			if (isset($_GET['quote']) && isnum($_GET['quote'])) {
				$quote_result = dbquery("SELECT a.post_message, b.user_name
										FROM ".DB_FORUM_POSTS." a
										INNER JOIN ".DB_USERS." b ON a.post_author=b.user_id
										WHERE thread_id='".$this->thread_data['thread_id']."' and post_id='".$_GET['quote']."'");
				if (dbrows($quote_result) > 0) {
					$quote_data = dbarray($quote_result);
					// do not do this. to silently inject.
					$post_data['post_message'] = "[quote name=".$quote_data['user_name']." post=".$_GET['quote']."]@".$quote_data['user_name']." - ".strip_bbcodes($quote_data['post_message'])."[/quote]".$post_data['post_message'];
					$form_action .= "&amp;post_id=".$_GET['post_id']."&amp;quote=".$_GET['quote'];
				} else {
                    redirect(INFUSIONS."forum/index.php");
				}
			}

			$info = array(
				'title' => $locale['forum_0503'],
				'description' => $locale['forum_2000'].$this->thread_data['thread_subject'],
				'openform' => openform('input_form', 'post', $form_action, array('enctype' => $this->getThreadPermission("can_upload_attach") ? TRUE : FALSE, 'max_tokens' => 1)),
				'closeform' => closeform(),
				'forum_id_field' => form_hidden('forum_id', "", $post_data['forum_id']),
				'thread_id_field' => form_hidden('thread_id', "", $post_data['thread_id']),
				"forum_field" => "",
				'subject_field' => form_hidden('thread_subject', "", $this->thread_data['thread_subject']),
				'message_field' => form_textarea('post_message', $locale['forum_0601'], $post_data['post_message'],
												 array(
													'required' => TRUE,
													'error_text' => '',
													'autosize' => TRUE,
													'no_resize' => TRUE,
													'preview' => TRUE,
													'form_name' => 'input_form',
													'bbcode' => TRUE
												 )),
				// happens only in EDIT
				'delete_field' => '',
				'edit_reason_field' => '',
				'attachment_field' => $this->getThreadPermission("can_upload_attach") ?
						form_fileinput('file_attachments[]', $locale['forum_0557'], "",
									   array(	'input_id' => 'file_attachments',
												'upload_path' => INFUSIONS.'forum/attachments/',
												'type' => 'object',
												'preview_off' => TRUE,
												"multiple" => TRUE,
										  		"inline" => false,
												'max_count' => $forum_settings['forum_attachmax_count'],
												'valid_ext' => $forum_settings['forum_attachtypes'],
										   "class"=>"m-b-0",
										))."
								 <div class='m-b-20'>\n<small>".sprintf($locale['forum_0559'], parsebytesize($forum_settings['forum_attachmax']), str_replace('|', ', ', $forum_settings['forum_attachtypes']), $forum_settings['forum_attachmax_count'])."</small>\n</div>\n"
					 : "",
				"poll_form" => "",
				'smileys_field' => form_checkbox('post_smileys', $locale['forum_0622'], $post_data['post_smileys'], array('class' => 'm-b-0')),
				'signature_field' => (array_key_exists("user_sig", $userdata) && $userdata['user_sig']) ? form_checkbox('post_showsig', $locale['forum_0623'], $post_data['post_showsig'], array('class' => 'm-b-0')) : '',
				'sticky_field' => '',
				'lock_field' => '',
				'hide_edit_field' => '',
				'post_locked_field' => '',
				// not available in edit mode.
				'notify_field' => $forum_settings['thread_notify'] ? form_checkbox('notify_me', $locale['forum_0626'], $post_data['notify_me'], array('class' => 'm-b-0')) : '',
				'post_buttons' => form_button('post_reply', $locale['forum_0504'], $locale['forum_0504'], array('class' => 'btn-primary')).form_button('cancel', $locale['cancel'], $locale['cancel'], array('class' => 'btn-default m-l-10')),
				'last_posts_reply' => ''
			);
			// only in reply
			if ($forum_settings['forum_last_posts_reply']) {
				$result = dbquery("
				SELECT
				p.thread_id, p.post_message, p.post_smileys, p.post_author, p.post_datestamp, p.post_hidden,
							u.user_id, u.user_name, u.user_status, u.user_avatar
							FROM ".DB_FORUM_POSTS." p
							LEFT JOIN ".DB_USERS." u ON p.post_author = u.user_id
							WHERE p.thread_id='".$this->thread_data['thread_id']."' AND p.post_hidden='0'
							GROUP BY p.post_id
							ORDER BY p.post_datestamp DESC LIMIT 0,".$forum_settings['posts_per_page']
				);
				if (dbrows($result)) {
					$title = sprintf($locale['forum_0526'], $forum_settings['forum_last_posts_reply']);
					if ($forum_settings['forum_last_posts_reply'] == "1") {
						$title = $locale['forum_0525'];
					}
					ob_start();
					echo "<p><strong>".$title."</strong>\n</p>\n";
					echo "<table class='table table-responsive'>\n";
					$i = $forum_settings['posts_per_page'];
					while ($data = dbarray($result)) {
						$message = $data['post_message'];
						if ($data['post_smileys']) {
							$message = parsesmileys($message);
						}
						$message = parseubb($message);
						echo "<tr>\n<td class='tbl2 forum_thread_user_name' style='width:10%'><!--forum_thread_user_name-->".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</td>\n";
						echo "<td class='tbl2 forum_thread_post_date'>\n";
						echo "<div style='float:right' class='small'>\n";
						echo $i.($i == $forum_settings['forum_last_posts_reply'] ? " (".$locale['forum_0525'].")" : "");
						echo "</div>\n";
						echo "<div class='small'>".$locale['forum_0524'].showdate("forumdate", $data['post_datestamp'])."</div>\n";
						echo "</td>\n";
						echo "</tr>\n<tr>\n<td valign='top' class='tbl2 forum_thread_user_info' style='width:10%'>\n";
						echo display_avatar($data, '50px');
						echo "</td>\n<td valign='top' class='tbl1 forum_thread_user_post'>\n";
						echo nl2br($message);
						echo "</td>\n</tr>\n";
						$i--;
					}
					echo "</table>\n";
					$info['last_posts_reply'] = ob_get_contents();
					ob_end_clean();
				}
			}
			display_forum_postform($info);
		} else {
			if (fusion_get_settings("site_seo")) {
                redirect(fusion_get_settings("siteurl")."infusions/forum/index.php");
            }
            redirect(INFUSIONS.'forum/index.php');
		}
	}

	public function render_edit_form() {

        $forum_settings = $this->get_forum_settings();

        $locale = fusion_get_locale("", FORUM_LOCALE);

        $userdata = fusion_get_userdata();

		$this->thread_data = $this->thread_info['thread'];

		if ((!iMOD or !iSUPERADMIN) && $this->thread_data['thread_locked']) redirect(INFUSIONS.'forum/index.php');

		if (isset($_GET['post_id']) && isnum($_GET['post_id'])) {

			add_to_title($locale['global_201'].$locale['forum_0503']);
			add_breadcrumb(array('link' => '', 'title' => $locale['forum_0503']));

			$result = dbquery("SELECT tp.*, tt.thread_subject, tt.thread_poll, tt.thread_author, tt.thread_locked, MIN(tp2.post_id) AS first_post
				FROM ".DB_FORUM_POSTS." tp
				INNER JOIN ".DB_FORUM_THREADS." tt on tp.thread_id=tt.thread_id
				INNER JOIN ".DB_FORUM_POSTS." tp2 on tp.thread_id=tp2.thread_id
				WHERE tp.post_id='".intval($_GET['post_id'])."' AND tp.thread_id='".intval($this->thread_data['thread_id'])."' AND tp.forum_id='".intval($this->thread_data['forum_id'])."'
				GROUP BY tp2.post_id
				");

			if (dbrows($result) > 0) {

				$post_data = dbarray($result);

				if ((iMOD or iSUPERADMIN) || ($this->getThreadPermission("can_reply") && $post_data['post_author'] == $userdata['user_id'])) {

					$is_first_post = ($post_data['post_id'] == $this->thread_info['post_firstpost']) ? TRUE : FALSE;

					// no edit if locked
					if ($post_data['post_locked'] && !iMOD) {
                        redirect(INFUSIONS."forum/postify.php?post=edit&error=5&forum_id=".$this->thread_data['forum_id']."&thread_id=".$this->thread_data['thread_id']."&post_id=".$post_data['post_id']);
					}

					// no edit if time limit reached
					if (!iMOD && ($forum_settings['forum_edit_timelimit'] > 0 && (time()-$forum_settings['forum_edit_timelimit']*60) > $post_data['post_datestamp'])) {
                        redirect(INFUSIONS."forum/postify.php?post=edit&error=6&forum_id=".$this->thread_data['forum_id']."&thread_id=".$this->thread_data['thread_id']."&post_id=".$post_data['post_id']);
					}

					// execute form post actions
					if (isset($_POST['post_edit'])) {
						require_once INCLUDES."flood_include.php";
						// all data is sanitized here.
						if (!flood_control("post_datestamp", DB_FORUM_POSTS, "post_author='".$userdata['user_id']."'")) { // have notice
							$post_data = array(
								'forum_id' => $this->thread_data['forum_id'],
								'thread_id' => $this->thread_data['thread_id'],
								'post_id' => $post_data['post_id'],
								"thread_subject" => "",
								'post_message' => form_sanitizer($_POST['post_message'], '', 'post_message'),
								'post_showsig' => isset($_POST['post_showsig']) ? 1 : 0,
								'post_smileys' => isset($_POST['post_smileys']) || isset($_POST['post_message']) && preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $_POST['post_message']) ? 1 : 0,
								'post_author' => $userdata['user_id'],
								'post_datestamp' => $post_data['post_datestamp'], // update on datestamp or not?
								'post_ip' => USER_IP,
								'post_ip_type' => USER_IP_TYPE,
								'post_edituser' => $userdata['user_id'],
								'post_edittime' => time(),
								'post_editreason' => form_sanitizer($_POST['post_editreason'], '', 'post_editreason'),
								'post_hidden' => 0,
								'notify_me' => 0,
								'post_locked' => $forum_settings['forum_edit_lock'] || isset($_POST['post_locked']) ? 1 : 0
							);

							// require thread_subject if first post
							if ($is_first_post) {
								$post_data['thread_subject'] = form_sanitizer($_POST['thread_subject'], '', 'thread_subject');
                                $this->thread_data['thread_subject'] = $post_data['thread_subject'];
							}

							if (\defender::safe()) {

                                // Update thread subject
                                if ($is_first_post) {
                                    dbquery_insert(DB_FORUM_THREADS, $this->thread_data, "update", array("keep_session"=>TRUE));
                                }

								// Prepare forum merging action
								$last_post_author = dbarray(dbquery("SELECT post_author FROM ".DB_FORUM_POSTS." WHERE thread_id='".$this->thread_data['thread_id']."' ORDER BY post_id DESC LIMIT 1"));
								if ($last_post_author == $post_data['post_author'] && $this->thread_data['forum_merge']) {
									$last_message = dbarray(dbquery("SELECT post_id, post_message FROM ".DB_FORUM_POSTS." WHERE thread_id='".$this->thread_data['thread_id']."' ORDER BY post_id DESC"));
									$post_data['post_id'] = $last_message['post_id'];
									$post_data['post_message'] = $last_message['post_message']."\n\n".$locale['forum_0640']." ".showdate("longdate", time()).":\n".$post_data['post_message'];
                                    print_p($post_data);
									dbquery_insert(DB_FORUM_POSTS, $post_data, 'update', array('primary_key' => 'post_id','keep_session' => TRUE));
								} else {
									dbquery_insert(DB_FORUM_POSTS, $post_data, 'update', array('primary_key' => 'post_id', 'keep_session' => TRUE));
								}

								// Delete attachments if there is any
								foreach ($_POST as $key => $value) {
									if (!strstr($key, "delete_attach")) continue;
									$key = str_replace("delete_attach_", "", $key);
									$result = dbquery("SELECT * FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".$post_data['post_id']."' AND attach_id='".(isnum($key) ? $key : 0)."'");
									if (dbrows($result) != 0 && $value) {
										$adata = dbarray($result);
										unlink(FORUM."attachments/".$adata['attach_name']);
										dbquery("DELETE FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".$post_data['post_id']."' AND attach_id='".(isnum($key) ? $key : 0)."'");
									}
								}

								if (!empty($_FILES) && is_uploaded_file($_FILES['file_attachments']['tmp_name'][0]) && $this->getThreadPermission("can_upload_attach")) {
									$upload = form_sanitizer($_FILES['file_attachments'], '', 'file_attachments');
									if ($upload['error'] == 0) {
										foreach ($upload['target_file'] as $arr => $file_name) {
											$attachment = array('thread_id' => $this->thread_data['thread_id'],
												'post_id' => $post_data['post_id'],
												'attach_name' => $file_name,
												'attach_mime' => $upload['type'][$arr],
												'attach_size' => $upload['source_size'][$arr],
												'attach_count' => '0', // downloaded times?
											);
											dbquery_insert(DB_FORUM_ATTACHMENTS, $attachment, 'save', array('keep_session' => TRUE));
										}
									}
								}

								if (\defender::safe()) {
                                    redirect(INFUSIONS."forum/postify.php?post=edit&error=0&amp;forum_id=".intval($post_data['forum_id'])."&amp;thread_id=".intval($post_data['thread_id'])."&amp;post_id=".intval($post_data['post_id']));
								}
							}
						}
					}

					// template data
                    $form_action = INFUSIONS."forum/viewthread.php?action=edit&amp;forum_id=".$this->thread_data['forum_id']."&amp;thread_id=".$this->thread_data['thread_id']."&amp;post_id=".$_GET['post_id'];

					// get attachment.
					$attachments = array();
					$attach_rows = 0;
					if ($this->getThreadPermission("can_upload_attach") && !empty($this->thread_info['post_items'][$post_data['post_id']]['post_attachments'])) { // need id
						$a_result = dbquery("SELECT * FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".intval($post_data['post_id'])."' AND thread_id='".intval($this->thread_data['thread_id'])."'");
						$attach_rows = dbrows($a_result);
						if ($attach_rows > 0) {
							while ($a_data = dbarray($a_result)) {
								$attachments[] = $a_data;
							}
						}
					}

					$info = array(
						'title' => $locale['forum_0507'],
						'description' => $locale['forum_2000'].$this->thread_data['thread_subject'],
						'openform' => openform('input_form', 'post', $form_action, array('enctype' => $this->getThreadPermission("can_upload_attach") ? TRUE : FALSE)),
						'closeform' => closeform(),
						'forum_id_field' => form_hidden('forum_id', '', $post_data['forum_id']),
						'thread_id_field' => form_hidden('thread_id', '', $post_data['thread_id']),
						"forum_field" => "",
						'subject_field' => $this->thread_info['post_firstpost'] == $_GET['post_id'] ?
								form_text('thread_subject', $locale['forum_0600'], $this->thread_data['thread_subject'],
										  array('required' => TRUE,
												'placeholder' => $locale['forum_2001'],
												"class" => 'm-t-20 m-b-20'))
								: form_hidden("thread_subject", "", $this->thread_data['thread_subject']),
						'message_field' => form_textarea('post_message', $locale['forum_0601'], $post_data['post_message'],
														 array('required' => TRUE,
																'autosize' => TRUE,
																'no_resize' => TRUE,
																'preview' => TRUE,
																'form_name' => 'input_form',
																'bbcode' => TRUE
														 )),
						// happens only in EDIT
						'delete_field' => form_checkbox('delete', $locale['forum_0624'], '', array('class' => 'm-b-0')),
						'edit_reason_field' => form_text('post_editreason', $locale['forum_0611'], $post_data['post_editreason'], array('placeholder' => '','class' => 'm-t-20 m-b-20')),
						'attachment_field' => $this->getThreadPermission("can_upload_attach") ?
										form_fileinput('file_attachments[]', $locale['forum_0557'], "",
												 array('input_id' => 'file_attachments',
														'upload_path' => INFUSIONS.'forum/attachments/',
														'type' => 'object',
														'preview_off' => TRUE,
														'multiple' => TRUE,
														'max_count' => $attach_rows > 0 ?  $forum_settings['forum_attachmax_count']-$attach_rows : $forum_settings['forum_attachmax_count'],
														'valid_ext' => $forum_settings['forum_attachtypes']))."
														 <div class='m-b-20'>\n<small>".sprintf($locale['forum_0559'], parsebytesize($forum_settings['forum_attachmax']), str_replace('|', ', ', $forum_settings['forum_attachtypes']), $forum_settings['forum_attachmax_count'])."</small>\n</div>\n"
										 : "",
						// only happens during edit on first post or new thread AND has poll -- info['forum_poll'] && checkgroup($info['forum_poll']) && ($data['edit'] or $data['new']
						"poll_form" => "",
						'smileys_field' => form_checkbox('post_smileys', $locale['forum_0622'], $post_data['post_smileys'], array('class' => 'm-b-0')),
						'signature_field' => (array_key_exists("user_sig", $userdata) && $userdata['user_sig']) ? form_checkbox('post_showsig', $locale['forum_0623'], $post_data['post_showsig'], array('class' => 'm-b-0')) : '',
						//sticky only in new thread or edit first post
						'sticky_field' => ((iMOD || iSUPERADMIN) && $is_first_post) ? form_checkbox('thread_sticky', $locale['forum_0620'], $this->thread_data['thread_sticky'], array('class' => 'm-b-0')) : '',
						'lock_field' => (iMOD || iSUPERADMIN) ? form_checkbox('thread_locked', $locale['forum_0621'], $this->thread_data['thread_locked'], array('class' => 'm-b-0')) : '',
						'hide_edit_field' => form_checkbox('hide_edit', $locale['forum_0627'], '', array('class' => 'm-b-0')),
						// edit mode only
						'post_locked_field' => (iMOD || iSUPERADMIN) ? form_checkbox('post_locked', $locale['forum_0628'], $post_data['post_locked'], array('class' => 'm-b-0')) : '',
						// edit mode only
						// not available in edit mode.
						'notify_field' => '',
						//$forum_settings['thread_notify'] ? form_checkbox('notify_me', $locale['forum_0626'], $post_data['notify_me'], array('class' => 'm-b-0')) : '',
						'post_buttons' => form_button('post_edit', $locale['forum_0504'], $locale['forum_0504'], array('class' => 'btn-primary')).form_button('cancel', $locale['cancel'], $locale['cancel'], array('class' => 'btn-default m-l-10')),
						'last_posts_reply' => ''
					);
					$a_info = '';
					if (!empty($attachments)) {
						foreach($attachments as $a_data) {
							$a_info .= "<label><input type='checkbox' name='delete_attach_".$a_data['attach_id']."' value='1' /> ".$locale['forum_0625']."</label>\n"."<a href='".INFUSIONS."forum/attachments/".$a_data['attach_name']."'>".$a_data['attach_name']."</a> [".parsebytesize($a_data['attach_size'])."]\n"."<br/>\n";
						}
						$info['attachment_field'] = $a_info.$info['attachment_field'];
					}

                    display_forum_postform($info);

				} else {
                    if (fusion_get_settings("site_seo")) {
                        redirect(fusion_get_settings("siteurl")."infusions/forum/index.php");
                    }
					redirect(INFUSIONS.'forum/index.php'); // no access
				}
			} else {
                redirect(INFUSIONS."forum/postify.php?post=edit&error=4&forum_id=".$this->thread_data['forum_id']."&thread_id=".$this->thread_data['thread_id']."&post_id=".$_GET['post_id']);
			}
		} else {
            if (fusion_get_settings("site_seo")) {
                redirect(fusion_get_settings("siteurl")."infusions/forum/index.php");
            }
			redirect(INFUSIONS.'forum/index.php');
		}
	}

	private function set_ThreadJs() {
		$viewthread_js = '';
		//javascript to footer
		$highlight_js = "";
		$colorbox_js = "";
		$edit_reason_js = '';
		/** javascript **/
		// highlight jQuery plugin
		if (isset($_GET['highlight'])) {
			$words = explode(" ", urldecode($_GET['highlight']));
			$higlight = "";
			$i = 1;
			$c_words = count($words);
			foreach ($words as $hlight) {
				$hlight = htmlentities($hlight, ENT_QUOTES);
				$higlight .= "'".$hlight."'";
				$higlight .= ($i < $c_words ? "," : "");
				$i++;
			}
			add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/jquery.highlight.js'></script>");
			$highlight_js .= "$('.search_result').highlight([".$higlight."],{wordsOnly:true});";
			$highlight_js .= "$('.highlight').css({backgroundColor:'#FFFF88'});"; //better via theme or settings
		}
		$edit_reason_js .= "
			$('.reason_div').hide();
			$('div').find('.reason_button').css({cursor: 'pointer' });
			$('.reason_button').bind('click', function(e) {
				var target = $(this).data('target');
				$('#'+target).stop().slideToggle('fast');
			});
			";
		// viewthread javascript, moved to footer
		if (!empty($highlight_js) || !empty($colorbox_js) || !empty($edit_reason_js)) {
			$viewthread_js .= $highlight_js.$colorbox_js.$edit_reason_js;
		}
		$viewthread_js .= "$('a[href=#top]').click(function(){";
		$viewthread_js .= "$('html, body').animate({scrollTop:0}, 'slow');";
		$viewthread_js .= "return false;";
		$viewthread_js .= "});";
		$viewthread_js .= "});";
		// below functions could be made more unobtrusive thanks to jQuery, giving a more accessible cms
		$viewthread_js .= "function jumpforum(forum_id){";
		$viewthread_js .= "document.location.href='".INFUSIONS."forum/viewforum.php?forum_id='+forum_id;";
		$viewthread_js .= "}";
		if (iMOD) { // only moderators need this javascript
			$viewthread_js .= "function setChecked(frmName,chkName,val){";
			$viewthread_js .= "dml=document.forms[frmName];";
			$viewthread_js .= "len=dml.elements.length;";
			$viewthread_js .= "for(i=0;i<len;i++){";
			$viewthread_js .= "if(dml.elements[i].name==chkName){";
			$viewthread_js .= "dml.elements[i].checked=val;";
			$viewthread_js .= "}";
			$viewthread_js .= "}";
			$viewthread_js .= "}";
		}
		//$viewthread_js .= "/*]]>*/";
		//$viewthread_js .= "</script>";
		add_to_jquery($viewthread_js);
	}
}