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
class newgamesCommand extends UserCommand
{

	protected $name = 'newgames';
	protected $description = 'Busca en New Spectrum Games. Devuelve una lista de coincidencias.';
	protected $usage = '/newgames <búsqueda> o /newgames novedades o /newgames quejugar';
	protected $version = '1.4';

	/**
	 * The file we are going to use as cache
	 *
	 * @var string
	 */
	private $cache_file = "/home/dariogr/public_html/roboter/cache/newgamesdb.csv";

	/**
	 * Source of information
	 */
	private $source = "https://sites.google.com/site/speccy21/home";

	/**
	 * The url of the sheet we are going to capture
	 *
	 * @var string
	 */
	private $sheet_url = "https://docs.google.com/spreadsheets/d/1CowiqCnhgWuI5Nk62eO2snICmZWJXsov5aiOVYOidNA/export?gid=2135481993&format=csv";

	
	public function execute()
	{
		// Some BOT variables
		$message = $this->getMessage();
		$chat_id = $message->getChat()->getId();
		$command_str = trim($message->getText(true));
	
		$working_msg = Request::sendMessage([ "chat_id" => $chat_id, "text" => "Buscando en New Spectrum Games..." ]);

		// We detect if the user is asking for an author
		// if ( substr($command_str, 0, 4) == "por:" ) {
		// 	$author_name = trim(substr($command_str, 4));
		// 	$command_str = $author_name;
		// 	// 4 is the "author" column
		// 	$search_field = 4;
		// }

		// Commands or no commands?
		switch ($command_str) {
			case "":
			case "novedades":
				$response = "*Lo más reciente en *[New Spectrum Games]({$this->source})\n";
				$response .= $this->searchSpeccy();
			
				if ( empty($command_str) ) $response .= "(/ayuda newgames para más comandos)";
				
			break;
			case "*":
			case "sorprendeme":
			case "sorpréndeme":
			case "quejugar":

			$response = "*Un juego aleatorio, cortesía de* [New Spectrum Games]({$this->source}):\n".$this->randomSpeccy();

			break;
			case "clearcache":

			$refresh = $this->refreshCache(true);
			if ( $refresh == true ) $response = "El cache fue actualizado.";
			else $response = "El cache NO pudo actualizarse.";

			break;
			case "source":
				$response = "> ".$this->source;
			break;
			default:

			$response = $this->searchSpeccy($command_str);
			
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
	 * Walks the CSV looking for a match
	 * @param $q searh query
	 * @return string
	 */
	private function searchSpeccy($q = false) {

		// [0] Link
		// [1] Ingame image
		// [3] Year
		// [4] Author
		// [6] Title
		// [10] Download Link
		// [11] PlayOnline Link

		// Refresh de cache and open the handle
		$this->refreshCache();
		$csv_handle = fopen($this->cache_file, "r");

		$n_matches = 0;
		$result = array();

		if (($csv_handle) !== FALSE) {
			while (($data = fgetcsv($csv_handle, 1000, ",")) !== FALSE) {
				// first two rows are titles and control ones
				if ( $data[0] == "Link" || $data[0] == "Hidden" ) continue;
				
				if ( !$q
					|| stripos($data[6], $q) !== false
					|| stripos($data[4], $q) !== false
					|| stripos($data[3], $q) !== false
				) {
					if ( $n_matches < OUTPUTLINES ) {
						$result[] = array(
							"title" => htmlentities($data[6]),
							"author" => $data[4],
							"year" => substr(strstr($data[3], ','), 1),
							"link" => $this->encodeLink($data[0]),
							"download" => $this->encodeLink($data[10]),
							"play" => $this->encodeLink($data[11])
						);
					}
					$n_matches++;
				}
			}
			fclose($csv_handle);

			// No results?
			if ( count($result) == 0 ) return "No se encontraron juegos con *{$q}*. Prueba con /zxinfo {$q}.";

			// Let's compile everythung on a markdown list
			$response = $this->buildResponse($result, $n_matches);

		} else {
			$response = "Por alguna razón no pude extraer los datos de la fuente.";
		}

		return $response;
		
	}


		/**
	 * Walks the CSV and select a random game
	 * @return string
	 */
	private function randomSpeccy() {
		// Ok, we are going to use a non-fancy traditional method here
		// that implies we are going to walk the CSV file twice
		// I know, I know, not optimal but at this point is still fast enought

		// Refresh de cache and open the handle
		$this->refreshCache();

		// Lets first cound the csv size
		$csv_size = 0;
		$fp = fopen($this->cache_file,"r");
		if( $fp ) {
			while(!feof($fp)){
				$content = fgets($fp);
				if($content) $csv_size++;
			}
		} else {
			return "Por alguna razón no pude extraer los datos de la fuente.";
		}
		fclose($fp);

		$rand = rand(2, $csv_size);
		$csv_handle = fopen($this->cache_file, "r");

		$result = array();
		$count = 1;
		while (($data = fgetcsv($csv_handle, 1000, ",")) !== FALSE) {
		
			if ( $count++ == $rand ) {
				$result[] = array(
					"title" => htmlentities($data[6]),
					"author" => $data[4],
					"link" => $this->encodeLink($data[0]),
					"year" => substr(strstr($data[3], ','), 1),					
					"download" => $this->encodeLink($data[10]),
					"play" => $this->encodeLink($data[11])
				);
				break;
			}
		}
		fclose($csv_handle);

		// Let's compile everythung on a markdown list
		return $this->buildResponse($result);

	}

	/**
	 * Build the markdown list
	 *
	 * @param [type] $array
	 * @param boolean $more
	 * @return string
	 */
	private function buildResponse ($array, $matches = 0) {
		$response = "";
		foreach ( $array AS $item ) {
			$response .= "\xF0\x9F\x95\xB9 ";
			$response .= ($item["link"]) ? "[{$item["title"]}]({$item["link"]}) ({$item['year']})" : "{$item["title"]} ({$item['year']})";
			$response .= " por {$item [author]}";
			if ( $item['download'] ) $response .= " - [Bajar]({$item["download"]})";
			if ( $item['play'] ) $response .= " - [Jugar]({$item["play"]})";
			$response .= "\n";
		}
		
		$n_left = $matches - OUTPUTLINES;
		if ( $n_left > 0 ) {
			$response .= "*".$n_left."* más en [New Spectrum Games]({$this->source})\n";
		}

		return $response;
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
				$csv_file = file_get_contents($this->sheet_url);
				file_put_contents($this->cache_file, $csv_file, LOCK_EX);
			} catch (Exception $e) {
				return false;
			}

			return true;
		}
	}

	/**
	 * Quick encode a link to avoid markdown breakdown
	 *
	 * @param [type] $link
	 * @return string
	 */
	private function encodeLink($link) {
		return str_replace(array( "(", ")" ), array( "%28", "%29" ), $link);
	}
}