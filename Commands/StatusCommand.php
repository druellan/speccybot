<?php

namespace Longman\TelegramBot\Commands\SystemCommands;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;
/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 */
class StatusCommand extends SystemCommand
{
	/**
	 * @var string
	 */
	protected $name = 'status';
	/**
	 * @var string
	 */
	protected $description = 'Status del bot y del servidor';
	/**
	 * @var string
	 */
	protected $usage = '/status';
	/**
	 * @var string
	 */
	protected $version = '1.0';
	/**
	 * @var bool
	 */
	protected $private_only = false;
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
		$text    = 'Status del servidor: http://heliohost.grd.net.pl/monitor/';
		$data = [
			'chat_id' => $chat_id,
			'text'    => $text,
		];
		return Request::sendMessage($data);
	}
}