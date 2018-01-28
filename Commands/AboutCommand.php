<?php

namespace Longman\TelegramBot\Commands\SystemCommands;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;
/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 */
class AboutCommand extends SystemCommand
{
	/**
	 * @var string
	 */
	protected $name = 'about';
	/**
	 * @var string
	 */
	protected $description = '¿Pero que es esto?';
	/**
	 * @var string
	 */
	protected $usage = '/about';
	/**
	 * @var string
	 */
	protected $version = '1.0.0';
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
		$text    = "💡 Este BOT fue ideado por @druellan para el [Canal de Javi Ortiz Spectrumero](https://t.me/spectrumero). Puedes dejar tus sugerencias y comentarios en nuestro [Canal de Desarrollo](https://t.me/joinchat/GF2QNg-xRdd9cQFRjoDXNQ).\n".
		"🔒 El BOT es de uso exclusivo del canal, pero si quieres emplearlo en el tuyo, sólo ponte en contacto con nosotros.\n".
		"❤️ Gracias a los siguientes usuarios por sus aportaciones: *@146053915 (Javi Ortiz), @equinoxe, @Yyrkoon1982 (Juan José), @10688814 (Diego)*\n".
		"\nUsa */ayuda* para ver una lista de comandos.";
		$data = [
			'chat_id' => $chat_id,
			'text'    => $text,
			'disable_web_page_preview' => true,
			'parse_mode' => 'markdown',
		];
		return Request::sendMessage($data);
	}
}