<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_name_middle_include_var.php
| Author: Chubatyj Vitalij (Rizado)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;
include __DIR__.'/locale/'.LANGUAGE.'.php';
// Version of the user fields api
$user_field_api_version = "1.01.00";
$user_field_name = $locale['uf_name_middle'];
$user_field_desc = $locale['uf_name_middle_desc'];
$user_field_dbname = "user_name_middle";
$user_field_group = 2;
$user_field_dbinfo = "VARCHAR(20) NOT NULL DEFAULT ''";
