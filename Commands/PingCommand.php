<?php

namespace Longman\TelegramBot\Commands\SystemCommands;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;
/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 */
class PingCommand extends SystemCommand
{
	/**
	 * @var string
	 */
	protected $name = 'ping';
	/**
	 * @var string
	 */
	protected $description = 'PingPong';
	/**
	 * @var string
	 */
	protected $usage = '/ping';
	/**
	 * @var string
	 */
	protected $version = '1.1.0';
	/**
	 * @var bool
	 */
	protected $private_only = true;
	/**
	 * Command execute method
	 *
	 * @return \Longman\TelegramBot\Entities\ServerResponse
	 * @throws \Longman\TelegramBot\Exception\TelegramException
	 */
	public function execute()
	{
		$message = $this->getMessage();
		$chat_id = $message->getChat()->getId();
		$text    = '¡Pong!';
		$data = [
			'chat_id' => $chat_id,
			'text'    => $text,
		];
		return Request::sendMessage($data);
	}
}