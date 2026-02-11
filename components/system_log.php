<?php
include_once __DIR__ . '/pusher_helper.php';

function logAction(
    PDO $connect,
    ?int $user_id,
    string $category,
    string $action_type,
    string $description,
    ?int $target_id = null
): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    // Always generate UTC time
    // $utcNow = (new DateTime('now', new DateTimeZone('UTC')))
    //     ->format('Y-m-d H:i:s');

    try {
        $stmt = $connect->prepare("
            INSERT INTO system_logs
            (
                user_id,
                action_category,
                action_type,
                target_id,
                description,
                ip_address,
                user_agent,
                created_at
            )
            VALUES
            (
                :user_id,
                :category,
                :action_type,
                :target_id,
                :description,
                :ip,
                :agent,
                :created_at
            )
        ");

        $stmt->execute([
            ':user_id'     => $user_id,
            ':category'    => strtoupper($category),
            ':action_type' => strtoupper($action_type),
            ':target_id'   => $target_id,
            ':description' => $description,
            ':ip'          => $ip,
            ':agent'       => $agent,
            ':created_at'  => localNow()
        ]);

        PusherHelper::send('logs-channel', 'log-info', ['msg' => 'logs successful']);
    } catch (\Throwable $e) {
        error_log('[SYSTEM LOG ERROR] ' . $e->getMessage());
    }
}
