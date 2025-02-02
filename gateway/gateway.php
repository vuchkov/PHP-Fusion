<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: gateway.php
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
defined('IN_FUSION') || exit;

/**
 * Experimental Anti Bot Gateway that combine multiple methods to prevent auto bots.
 */
require_once "constants_include.php";
require_once "functions_include.php";

// Terminate and ban all excessive access atempts.
antiflood_countaccess();

// Flag for pass, just increment on amount of checks we add.
$multiplier = 0;
$reply_method = '';

$info = [
    'showform'         => FALSE,
    'incorrect_answer' => FALSE
];
// print_P($_POST);
session_add('validated', 'FALSE');

if (!post('gateway_submit') && !post('register')) {

    // Get some numbers up. Always keep an odd number to void 10-10 etc.
    $a = rand(11, 20);
    $b = rand(1, 10);

    if ($a > 15) {
        $antibot = intval($a + $b);
        $multiplier = "+";
        $reply_method = $locale['gateway_062'];
        $a = convertNumberToWord($a);
        $antibot = convertNumberToWord($antibot);
        // $_SESSION["antibot"] = strtolower($antibot);
        session_add('antibot', strtolower($antibot));
    } else {
        $antibot = intval($a - $b);
        $multiplier = "-";
        $reply_method = $locale['gateway_063'];
        //$_SESSION["antibot"] = intval($antibot);
        session_add('antibot', (int) $antibot);
        $b = convertNumberToWord($b);
    }

    $a = str_rot47($a);
    $b = str_rot47($b);

    echo "<noscript>".$locale['gateway_052']."</noscript>";

    // Just add fields to random
    $honeypot_array = [$locale['gateway_053'], $locale['gateway_054'], $locale['gateway_055'], $locale['gateway_056'], $locale['gateway_057'], $locale['gateway_058'], $locale['gateway_059']];
    shuffle($honeypot_array);
    //$_SESSION["honeypot"] = $honeypot_array[3];
    session_add('honeypot', $honeypot_array[3]);

    // Try this and we see, Rot47 Encryption etc..
    add_to_footer('<script type="text/javascript">
        function decode(x) {
            let s = "";
            for (let i = 0; i < x.length; i++) {
                let j = x.charCodeAt(i);
                if ((j >= 33) && (j <= 126)) {
                    s += String.fromCharCode(33 + ((j + 14) % 94));
                } else {
                    s += String.fromCharCode(j);
                }
            }

            return s;
        }
        $("#gateway_question").append("'.$locale['gateway_060'].' " + decode("'.$a.'") + " '.$multiplier.' " + decode("'.$b.'") + " '.$locale['gateway_061'].' '.$reply_method.'");
    </script>');

    $info = [
        'showform'          => TRUE,
        'gateway_question' => '<span id="gateway_question"></span>',
        'openform'          => openform('Fusion_Gateway', 'post', 'register.php', ['class' => 'm-t-20']),
        'closeform'         => closeform(),
        'hiddeninput'       => form_hidden($honeypot_array[3], "", ""),
        'textinput'         => form_text('gateway_answer', "", "", ['error_text' => $locale['gateway_064'], 'required' => 1]),
        'button'            => form_button('gateway_submit', $locale['gateway_065'], $locale['gateway_065'], ['class' => 'btn-primary m-t-10']),
    ];
}

if (post('gateway_answer')) {
    $honeypot = session_get('honeypot');

    if (isset($_POST[$honeypot]) && !post($honeypot)) {
        $antibot = stripinput(strtolower(post('gateway_answer')));
        $antibot_session = session_get('antibot');
        if ($antibot_session == $antibot) {

            session_add('validated', 'TRUE');

            redirect(BASEDIR.'register.php');
        } else {
            $info['incorrect_answer'] = TRUE;
        }
    }
}

// print_P(session_get('antibot'), FALSE, TRUE, 0);

if (!function_exists('display_gateway')) {
    function display_gateway($info) {
        global $locale;

        echo "<div class='container'>";
        if ($info['showform'] == TRUE) {
            opentable($locale['gateway_069']);
            echo $info['openform'];
            echo $info['hiddeninput'];
            echo '<h3>'.$info['gateway_question'].'</h3>';
            echo $info['textinput'];
            echo $info['button'];
            echo $info['closeform'];
            closetable();
        } else if (!isset($_SESSION["validated"])) {
            echo '<div class="well text-center"><h3 class="m-0">'.$locale['gateway_068'].'</h3></div>';
        }

        if (isset($info['incorrect_answer']) && $info['incorrect_answer'] == TRUE) {
            opentable($locale['gateway_069']);
            echo '<div class="well text-center"><h3 class="m-0">'.$locale['gateway_066'].'</h3></div>';
            echo '<input type="button" value="'.$locale['gateway_067'].'" class="text-center btn btn-info spacer-xs" onclick="location=\''.BASEDIR.'register.php\'"/>';
            closetable();
        }
        echo "</div>";
    }
}

display_gateway($info);
