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
class BiteLipCommand extends UserCommand
{
	/**
	 * @var string
	 */
	protected $name = 'bitelip';
	/**
	 * @var string
	 */
	protected $description = 'Conviértete en HULK(tm).';
	/**
	 * @var string
	 */
	protected $usage = '/bitelip';
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
			'text'       => "*{$sender}* se convierte en *HULK!*(tm)",
			'parse_mode' => 'markdown'
		];
		return Request::sendMessage($data);
	}

}