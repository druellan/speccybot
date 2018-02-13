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
class MapsCommand extends UserCommand
{

	protected $name = 'maps';
	protected $description = 'Busca en maps.speccy.cz y devuelve el URL del mapa.';
	protected $usage = '/maps <nombre>';
	protected $version = '1.0';

	/**
	 * Source of information
	 */
	private $source = "https://maps.speccy.cz/";


	public function execute()
	{
		// Some BOT variables
		$message = $this->getMessage();
		$chat_id = $message->getChat()->getId();
		$command_str = trim($message->getText(true));

		$working_msg = Request::sendMessage([ "chat_id" => $chat_id, "text" => "Buscando el mapa..." ]);

		// Commands or no commands?
		switch ($command_str) {
			case "":
				//Hint
				$response = "Usa <b>".htmlspecialchars($this->usage)."</b>, donde <nombre> es el nombre del juego.";
			break;

			// case "*":
			// case "sorprendeme":
			// case "sorpréndeme":			

			// break;
			case "source":
				$response = "> ".$this->source;
			break;
			default:

			$response = $this->searchMap($command_str);
			
		}

		// Return on html format
		$data = [
			'chat_id'    => $chat_id,
			'text'       => $response,
			'message_id' => $working_msg->result->message_id,
			'parse_mode' => 'html'
		];
		return Request::editMessageText($data);
	}


	/**
	 * Fetch the page HTML and parse it
	 * @param $q searh query
	 * @return string
	 */
	private function searchMap($q, $alternate = false) {

		$q = str_replace(' ', '', $q);
		$url = $this->source."map.php?id=".urlencode($q).(($alternate) ? "1" : "");

		// Fetch the page
		$context = stream_context_create();
		$html = file_get_contents($url, false, $context);

		// Walk the DOM
		$dom = new \DOMDocument();
		@$dom->loadHTML($html);

		// Maps are always a single PNG, so, we seek for that
		$src = $dom->getElementById('obrazek')->getAttribute("src");

		// It's empty? we can try a couple of guess
		if ( $src == "maps/" && !$alternate ) {
			$response = $this->searchMap($q, true);
		}

		else if ( $src == "maps/" ) {
			$response = "No hay un mapa de <b>{$q}</b>. Recuerda que el nombre debe ser exacto y sin espacios. Si no estás seguro, busca en el <a href='{$this->source}'>índice alfabético</a>.";
		}

		else {
			$response = "Encontré este  🗺️ {$this->source}{$src}";
		}

		return $response;
	}
}