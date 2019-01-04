<?php

namespace Longman\TelegramBot\Commands\UserCommands;
use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;
/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 */
class zxinfoCommand extends UserCommand
{

	protected $name = 'zxinfo';
	protected $description = 'Busca en la ZXDB. Preferimos usar /zxdb';
	protected $usage = '';
	protected $version = '1.3';

	public function execute()
	{

		$response = "Reemplazado por el comando /zxdb.";
		$message = $this->getMessage();
		$chat_id = $message->getChat()->getId();

		return Request::sendMessage([ "chat_id" => $chat_id, "text" => $response ]);
	}

}