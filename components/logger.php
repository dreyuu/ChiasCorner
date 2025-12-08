<?php
function logError($message, $type = "ERROR")
{
    $logDir      = __DIR__ . '/../logs/';
    $archiveDir  = $logDir . 'archive/';
    $logFile     = $logDir . 'error.log';

    // Ensure log directory exists
    if (!is_dir($logDir)) mkdir($logDir, 0777, true);
    if (!is_dir($archiveDir)) mkdir($archiveDir, 0777, true);

    // Rotate if file too large (2MB)
    if (file_exists($logFile) && filesize($logFile) > 2 * 1024 * 1024) {
        $timestamp = date("Y-m-d_H-i-s");
        $archiveFile = $archiveDir . "error-$timestamp.log";

        // Move current log → archive
        rename($logFile, $archiveFile);

        // Compress to ZIP if available
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open("$archiveFile.zip", ZipArchive::CREATE) === TRUE) {
                $zip->addFile($archiveFile, basename($archiveFile));
                $zip->close();
                unlink($archiveFile); // delete uncompressed log
            }
        }
    }

    // Delete old zipped logs (older than 30 days)
    foreach (glob($archiveDir . '*.zip') as $f) {
        if (filemtime($f) < time() - (30 * 24 * 60 * 60)) {
            unlink($f);
        }
    }

    // Ensure main log file exists
    if (!file_exists($logFile)) {
        file_put_contents($logFile, "===== Log File Created (" . date("Y-m-d H:i:s") . ") =====\n");
    }

    // Get client IP
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

    // Get file & line where logError() was called
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    $originFile = $trace[0]['file'] ?? 'unknown';
    $originLine = $trace[0]['line'] ?? 'unknown';

    // Format log entry
    $entry = sprintf(
        "[%s] [%s] [%s] [%s:%s] %s%s",
        date("Y-m-d H:i:s"),
        strtoupper($type),
        $ip,
        $originFile,
        $originLine,
        $message,
        PHP_EOL
    );

    // Write entry to log
    file_put_contents($logFile, $entry, FILE_APPEND);
}
