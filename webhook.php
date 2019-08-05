<?php

// Security as recommended for the bot

// Set the ranges of valid Telegram IPs.
// https://core.telegram.org/bots/webhooks#the-short-version
$telegram_ip_ranges = [
	['lower' => '149.154.160.0', 'upper' => '149.154.175.255'], // literally 149.154.160.0/20
	['lower' => '91.108.4.0',    'upper' => '91.108.7.255'],    // literally 91.108.4.0/22
];

$ip_dec = (float) sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']));
$ok=false;

foreach ($telegram_ip_ranges as $telegram_ip_range) if (!$ok) {
	// Make sure the IP is valid.
	$lower_dec = (float) sprintf("%u", ip2long($telegram_ip_range['lower']));
	$upper_dec = (float) sprintf("%u", ip2long($telegram_ip_range['upper']));
	if ($ip_dec >= $lower_dec and $ip_dec <= $upper_dec) $ok=true;
}
if (!$ok) die("Estoooo... y tú quien eres?");


// Load composer
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/credentials/global.php';
	
// Define all paths for your custom commands in this array (leave as empty array if not used)
$commands_paths = [
	__DIR__ . '/Commands/',
];

try {
	// Create Telegram API object
	$telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

	// Add commands paths containing your custom commands
	$telegram->addCommandsPaths($commands_paths);

	// Enable admin users
	$telegram->enableAdmins($admin_users);

	// Enable MySQL
	//$telegram->enableMySql($mysql_credentials);

	// Logging (Error, Debug and Raw Updates)
	//Longman\TelegramBot\TelegramLog::initErrorLog(__DIR__ . "/{$bot_username}_error.log");
	//Longman\TelegramBot\TelegramLog::initDebugLog(__DIR__ . "/{$bot_username}_debug.log");
	//Longman\TelegramBot\TelegramLog::initUpdateLog(__DIR__ . "/{$bot_username}_update.log");

	// If you are using a custom Monolog instance for logging, use this instead of the above
	//Longman\TelegramBot\TelegramLog::initialize($your_external_monolog_instance);

	// Set custom Upload and Download paths
	//$telegram->setDownloadPath(__DIR__ . '/Download');
	//$telegram->setUploadPath(__DIR__ . '/Upload');

	// Here you can set some command specific parameters
	// e.g. Google geocode/timezone api key for /date command
	//$telegram->setCommandConfig('date', ['google_api_key' => 'your_google_api_key_here']);
	$telegram->setCommandConfig('quever', [
		'yt_api_key' => $yt_api_key,
		'channels' => $yt_channels
	]);

	$telegram->setCommandConfig('newchatmembers', [
		'welcome_messages' => $welcome_messages
	]);

	// This bot is private, check the allowed groups:

	$POST = file_get_contents("php://input");
	$POST_DATA = json_decode($POST, true);

	$user_id = null;
	$type = $POST_DATA['message']['chat']['type'];
	if (isset($POST_DATA['message']['chat']['id'])) {
		$user_id = $POST_DATA['message']['chat']['id'];
	}

	if ($type == "group" || $type == "supergroup" || $type == "channel") {
		if (!in_array($user_id, $allowed_chans)) {
			$data = [
				'chat_id' => $user_id,
				'text'    => "Acceso restringido para el ID ".$user_id
			];
			Longman\TelegramBot\Request::sendMessage($data);
			exit();
		}
	}

	// Botan.io integration
	//$telegram->enableBotan('your_botan_token');

	// Requests Limiter (tries to prevent reaching Telegram API limits)
	$telegram->enableLimiter();

	// Handle telegram webhook request
	$telegram->handle();

} catch (Longman\TelegramBot\Exception\TelegramException $e) {
	// Silence is golden!
	//echo $e;
	// Log telegram errors
	Longman\TelegramBot\TelegramLog::error($e);
} catch (Longman\TelegramBot\Exception\TelegramLogException $e) {
	// Silence is golden!
	// Uncomment this to catch log initialisation errors
	//echo $e;
}


/**
 * VERY handy debug tool
 *
 * @param [mixed] $data
 * @return string
 */
function msg_dump($data) {
	ob_start();
	print_r($data);
	return ob_get_contents();
	ob_end_clean;
}