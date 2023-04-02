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
class zxdbCommand extends UserCommand
{

	protected $name = 'zxdb';
	protected $description = 'Busca cosas de ZX Spectrum y ZX81 en la ZXDB (ZXInfo/Spectrum Computing).';
	protected $usage = '/zxdb (--titulo) (--empresa) (--autor) <búsqueda> o /zxdb novedades o /zxdb sorpréndeme';
	protected $version = '1.7';

	/**
	 * Source of information
	 */
	private $source = "http://zxinfo.dk";

	/**
	 * API entry point
	 */
	private $api_url           = "https://api.zxinfo.dk/v3/search/?";
	private $api_url_publisher = "https://api.zxinfo.dk/v3/publishers/%s/games?";
	private $api_url_author    = "https://api.zxinfo.dk/v3/authors/%s/games?";
	private $api_url_random    = "https://api.zxinfo.dk/v3/games/random/3";

	/**
	 * Repository URL
	 */
	private $archive1_url = "https://archive.org/download/World_of_Spectrum_June_2017_Mirror/World%20of%20Spectrum%20June%202017%20Mirror.zip/World%20of%20Spectrum%20June%202017%20Mirror";
	private $archive2_url = "https://spectrumcomputing.co.uk";

	/**
	 * Frontend Search
	 */
	private $search_url = "https://zxinfo.dk/search/";

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
		$operator = false;

		$working_msg = Request::sendMessage([ "chat_id" => $chat_id, "text" => "Buscando en ZXDB..." ]);

		// Let's detect the modificator
		// TODO: might be nice if we can concatenate other modificators like --random
		preg_match("%—(.*?) %i", $command_str, $matches);
		if ( $matches ) {
			$command_str = str_replace ($matches[0], "", $command_str);
			$operator = $matches[1];
		}

		// Commands or no commands?
		switch ($command_str) {
			case "":
			case "novedades":
				$response = "Últimas actualizaciones en [ZXDB]({$this->source}):\n".$this->searchOnZXinfo(false, false, false);
				
				//Hint
				$response .= "\nPara otras búsquedas: *{$this->usage}*";
			break;

			case "*":
			case "sorprendeme":
			case "sorpréndeme":
			case "quejugar":
			case "random":

			$response = "*Tres juegos aleatorios, cortesía de la* [ZXDB]({$this->source}):\n".$this->searchOnZXinfo(false, $operator, true);

			break;
			case "source":
				$response = "> ".$this->source;
			break;
			default:

			$response = $this->searchOnZXinfo($command_str, $operator);
			
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
	private function searchOnZXinfo($q = false, $operator = false, $random = false) {

		$outputlines = OUTPUTLINES * 2;
		$q = urlencode($q);

		if ( $q ) {
			// Lets get ready with the query
			// In this case, we have something to search for
			$options = array(
				'offset'      => "0",
				'size'        => $outputlines,
				"mode"        => "full",
				"sort"        => "rel_desc",
				'contenttype' => "SOFTWARE"
			);

			switch($operator) {
				case "publisher":
				case "label":
				case "firma":
				case "distribuidora":
				case "empresa":

					$api_url = sprintf($this->api_url_publisher, $q);

				break;
				case "author":
				case "autor":

					$api_url = sprintf($this->api_url_author, $q);

				break;
				case "title":
				case "titulo":
				case "título":
				default:
				
					$options['query'] = $q;
					$api_url = $this->api_url;
			}

		} else if ($random) {
			// Nothing to query but random: we try random stuff
			$options = array();
			$api_url = $this->api_url_random;
		} else {
			// Nothing, no random? Ok, we try just new things
			$options = array(
				'offset'       => "0",
				'size'         => $outputlines,
				"mode"         => "full",
				"sort"         => "date_desc",
				'contenttype'  => "SOFTWARE"
			);
			$api_url = $this->api_url;
		}

		$query = http_build_query($options);
		$fetch_url = $api_url . $query;
	
		// Fetch the data
		try {
			$json = file_get_contents($fetch_url);
		} catch (\Throwable $th) {
			$markdown = "Se produjo un error al intentar traer la información. Ni idea que pasó, pero intenta de nuevo más tarde.";
			$markdown .= "[Debug] query: ".$fetch_url;
			return $markdown;
		}

		$data = json_decode($json, TRUE);

		// How many we have?
		$hits_total = $data['hits']['total']['value'];

		// Nothing found, inform and exit
		if ( $hits_total == 0 ) {
			$markdown = "No se encontró nada sobre *{$q}* en la ZXDB. Prueba con alguno de los filtros '--empresa', '--autor' o '--titulo'";
			//$markdown .= "[Debug] query: ".$fetch_url;
			return $markdown;
		}

		foreach ( $data['hits']['hits'] as $hit ) {
			$details_url = $this->details_url.$hit['_id'];
			$source = $hit['_source'];
			$title = $source['title'];
			$link = false;

			$publisher = $source['publishers'][0]['name'];
			$publisher = ($publisher) ? " {$publisher}" : "";

			// If we don't have a publisher, just use the author
			if ( !$publisher ) {
				$publisher = $source['authors'][0]['name'];
				$publisher = ($publisher) ? " {$publisher}" : "";
			}
			
			$availability = $source['availability'];
			$availability = ($availability) ? " _".$availability."_" : "";

			$year = $source['originalYearOfRelease'];
			$year = ($year) ? " (".$year.")" : "";

			$markdown .= "\xF0\x9F\x95\xB9 [{$title}]({$details_url}) -{$publisher}{$year}{$availability}";
			
			// Lets see if the image is part of the releases
			if ( !empty($source['releases'][0]['files'][0]['path']) ) {
				$link = $this->escapePath($source['releases'][0]['files'][0]['path']);
				$archive = $this->archive1_url;
			} else {
				// If not, perhaps there is a link on the "additionals"
				foreach ( $source['additionalDownloads"'] as $additional ) {
					if ( $additional['type'] == "Tape image" ) {
						$link = $this->escapePath($additional['url']);
						$archive = $this->archive2_url;
						break;
					}
				}
			}

			// Now lets sanitize the links
			// IMPORTANT, only do this if the title is available, just to avoid
			// exposing commercial titles!
			if ( $link && $source['availability'] == "Available" ) {
				if ( substr( $link, 0, 5 ) === "/pub/" )
					$link = str_replace("%2Fpub%2F", $this->archive1_url, $link);
				if ( substr( $link, 0, 6 ) === "/zxdb/" )
					$link = $this->archive2_url . $link;
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

	private function escapePath($path) {
		return implode('/', array_map('rawurlencode', explode('/', $path)));
	}
}