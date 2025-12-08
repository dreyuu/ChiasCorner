<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// Load env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// DB & B2 settings
$dbHost = $_ENV['DB_HOST'];
$dbName = $_ENV['DB_NAME'];
$dbUser = $_ENV['DB_USER'];
$dbPass = $_ENV['DB_PASS'];

$b2KeyId = $_ENV['B2_KEY_ID'];
$b2AppKey = $_ENV['B2_APP_KEY'];
$b2Bucket = $_ENV['B2_BUCKET'];
$b2Endpoint = $_ENV['B2_ENDPOINT'];

// Determine month folder
$monthFolder = date('F'); // e.g., January

// Backup filenames
$date = date('Y-m-d_H-i-s');
$tempSql = tempnam(sys_get_temp_dir(), 'backup_') . '.sql';
$tempZip = tempnam(sys_get_temp_dir(), 'backup_') . '.zip';

// === 1️⃣ Export database using PDO ===
try {
  $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
  $sqlDump = "";

  foreach ($pdo->query("SHOW TABLES") as $row) {
    $table = $row[0];
    $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
    $sqlDump .= $create['Create Table'] . ";\n";

    $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
      $vals = array_map(fn($v) => isset($v) ? "'" . addslashes($v) . "'" : "NULL", array_values($r));
      $sqlDump .= "INSERT INTO `$table` VALUES(" . implode(",", $vals) . ");\n";
    }
  }

  file_put_contents($tempSql, $sqlDump);
} catch (PDOException $e) {
  die("❌ Database export failed: " . $e->getMessage());
}

// === 2️⃣ Compress SQL file ===
$zip = new ZipArchive();
if ($zip->open($tempZip, ZipArchive::CREATE) === TRUE) {
  $zip->addFile($tempSql, basename($tempSql));
  $zip->close();
  unlink($tempSql); // remove .sql immediately
} else {
  die("❌ Failed to create ZIP file");
}

// === 3️⃣ Upload to Backblaze B2 ===
$s3 = new S3Client([
  'version' => 'latest',
  'region'  => 'us-west-002',
  'endpoint' => $b2Endpoint,
  'credentials' => [
    'key'    => $b2KeyId,
    'secret' => $b2AppKey,
  ],
]);

try {
  $result = $s3->putObject([
    'Bucket' => $b2Bucket,
    'Key'    => $monthFolder . '/' . $dbName . '_backup_' . $date . '.zip',
    'SourceFile' => $tempZip,
  ]);
  echo "✅ Backup uploaded to Backblaze<br>";
  unlink($tempZip); // remove zip immediately
} catch (AwsException $e) {
  unlink($tempZip);
  die("❌ Upload failed: " . $e->getMessage());
}


// === 5️⃣ Delete old months on Backblaze (keep last 6 months) ===
try {
  $objects = $s3->listObjects([
    'Bucket' => $b2Bucket,
  ]);

  $currentMonthNumber = (int)date('m');
  $currentYear = (int)date('Y');

  foreach ($objects['Contents'] ?? [] as $obj) {
    // Extract the month folder from the key
    $key = $obj['Key'];
    $parts = explode('/', $key);
    $monthName = $parts[0] ?? '';
    $monthNumber = date('m', strtotime("1 $monthName $currentYear"));

    $monthDiff = ($currentYear - date('Y')) * 12 + ($currentMonthNumber - $monthNumber);

    if ($monthDiff > 5) {
      // delete object older than 6 months
      $s3->deleteObject([
        'Bucket' => $b2Bucket,
        'Key' => $key,
      ]);
      echo "🗑️ Deleted old backup on B2: $key<br>";
    }
  }
} catch (AwsException $e) {
  echo "❌ Cleanup failed: " . $e->getMessage();
}
