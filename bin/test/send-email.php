<?php

declare(strict_types=1);

opcache_invalidate(__FILE__, true);

if (PHP_SAPI !== 'cli') {
    return false;
}

chdir(__DIR__ . '/../../');

use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mail\Transport\SmtpOptions;
use Laminas\Mail\Message;

require 'vendor/autoload.php';

$transport = new SmtpTransport();
$options   = new SmtpOptions([
    'name'              => '',
    'host'              => '',
    'port'              => 587,
    'connection_class'  => '',
    'connection_config' => [
        'username'       => '',
        'password'       => '',
        'ssl'            => 'tls',
        'novalidatecert' => true,
    ],
]);
$transport->setOptions($options);

$message = new Message();
$message->setFrom('noreply@budapest.hu');
$message->addTo('htomy92@gmail.com');
$message->setSubject('Hello Világ!');
$message->setBody('Ez egy teszt e-mail');

$transport->send($message);

var_dump('E-mail sent');
sleep(305);
var_dump('Soon to exit...');
exit;
