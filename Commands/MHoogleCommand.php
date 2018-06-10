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
class MHoogleCommand extends UserCommand
{

	protected $name = 'mhoogle';
	protected $description = 'Busca contenido en las revistas MicroHobby empleando mhoogle.speccy.org. Devuelve una lista de coincidencias.';
	protected $usage = '/mhoogle <búsqueda>';
	protected $version = '1.0';

	/**
	 * Source of information
	 */
	private $source = "http://mhoogle.speccy.org/";


	public function execute()
	{
		// Some BOT variables
		$message = $this->getMessage();
		$chat_id = $message->getChat()->getId();
		$command_str = trim($message->getText(true));

		$working_msg = Request::sendMessage([ "chat_id" => $chat_id, "text" => "Buscando en MHoogle..." ]);

		// Commands or no commands?
		switch ($command_str) {
			case "":
				//Hint
				$response = "Usa <b>".htmlspecialchars($this->usage)."</b>";
			break;

			// case "*":
			// case "sorprendeme":
			// case "sorpréndeme":			

			// break;
			case "source":
				$response = "> ".$this->source;
			break;
			default:

			$response = $this->searchOnMh($command_str);
			
		}

		// Return on html format
		$data = [
			'chat_id'    => $chat_id,
			'text'       => $response,
			'message_id' => $working_msg->result->message_id,
			'disable_web_page_preview' => true,
			'parse_mode' => 'html'
		];
		return Request::editMessageText($data);
	}


	/**
	 * Fetch the page HTML and parse it
	 * @param $q searh query
	 * @return string
	 */
	private function searchOnMh($q) {

		$url = 'http://mhoogle.speccy.org/mhoogle.php?palabras='.urlencode($q).'&buscar=Buscar&tipo=1';

		// Fetch the code
		$context = stream_context_create();
		$html = file_get_contents($url, false, $context);

		// Start the XPath
		$dom = new \DOMDocument();
		@$dom->loadHTML($html);
		$xpath = new \DOMXPath($dom);
		
		// This are results
		$nodes = $xpath->query('//div[@class="resultados"]');
		// this are the counters
		$results = $xpath->query('//div[@class="num_results"]/strong');
		
		$response = "";
		$count = 1;
		
		// iterate each row
		foreach ($nodes AS $line) {
			$page_path = $xpath->query('span[@class="titulo"]', $line);
			$page_title = $page_path[0]->textContent;
		
			// let trim the page number if needed
			$trim_name = strpos($page_title, " - ");
			if ( $trim_name ) $page_title = substr($page_title, $trim_name + 3);
		
			$page_link = $page_path[0]->firstChild->attributes[0]->value;
			$magazine_path = $xpath->query('//span[@class="revista"]/a', $line);
			$magazine_title = $magazine_path[0]->textContent;
			$magazine_link = $magazine_path[0]->attributes[0]->value;
			$magazine_link = str_replace( "www.microhobby.org", "microhobby.speccy.cz/mhforever", $magazine_link );

			$response .= "📔 <a href='{$magazine_link}'>{$magazine_title}</a>  📑 <a href='{$page_link}'>{$page_title}</a>\n";

			if ( $count++ > OUTPUTLINES ) {
				// How many are left?
				$results_num = ($results[1]->textContent) - OUTPUTLINES;
				$response .= "<a href='{$url}'>{$results_num} más en MHoogle</a>";
				break;
			}
		}

		if ( empty($response) ) $response = "No se encontraron artículos con <b>{$q}</b>.";

		return $response;
	}
}