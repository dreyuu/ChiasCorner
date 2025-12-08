<?php
// require __DIR__ . '/vendor/autoload.php';

// use Dotenv\Dotenv;
// // Load env
// $dotenv = Dotenv::createImmutable(__DIR__ . '/');
// $dotenv->load();

include 'inc/navbar.php';

$options = [
    'cluster' => $_ENV['PUSHER_CLUSTER'],
    'useTLS' => true
];

$pusher = new Pusher\Pusher(
    $_ENV['PUSHER_KEY'],
    $_ENV['PUSHER_SECRET'],
    $_ENV['PUSHER_APP_ID'],
    $options
);

$data = ['message' => 'Hello from PHP!'];
$pusher->trigger('print-channel', 'new-order', $data);

echo "Event triggered!";
