<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Pusher\Pusher;
use Dotenv\Dotenv;

// load env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

class PusherHelper
{

    private static function instance()
    {
        return new Pusher(
            $_ENV['PUSHER_KEY'],
            $_ENV['PUSHER_SECRET'],
            $_ENV['PUSHER_APP_ID'],
            [
                'cluster' => $_ENV['PUSHER_CLUSTER'],
                'useTLS' => true
            ]
        );
    }

    // 🔥 Send event
    public static function send($channel, $event, $data = [])
    {
        $pusher = self::instance();
        $pusher->trigger($channel, $event, $data);
    }
}
// how to use it
// PusherHelper::send("orders-channel", "order-added", ["msg" => "ok"]);
// After inserting into your MySQL database

// -- include pusher
// include "pusher_helper.php";

// PusherHelper::send("orders-channel", "order-added", [
//     "status" => "success",
//     "order_id" => $newOrderId
// ]);

// echo json_encode(["message" => "Order added"]);

// js code
// <script src="https://js.pusher.com/8.2/pusher.min.js"></script>

// var pusher = new Pusher('YOUR_APP_KEY', {
//     cluster: 'YOUR_CLUSTER'
// });

// var channel = pusher.subscribe('orders-channel');

// // ADD
// channel.bind('order-added', function(data) {
//     console.log("Order Added:", data);
//     displayOrderOnTable();
// });


// var pusher = new Pusher(`<?php echo $_ENV['PUSHER_KEY']; >`, {
//         cluster: `<?php echo $_ENV['PUSHER_CLUSTER']; >`
//     });
//     var channel = pusher.subscribe('users-channel');
//     channel.bind('user-added', function(data) {
//         fetchUser();
//     })
//     channel.bind('user-updated', function(data) {
//         fetchUser();
//     });
