<?php
/**
 * Mail / SMTP configuration for PHPMailer.
 * Fill with your SMTP provider credentials in production.
 */
return [
    'enabled'    => false,            // set true once SMTP is configured
    'host'       => 'smtp.gmail.com',
    'port'       => 587,
    'encryption' => 'tls',            // tls | ssl | ''
    'username'   => '',
    'password'   => '',
    'from_email' => 'no-reply@kyrosrd.com',
    'from_name'  => 'Kyros Rent Car',
    // When mail is disabled, outgoing messages are written here for debugging.
    'log_path'   => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'mail.log',
];
