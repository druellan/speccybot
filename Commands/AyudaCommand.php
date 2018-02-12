<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Longman\TelegramBot\Commands\UserCommands;
use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;
/**
 * User "/help" command
 *
 * Command that lists all available commands and displays them in User and Admin sections.
 */
class AyudaCommand extends UserCommand
{
	/**
	 * @var string
	 */
	protected $name = 'ayuda';
	/**
	 * @var string
	 */
	protected $description = 'Muestra una lista de comandos para usar con el bot.';
	/**
	 * @var string
	 */
	protected $usage = '/ayuda or /ayuda <comando>';
	/**
	 * @var string
	 */
	protected $version = '1.1';
	/**
	 * @inheritdoc
	 */
	public function execute()
	{
		$message     = $this->getMessage();
		$chat_id     = $message->getChat()->getId();
		$user_id     = $message->getFrom()->getId();
		
		$command_str = trim($message->getText(true));
		// Admin commands shouldn't be shown in group chats
		$safe_to_show = $message->getChat()->isPrivateChat();

		$data = [
			'chat_id'    => $user_id,
			'parse_mode' => 'markdown'
		];

		list($all_commands, $user_commands, $admin_commands) = $this->getUserAdminCommands();
		// If no command parameter is passed, show the list.
		if ($command_str === '') {
			$data['text'] = '*Lista de Comandos*' . PHP_EOL . PHP_EOL;
			foreach ($user_commands as $user_command) {
				$data['text'] .= '/' . $user_command->getName() . ' - ' . $user_command->getDescription() . PHP_EOL;
			}
			if ($safe_to_show && count($admin_commands) > 0) {
				$data['text'] .= PHP_EOL . '*Comandos del Administrador*:' . PHP_EOL;
				foreach ($admin_commands as $admin_command) {
					$data['text'] .= '/' . $admin_command->getName() . ' - ' . $admin_command->getDescription() . PHP_EOL;
				}
			}

			// If the chat is not private, just notify
			if ( !$safe_to_show ) {
				$dataPub = [
					'chat_id'    => $chat_id,
					'parse_mode' => 'markdown',
					'text'       => '← la ayuda fue enviada por mensaje privado.'
				];
				Request::sendMessage($dataPub);
			}

			// We send the help via private message.
			$data['text'] .= PHP_EOL . "ℹ️ Para una ayuda más precisa, usa: /ayuda <comando>." . PHP_EOL . "💡 Puedes usar este mismo chat para emplear el bot en privado.";
			return Request::sendMessage($data);
		}
		$command_str = str_replace('/', '', $command_str);
		if (isset($all_commands[$command_str]) && ($safe_to_show || !$all_commands[$command_str]->isAdminCommand())) {
			$command      = $all_commands[$command_str];
			$data['text'] = sprintf(
				'*%s* (v%s)' . PHP_EOL .
				'%s' . PHP_EOL .
				'Usa %s',
				$command->getName(),
				$command->getVersion(),
				$command->getDescription(),
				$command->getUsage()
			);
			return Request::sendMessage($data);
		}
		$data['text'] = 'No hay ayuda disponible: no conozco el comando /' . $command_str;
		return Request::sendMessage($data);
	}
	/**
	 * Get all available User and Admin commands to display in the help list.
	 *
	 * @return Command[][]
	 */
	protected function getUserAdminCommands()
	{
		// Only get enabled Admin and User commands that are allowed to be shown.
		/** @var Command[] $commands */
		$commands = array_filter($this->telegram->getCommandsList(), function ($command) {
			/** @var Command $command */
			return !$command->isSystemCommand() && $command->showInHelp() && $command->isEnabled();
		});
		$user_commands = array_filter($commands, function ($command) {
			/** @var Command $command */
			return $command->isUserCommand();
		});
		$admin_commands = array_filter($commands, function ($command) {
			/** @var Command $command */
			return $command->isAdminCommand();
		});
		ksort($commands);
		ksort($user_commands);
		ksort($admin_commands);
		return [$commands, $user_commands, $admin_commands];
	}
}