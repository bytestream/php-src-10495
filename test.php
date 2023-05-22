<?php declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__.'/vendor/autoload.php';

$socket = new Horde_Imap_Client_Socket([
  'username' => 'kieran@supportpal.com',
  'password' => '',
  'hostspec' => 'supportpal.com',
  'port' => 993,
  'secure' => 'ssl',
  'timeout' => 30,
  'debug' => 'php://stdout',
  'debug_literal' => true,
]);

echo 'Logging in...'.PHP_EOL;
$socket->login();

echo 'Searching for unread emails...'.PHP_EOL;
$query = new Horde_Imap_Client_Search_Query;
$query->flag(Horde_Imap_Client::FLAG_SEEN, false);
$results = $socket->search('INBOX', $query);

echo 'Downloading emails...'.PHP_EOL;
$query = new Horde_Imap_Client_Fetch_Query;
$query->fullText(['peek' => false]);
$list =  $socket->fetch('INBOX', $query, ['ids' => new Horde_Imap_Client_Ids($results['match'])]);
foreach ($list as $item) {
  $file = tempnam(sys_get_temp_dir(), 'TMP_');
  echo sprintf('Writing email to \'%s\'...'.PHP_EOL, $file);
  file_put_contents($file, $item->getFullMsg(true));
}

echo 'Logging out...'.PHP_EOL;
$socket->shutdown();

