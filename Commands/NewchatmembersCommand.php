<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;

/**
 * New chat member command
 */
class NewchatmembersCommand extends SystemCommand
{
	/**
	 * @var string
	 */
	protected $name = 'newchatmembers';

	/**
	 * @var string
	 */
	protected $description = 'New Chat Members';

	/**
	 * @var string
	 */
	protected $version = '1.3';

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
		$members = $message->getNewChatMembers();
		$welcome_messages = $this->getConfig('welcome_messages');
		$the_welcome = "";

		if ( isset($welcome_messages[$chat_id]) ) {
			$the_welcome = $welcome_messages[$chat_id];
		}

		if (!$message->botAddedInChat()) {
			$member_names = [];
			$count = 0;
			foreach ($members as $member) {
				$member_names[] = $member->tryMention();
				$count++;
			}
			$text = 'Hola ' . implode(', ', $member_names) . '.';
			if ( $count == 1 ) $text .= "👋 ¡Bienvenido al canal!\n";
			else $text .= "👋 ¡Bienvenidos al canal!\n";

			$text .= $the_welcome;
		}

		$data = [
			'chat_id' => $chat_id,
			'text'    => $text,
			'parse_mode' => 'markdown'
		];

		return Request::sendMessage($data);
	}
}
