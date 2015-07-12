<?php
return array(
    'web' => array(
        'site' => 'example.com',
        'name' => 'Site Name'
    ),
    'dbpool' => array(
        'default' => array(
            'dsn' => 'mysql:dbname=?Your DB?;host=127.0.0.1;port=3306',
            'user' => '?USER?',
            'password' => '?PASSWD?'
        )
    ),
    'email' => array(
        'EMAIL_HOST'         => "localhost", // SMTP server
        'EMAIL_PORT'         => 25,                    // set the SMTP port for the GMAIL server
        'EMAIL_USERNAME'     => "noreplay@example.com", // SMTP account username
        'EMAIL_PASSWORD'     => "---",
        'EMAIL_FROM'         => "noreplay@example.com", // SMTP email
        'EMAIL_CONTACT_NAME' => 'Site Name',
        'EMAIL_AUTH'         => false
    ),
    'user' => 'none', // если сессия должна быть связана с моделью юзера, то указываем название модели 'Users' иначе пишем 'none',
    'route' => 'route.php',

    'debug' => 'Y'
);