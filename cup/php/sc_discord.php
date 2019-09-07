<?php

try {

    $_language->readModule('discord', true);

    if (isset($_GET['code'])) {

        $discord_token = $_GET['code'];

        $discordCredentials = getDiscordCredentials();

        $token = discordApiRequest(
            'https://discordapp.com/api/oauth2/token',
            array(
                "grant_type" => "authorization_code",
                'client_id' => $discordCredentials['id'],
                'client_secret' => $discordCredentials['secret'],
                'redirect_uri' => $hp_url,
                'code' => $discord_token
            )
        );

        if (!isset($token->access_token)) {
            throw new \Exception($_language->module['invalid_access_token']);
        }

        $_SESSION['access_token'] = $token->access_token;

        $discordUserUrl = 'https://discordapp.com/api/users/@me';
        $user = discordApiRequest($discordUserUrl);

        $user_name = $user->username;
        $user_email = $user->email;

        $selectQuery = cup_query(
            "SELECT
                    `userID`
                FROM `" . PREFIX . "user`
                WHERE `email` = '" . $user_email . "'",
            __FILE__
        );

        if (isset($_SESSION[ 'ws_sessiontest' ])) {
            unset($_SESSION[ 'ws_sessiontest' ]);
        }

        if (mysqli_num_rows($selectQuery) == 1) {

            $get = mysqli_fetch_array($selectQuery);

            $user_id = $get[ 'userID' ];

        } else {

            $selectQuery = cup_query(
                "INSERT INTO `" . PREFIX . "user`
                    (
                        `registerdate`,
                        `lastlogin`,
                        `email`,
                        `username`,
                        `nickname`
                    )
                    VALUES
                    (
                        " . time() . ",
                        " . time() . ",
                        '" . $user_email . "',
                        '" . $user_name . "',
                        '" . $user_name . "'
                    )",
                __FILE__
            );

            $_SESSION['successArray'][] = $_language->module['new_user_confirm'];

            $user_id = mysqli_insert_id($_database);

            $selectQuery = cup_query(
                "SELECT
                        `userID`,
                        `email`,
                        `username`
                    FROM `" . PREFIX . "user`
                    WHERE `email` = '" . $user_email . "'",
                __FILE__
            );

            $ds = mysqli_fetch_array($selectQuery);

            $newpass_random = Gen_PasswordPepper();
            $newpass_hash = Gen_PasswordHash($newpass_random, $ds['userID']);

            $selectQuery = cup_query(
                "UPDATE `" . PREFIX . "user`
                    SET `password` = '',
                        `password_hash` = '" . $newpass_hash . "'
                    WHERE `userID` = " . $user_id,
                __FILE__
            );

            $ToEmail = $ds[ 'email' ];
            $vars = array('%pagetitle%', '%username%', '%new_password%', '%homepage_url%');
            $repl = array($hp_title, $ds[ 'username' ], utf8_encode($newpass_random), $hp_url);
            $header = str_replace($vars, $repl, $_language->module[ 'email_subject' ]);
            $Message = str_replace($vars, $repl, $_language->module[ 'email_text' ]);

            $sendmail = \webspell\Email::sendEmail(
                $admin_email,
                'Set Password',
                $ToEmail,
                $header,
                $Message
            );

        }

        if (!isset($user_id) || !validate_int($user_id, true)) {
            throw new \Exception();
        }

        //cookie
        \webspell\LoginCookie::set('ws_auth', $user_id, $sessionduration * 60 * 60);

        $_SESSION['successArray'][] = $_language->module['discord_login_ok'];

        header('Location: index.php');

    } else {

        $data_array = array();
        $data_array['$discord_url'] = getDiscordAuthUrl();
        $sc_discord = $GLOBALS["_template_cup"]->replaceTemplate("sc_discord", $data_array);

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
