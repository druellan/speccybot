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
class MeCommand extends UserCommand
{
	/**
	 * @var string
	 */
	protected $name = 'me';
	/**
	 * @var string
	 */
	protected $description = 'No digas nada, deja que el bot comente sobre tus acciones. Ej: /me señala al bot y dice "que genial"';
	/**
	 * @var string
	 */
	protected $usage = '/me';
	/**
	 * @var string
	 */
	protected $version = '1.0';

	public function execute()
	{
		$message = $this->getMessage();
		$chat_id = $message->getChat()->getId();
		$command_str = trim($message->getText(true));

		$sender = '@' . $message->getFrom()->getUsername();
		
		$data = [
			'chat_id'    => $chat_id,
			'text'       => "*{$sender}* ".$command_str,
			'parse_mode' => 'markdown'
		];
		return Request::sendMessage($data);
	}

}