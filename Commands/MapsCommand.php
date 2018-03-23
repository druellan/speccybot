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
	protected $description = 'Busca en maps.speccy.cz y devuelve los mapas encontrados.';
	protected $usage = '/maps <nombre>';
	protected $version = '2.2';

	/**
	 * Source of information
	 */
	private $source = "https://maps.speccy.cz/";

	/**
	 * The file we are going to use as cache
	 *
	 * @var string
	 */
	private $cache_file = "/home/dariogr/public_html/roboter/cache/mapslist.html";

	/**
	 * The url of the map list for caching
	 *
	 * @var string
	 */
	private $maplist_url = "https://maps.speccy.cz/index.php?sort=4&part=99";


	public function execute()
	{
		// Some BOT variables
		$message = $this->getMessage();
		$chat_id = $message->getChat()->getId();
		$command_str = trim($message->getText(true));

		$working_msg = Request::sendMessage([ "chat_id" => $chat_id, "text" => "Buscando mapas..." ]);

		// Commands or no commands?
		switch ($command_str) {
			case "":
				//Hint
				$response = htmlspecialchars("Usa <b>".$this->usage."</b>, donde <nombre> es el nombre del juego.");
			break;

			// case "*":
			// case "sorprendeme":
			// case "sorpréndeme":			

			// break;
			case "clearcache":

			$refresh = $this->refreshCache(true);
			if ( $refresh == true ) $response = "El cache fue actualizado.";
			else $response = "El cache NO pudo actualizarse.";

			break;
			case "source":
				$response = "> ".$this->source;
			break;
			default:

			// Short names can cause a flood on the channel, better to enforce bigger names
			if ( strlen ($command_str) < 3 ) $response = "Por favor, busca un nombre con más de 3 letras.";
			else $response = $this->searchMap($command_str);
			
		}

		// We try to detect long responses
		$disable_preview = false;
		if ( strpos($response, 'mapas') ) $disable_preview = true;

		// Return on html format
		$data = [
			'chat_id'    => $chat_id,
			'text'       => $response,
			'message_id' => $working_msg->result->message_id,
			'disable_web_page_preview' => $disable_preview,
			'parse_mode' => 'html'
		];
		return Request::editMessageText($data);
	}


	/**
	 * Fetch the page HTML and parse it
	 * @param $q searh query
	 * @return string
	 */
	private function searchMap($q) {

		$this->refreshCache();

		// Fetch the page
		$context = stream_context_create();
		$html = file_get_contents($this->cache_file, false, $context);

		// Sick of xpath, lets solve this using a regex
		preg_match_all('%<a title="(.*?)" href="(.*?)">((?:.*?)'.$q.'(?:.*?))</a>%i', $html, $output_array, PREG_SET_ORDER);

		// Nothing found
		if ( count($output_array) == 0 ) {
			return "No encontré un mapa para <b>{$q}</b>. Prueba buscando en el <a href='{$this->source}'>índice alfabético</a>.";
		}

		// One map found
		if ( count($output_array) == 1 ) {
			return "Encontré un mapa  ".$this->makeLink($output_array[0]);
		}

		// Multiple maps found
		$response = "Encontré estos mapas:\n";
		$map_count = 0; 
		foreach ($output_array as $map) {
			$response .= $this->makeLink($map)."\n";
			if ( $map_count++ >= OUTPUTLINES ) {
				$left = count($output_array) - OUTPUTLINES;
				$response .= "\n<b>{$left}</b> resultados más en <a href='{$this->source}'>maps.speccy.cz</a>.";
				break;
			}
		}

		return $response;
	}


	/**
	 * Create a link based on the map information
	 * 
	 * return string
	 */
	private function makeLink($ele) {

		preg_match("%map.php\?id=(.*?)&%i",$ele[2], $match);
		$map_id = $match[1];
		return "️🗺️  <a href='https://maps.speccy.cz/maps/{$map_id}.png'>{$ele[3]}</a>";

	}
		

	/**
	 * Refresh de cache file when needed
	 *
	 * @return bool
	 */
	private function refreshCache($force = false) {
		// This could become a ver long list, so Let´s try cache it
		if ( !(file_exists($this->cache_file) && (filemtime($this->cache_file) > (time() - 60 * 60 * 12 ))) || $force) {
			// If no cache is present, or cache is old, lets fetch the file
			try {
				$html_file = file_get_contents($this->maplist_url);
				file_put_contents($this->cache_file, $html_file, LOCK_EX);
			} catch (Exception $e) {
				return false;
			}

			return true;
		}
	}
}