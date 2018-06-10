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
class zxinfoCommand extends UserCommand
{

	protected $name = 'zxinfo';
	protected $description = 'Busca en la ZXDB (ZXInfo/Spectrum Computing). Devuelve una lista de coincidencias.';
	protected $usage = '/zxinfo <búsqueda> o /zxinfo novedades o /zxinfo sorpréndeme';
	protected $version = '1.3';

	/**
	 * Source of information
	 */
	private $source = "http://zxinfo.dk";

	/**
	 * API entry point
	 */
	private $api_url = "http://api.zxinfo.dk/api/zxinfo/v2/search?";

	/**
	 * Repository URL
	 */
	private $archive1_url = "http://www.worldofspectrum.org";
	private $archive2_url = "https://spectrumcomputing.co.uk";

	/**
	 * Frontend Search
	 */
	private $search_url = "http://zxinfo.dk/search/";

	/**
	 * Frontend details
	 */
	private $details_url = "https://spectrumcomputing.co.uk/index.php?cat=96&id=";
	//private $details_url = "http://zxinfo.dk/details/";
	


	public function execute()
	{
		// Some BOT variables
		$message = $this->getMessage();
		$chat_id = $message->getChat()->getId();
		$command_str = trim($message->getText(true));

		$working_msg = Request::sendMessage([ "chat_id" => $chat_id, "text" => "Buscando en ZXInfo..." ]);

		// Commands or no commands?
		switch ($command_str) {
			case "":
			case "novedades":
				$response = "Últimas actualizaciones en [ZXDB]({$this->source}):\n\n".$this->searchOnZXinfo(false, false);
				
				//Hint
				$response .= "\nPara otras búsquedas: *{$this->usage}*";
			break;

			case "*":
			case "sorprendeme":
			case "sorpréndeme":
			case "quejugar":
			case "random":

			$response = "*Un juego aleatorio, cortesía de* [ZXInfo]({$this->source}):\n".$this->searchOnZXinfo(false, true);

			break;
			case "source":
				$response = "> ".$this->source;
			break;
			default:

			$response = $this->searchOnZXinfo($command_str);
			
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
	 * Fetch the jSON and parse it
	 * @param $q searh query
	 * @return string
	 */
	private function searchOnZXinfo($q = false, $random = false) {

		$outputlines = OUTPUTLINES * 2;

		if ( $q ) {
			// Lets get ready with the query
			// In this case, we have something to search for
			$options = array(
				'offset'      => ($random) ? "random" : "0",
				'size'        => $outputlines,
				'query'       => urlencode($q),
				"mode"        => "full",
				"sort"        => "date_desc",
				'contenttype' => "SOFTWARE"
			);
		} else if ($random) {
			// Nothing as query, we try random stuff
			$options = array(
				'offset'       => "random",
				'availability' => "Available",
				'size'         => "1",
				"mode"         => "full",
				"sort"         => "date_desc",
				'contenttype'  => "SOFTWARE"
			);
			
		} else {
			// Nothing to random? Ok, we try just new things
			$options = array(
				'offset'       => "0",
				'size'         => $outputlines,
				"mode"         => "full",
				"sort"         => "date_desc",
				'contenttype'  => "SOFTWARE"
			);
		}

		$query = http_build_query($options);
		$fetch_url = $this->api_url . $query;
		
		// Fetch the data
		$json = file_get_contents($fetch_url);
		$data = json_decode($json, TRUE);

		// How many we have?
		$hits_total = $data['hits']['total'];

		// Nothing found, inform and exit
		if ( $hits_total == 0 ) {
			$markdown = "No se encontró nada sobre *{$q}* en ZXInfo. Prueba /wos {$q}";
			return $markdown;
		}

		foreach ( $data['hits']['hits'] as $hit ) {
			$details_url = $this->details_url.$hit['_id'];
			$source = $hit['_source'];
			$title = $source['fulltitle'];
			$link = false;

			$publisher = $source['publisher'][0]['name'];
			$publisher = ($publisher) ? " {$publisher}" : "";

			// If we don't have a publisher, just use the author
			if ( !$publisher ) {
				$publisher = $source['authors'][0]['authors'][0]['name'];
				$publisher = ($publisher) ? " {$publisher}" : "";
			}
			
			$availability = $source['availability'];
			$availability = ($availability) ? " _".$availability."_" : "";

			$year = $source['yearofrelease'];
			$year = ($year) ? " (".$year.")" : "";

			$markdown .= "\xF0\x9F\x95\xB9 [{$title}]({$details_url}) -{$publisher}{$year}{$availability}";
			
			// Lets see if the image is part of the releases
			if ( !empty($source['releases'][0]['url']) ) {
				$link = $source['releases'][0]['url'];
				$archive = $this->archive1_url;
			} else {
				// If not, perhaps there is a link on the "additionals"
				foreach ( $source['additionals'] as $additional ) {
					if ( $additional['type'] == "Tape image" ) {
						$link = $additional['url'];
						$archive = $this->archive2_url;
						break;
					}
				}
			}

			// Now lets sanitize the links
			if ( $link ) {
				$link_parts = explode("/", $link);
				array_walk($link_parts, function(&$val){
					$val = urlencode($val);
					return $val;
				});
				$link = $archive.implode("/", $link_parts);
				$markdown .= " - [Bajar]({$link})";
			}

			// Lets implement the online gamming
			// Only TAP files are allowed, so
			// foreach ( $source['additionals'] as $additional ) {
			// 	if ( $additional['format'] == "(non-TZX) TAP tape" ) {
			// 		$markdown .= " - [Jugar](".$this->archive2_url."/playonline.php?eml=1&downid=".$hit['_id'].")";
			// 	}

			// }

			$markdown .= "\n";
		}

		// How many left?
		$hits_more = $hits_total - $outputlines;
		if ( $hits_more > 0 && $q ) {
			$search_more_url = $this->search_url . urlencode($q);
			$markdown .= "\n[".$hits_more." resultados más en ZXInfo.dk]({$search_more_url})";
		}

		// If we are showing news, then just link to Spectrum Computing
		if ( !$q && !$random ) $markdown .= "\nMás novedades en [Spectrum Computing](https://spectrumcomputing.co.uk/index.php?cat=301)";

		return $markdown;
	}
}