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
class WosCommand extends UserCommand
{

	protected $name = 'wos';
	protected $description = 'Busca en World of Spectrum. Devuelve una lista de coincidencias.';
	protected $usage = '/wos <búsqueda> o /wos quejugar';
	protected $version = '1.6';

	/**
	 * Source of information
	 */
	private $source = "http://www.worldofspectrum.org";


	public function execute()
	{
		// Some BOT variables
		$message = $this->getMessage();
		$chat_id = $message->getChat()->getId();
		$command_str = trim($message->getText(true));

		$working_msg = Request::sendMessage([ "chat_id" => $chat_id, "text" => "Buscando en WOS..." ]);

		// Commands or no commands?
		switch ($command_str) {
			case "":
				//Hint
				$response = "Usa *{$this->usage}*";
			break;

			case "*":
			case "sorprendeme":
			case "sorpréndeme":
			case "quejugar":			

			$response = "*Un juego aleatorio, cortesía de* [WOS]({$this->source}):\n".$this->searchOnWos(false);

			break;
			case "source":
				$response = "> ".$this->source;
			break;
			default:

			$response = $this->searchOnWos($command_str);
			
		}

		// Return on markdown format		
		$data = [
			'chat_id'    => $chat_id,
			'message_id' => $working_msg->result->message_id,
			'text'       => $response,
			'disable_web_page_preview' => true,
			'parse_mode' => 'markdown'
		];
		return Request::editMessageText($data);
	}


	/**
	 * Fetch the page HTML and parse it
	 * @param $q searh query
	 * @return string
	 */
	private function searchOnWos($q = false) {

		// Lets fool WOS with a valid user-agent
		$options = array(
			'http'=>array(
				'method'=>"GET",
				'header'=>"Accept-language: en\r\n" .
						"User-Agent: Mozilla/5.0 (Linux; U; Android 4.0.3; ko-kr; LG-L160L Build/IML74K) AppleWebkit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30\r\n"
			)
		);
		
		if ($q) {
			$url = 'http://www.worldofspectrum.org/infoseek.cgi?regexp='.urlencode($q);
		} else {
			$url = "http://www.worldofspectrum.org/randomseek.cgi";
		}

		// Fetch the code

		$context = stream_context_create($options);
		$html = file_get_contents($url, false, $context);

		// Start the XPath
		$dom = new \DOMDocument();
		@$dom->loadHTML($html);
		$xpath = new \DOMXPath($dom);
		
		// WOS is based on tables, so we parse them one by one
		$nodes = $xpath->query('//table');
		$results = array();
		$markdown = "";
		
		foreach ($nodes AS $key => $table) {
			$rows = $xpath->query('tr', $table);
		
			// And each row, one by one
			// TODO: optimize a bit so we are not walking ALL the tables for just the 6 first results
			foreach ($rows as $row ) {
				$td = $xpath->query('td', $row);
				$label = strtolower($td[0]->textContent);
				$value = $td[1]->textContent;
				
				// There are no IDs/Class, but we can use the content of the first cell as ID
				switch ($label) {
					case "full title":
						$link = $xpath->query('td/font/a/@href', $row);
						$results[$key]["link"] = $this->source.$link[0]->value."&loadpics=1";
						$title_key = $key;
					case "year of release":
					case "publisher":
						$results[$key][$label] = trim($value);
					break;
				}

				// There is no labels to detect the links sections, but we can use the ´title´ of the first IMG
				$detect_download = substr($td[0]->firstChild->attributes[2]->value, 0, 23);
				if ( strpos($td[0]->firstChild->attributes[2]->value, "Click to run on-line with") !== false ) {
					$link = $xpath->query('td/font/a/@href', $row);
					$results[$title_key]["download"] = $this->source.$link[0]->value;
				}
			}
		}

		// We return the 6 first items
		$counter = 0;
		foreach($results AS $r) {
			$markdown .= "\xF0\x9F\x95\xB9 [{$r['full title']}]({$r['link']}) - {$r['publisher']} ({$r['year of release']})";
			
			if ( $r['download'] ) {
				$link = str_replace(array( "(", ")" ), array( "%28", "%29" ), $r['download']);
				$markdown .= " - [Bajar]({$link})";
			}

			$markdown .= "\n";

			if ( $counter++ == OUTPUTLINES ) {
				$markdown .= "[Más en WOS]({$url})";
				break;
			}
		}

		if ( $counter == 0 ) $markdown = "No se encontró nada sobre *{$q}* en WOS. Prueba /newgames {$q}";

		return $markdown;
	}
}