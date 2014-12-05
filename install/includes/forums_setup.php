<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: includes/forums_setup.php
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
if (isset($_POST['uninstall'])) {
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."forum_attachments");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."forum_ranks");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."forum_poll_options");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."forum_poll_voters");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."forum_polls");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."forums");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."posts");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."threads");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."thread_notify");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."forum_votes");
} else {
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."forum_attachments");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."forum_ranks");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."forum_poll_options");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."forum_poll_voters");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."forum_polls");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."forums");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."posts");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."threads");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."thread_notify");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."forum_votes");

	$result = dbquery("CREATE TABLE ".$db_prefix."forum_attachments (
			attach_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
			thread_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			post_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			attach_name VARCHAR(100) NOT NULL DEFAULT '',
			attach_mime VARCHAR(20) NOT NULL DEFAULT '',
			attach_size INT(20) UNSIGNED NOT NULL DEFAULT '0',
			attach_count INT(10) UNSIGNED NOT NULL DEFAULT '0',
			PRIMARY KEY (attach_id)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	if (!$result) {
		$fail = TRUE;
	}
	$result = dbquery("CREATE TABLE ".$db_prefix."forum_votes (
			forum_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			thread_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			post_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			vote_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			vote_points DECIMAL(3,0) NOT NULL DEFAULT '',
			vote_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	if (!$result) {
		$fail = TRUE;
	}
	$result = dbquery("CREATE TABLE ".$db_prefix."forum_ranks (
			rank_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
			rank_title VARCHAR(100) NOT NULL DEFAULT '',
			rank_image VARCHAR(100) NOT NULL DEFAULT '',
			rank_posts iNT(10) UNSIGNED NOT NULL DEFAULT '0',
			rank_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
			rank_apply SMALLINT(5) UNSIGNED NOT NULL DEFAULT '101',
			rank_language VARCHAR(50) NOT NULL DEFAULT '".$_POST['localeset']."',
			PRIMARY KEY (rank_id)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	if (!$result) {
		$fail = TRUE;
	}
	$result = dbquery("CREATE TABLE ".$db_prefix."forum_poll_options (
			thread_id MEDIUMINT(8) unsigned NOT NULL,
			forum_poll_option_id SMALLINT(5) UNSIGNED NOT NULL,
			forum_poll_option_text VARCHAR(150) NOT NULL,
			forum_poll_option_votes SMALLINT(5) UNSIGNED NOT NULL,
			KEY thread_id (thread_id)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	if (!$result) {
		$fail = TRUE;
	}
	$result = dbquery("CREATE TABLE ".$db_prefix."forum_poll_voters (
			thread_id MEDIUMINT(8) UNSIGNED NOT NULL,
			forum_vote_user_id MEDIUMINT(8) UNSIGNED NOT NULL,
			forum_vote_user_ip VARCHAR(45) NOT NULL,
			forum_vote_user_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
			KEY thread_id (thread_id,forum_vote_user_id)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	if (!$result) {
		$fail = TRUE;
	}
	$result = dbquery("CREATE TABLE ".$db_prefix."forum_polls (
			thread_id MEDIUMINT(8) UNSIGNED NOT NULL,
			forum_poll_title VARCHAR(250) NOT NULL,
			forum_poll_start INT(10) UNSIGNED DEFAULT NULL,
			forum_poll_length iNT(10) UNSIGNED NOT NULL,
			forum_poll_votes SMALLINT(5) unsigned NOT NULL,
			KEY thread_id (thread_id)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	if (!$result) {
		$fail = TRUE;
	}
	$result = dbquery("CREATE TABLE ".$db_prefix."forums (
			forum_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
			forum_cat MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			forum_branch MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			forum_name VARCHAR(50) NOT NULL DEFAULT '',
			forum_type TINYINT(1) NOT NULL DEFAULT '1',
			forum_answer_threshold TINYINT(3) NOT NULL DEFAULT '0',
			forum_lock TINYINT(1) NOT NULL DEFAULT '0',
			forum_order SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
			forum_description TEXT NOT NULL,
			forum_rules TEXT NOT NULL,
			forum_mods TEXT NOT NULL,
			forum_access TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
			forum_post SMALLINT(3) UNSIGNED DEFAULT '101',
			forum_reply SMALLINT(3) UNSIGNED DEFAULT '101',
			forum_poll SMALLINT(3) UNSIGNED NOT NULL DEFAULT '101',
			forum_vote SMALLINT(3) UNSIGNED NOT NULL DEFAULT '101',
			forum_image VARCHAR(100) NOT NULL DEFAULT '',
			forum_allow_ratings SMALLINT(1) UNSIGNED NOT NULL DEFAULT '0',
			forum_post_ratings SMALLINT(3) UNSIGNED NOT NULL DEFAULT '101',
			forum_users TINYINT(1) NOT NULL DEFAULT '0',
			forum_allow_attach SMALLINT(3) UNSIGNED NOT NULL DEFAULT '0',
			forum_attach_download SMALLINT(3) UNSIGNED NOT NULL DEFAULT'0',
			forum_quick_edit TINYINT(1) NOT NULL DEFAULT '0',
			forum_lastpostid MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '',
			forum_lastpost INT(10) UNSIGNED NOT NULL DEFAULT '0',
			forum_postcount MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			forum_threadcount MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			forum_lastuser MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			forum_merge TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
			forum_language VARCHAR(50) NOT NULL DEFAULT '".$_POST['localeset']."',
			forum_meta TEXT NOT NULL,
			forum_alias VARCHAR(50) NOT NULL DEFAULT '',
			PRIMARY KEY (forum_id),
			KEY forum_order (forum_order),
			KEY forum_lastpost (forum_lastpost),
			KEY forum_postcount (forum_postcount),
			KEY forum_threadcount (forum_threadcount)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	if (!$result) {
		$fail = TRUE;
	}
	$result = dbquery("CREATE TABLE ".$db_prefix."posts (
			forum_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			thread_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			post_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
			post_message TEXT NOT NULL,
			post_showsig TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
			post_smileys TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
			post_author MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			post_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
			post_ip VARCHAR(45) NOT NULL DEFAULT '',
			post_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
			post_edituser MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			post_edittime INT(10) UNSIGNED NOT NULL DEFAULT '0',
			post_editreason TEXT NOT NULL,
			post_hidden TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
			post_locked TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
			PRIMARY KEY (post_id),
			KEY thread_id (thread_id),
			KEY post_datestamp (post_datestamp)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	if (!$result) {
		$fail = TRUE;
	}
	$result = dbquery("CREATE TABLE ".$db_prefix."threads (
			forum_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			thread_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
			thread_subject VARCHAR(100) NOT NULL DEFAULT '',
			thread_author MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			thread_views MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			thread_lastpost INT(10) UNSIGNED NOT NULL DEFAULT '0',
			thread_lastpostid MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			thread_lastuser MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			thread_postcount SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
			thread_poll TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
			thread_sticky TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
			thread_locked TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
			thread_hidden TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
			PRIMARY KEY (thread_id),
			KEY thread_postcount (thread_postcount),
			KEY thread_lastpost (thread_lastpost),
			KEY thread_views (thread_views)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	if (!$result) {
		$fail = TRUE;
	}
	$result = dbquery("CREATE TABLE ".$db_prefix."thread_notify (
			thread_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			notify_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
			notify_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			notify_status tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
			KEY notify_datestamp (notify_datestamp)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	if (!$result) {
		$fail = TRUE;
	}
	// Local inserts
	$enabled_languages = explode('.', $settings['enabled_languages']);
	for ($i = 0; $i < sizeof($enabled_languages); $i++) {
		include LOCALE.$enabled_languages[$i]."/setup.php";
		$result = dbquery("INSERT INTO ".$db_prefix."forum_ranks VALUES ('', '".$locale['200']."', 'rank_super_admin.png', 0, '1', 103, '".$enabled_languages[$i]."')");
		$result = dbquery("INSERT INTO ".$db_prefix."forum_ranks VALUES ('', '".$locale['201']."', 'rank_admin.png', 0, '1', 102, '".$enabled_languages[$i]."')");
		$result = dbquery("INSERT INTO ".$db_prefix."forum_ranks VALUES ('', '".$locale['202']."', 'rank_mod.png', 0, '1', 104, '".$enabled_languages[$i]."')");
		$result = dbquery("INSERT INTO ".$db_prefix."forum_ranks VALUES ('', '".$locale['203']."', 'rank0.png', 0, '0', 101, '".$enabled_languages[$i]."')");
		$result = dbquery("INSERT INTO ".$db_prefix."forum_ranks VALUES ('', '".$locale['204']."', 'rank1.png', 10, '0', 101, '".$enabled_languages[$i]."')");
		$result = dbquery("INSERT INTO ".$db_prefix."forum_ranks VALUES ('', '".$locale['205']."', 'rank2.png', 50, '0', 101, '".$enabled_languages[$i]."')");
		$result = dbquery("INSERT INTO ".$db_prefix."forum_ranks VALUES ('', '".$locale['206']."', 'rank3.png', 200, '0', 101, '".$enabled_languages[$i]."')");
		$result = dbquery("INSERT INTO ".$db_prefix."forum_ranks VALUES ('', '".$locale['207']."', 'rank4.png', 500, '0', 101, '".$enabled_languages[$i]."')");
		$result = dbquery("INSERT INTO ".$db_prefix."forum_ranks VALUES ('', '".$locale['208']."', 'rank5.png', 1000, '0', 101, '".$enabled_languages[$i]."')");
	}
}



?>