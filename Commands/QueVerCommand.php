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
class QueVerCommand extends UserCommand
{
	protected $name = 'quever';
	protected $description = 'Muestra una recomendación para ver en YouTube.';
	protected $usage = '/quever live o /quever <canal> o /quever <búsqueda>';
	protected $version = '1.8';

	public function execute()
	{
		// Some BOT variables
		$message = $this->getMessage();
		$chat_id = $message->getChat()->getId();
		$command_str = trim($message->getText(true));

		// By default we disable de preview
		$disable_web_page_preview = true;
		
		$working_msg = Request::sendMessage([ "chat_id" => $chat_id, "text" => "Buscando en YouTube..." ]);


		// Is there a specific command?
		switch ($command_str) {
			case "endirecto":
			case "envivo":
			case "live":
				$response = $response = $this->listChanns("live");
				$disable_web_page_preview = false;
			break;

			case "sources":
			case "source":

				$response = "";
				foreach ( $this->getConfig('channels') as $source => $id ) {
					$response .= $source." | ";
				}

			break;

			default:

			//Perhaps we want an specific channel?
			if ( $command_str ) {

				$result = array();
				$preg_search = preg_replace(array("(a|á)","(e|é)","(i|í)","(o|ó)","(u|ú)"), array("(a|á)","(e|é)","(i|í)","(o|ó)","(u|ú)"), $command_str);

				// Let's iterate the sources, trying to find a match
				foreach ( $this->getConfig('channels') AS $chann => $id ) {
					$match = preg_match("/\b{$preg_search}\b/ui", $chann);
					
					if ( $match ) {
						$result[$chann] = $id;
						break;
					}
				}
				
				if ( count($result) > 0 ) {
					$response = "Lo último de <b>{$chann}</b>:\n";
					$response .= $this->listChanns(false, $result, OUTPUTLINES);
				} else {
					$response = $this->listChanns(false, false, 10, $command_str);
				}
				
			} else {
				$response = $this->listChanns();
			}
			
			
		}

		// Return on markdown format
		$data = [
			'chat_id'    => $chat_id,
			'message_id' => $working_msg->result->message_id,
			'text'       => $response,
			'disable_web_page_preview' => $disable_web_page_preview,
			'parse_mode' => 'html'
		];
		return Request::editMessageText($data);

	}

	/**
	 * Returns the most recent video on the list
	 * @return string
	 */
	// private function latest($channels) {

	// 	$videolist = $this->fetchYT($channels);

	// 	if (isset($videolist['live'])) {
	// 		$area = "live";
	// 		$pre = "🎙️ <b>En directo</b> ";

	// 	} else if (isset($videolist['upcoming'])) {
	// 		$area = "upcoming";
	// 		$pre = "⏰ En breve ";
	// 	} else {
	// 		$area = "none";
	// 		$pre = "📺 Lo último de ";
	// 	}
		
	// 	return $pre.$videolist[$area][0]['channel'].": <a href='https://www.youtube.com/watch?v=".$videolist[$area][0]['id']."'>".$videolist[$area][0]['title']."</a>";
	// }

	/**
	 * Returns the list of videos
	 * @return string
	 */
	private function listChanns($event_type = false, $channels = false, $maxResults = 1, $query = "") {
		
		if ( !$channels ) $channels = $this->getConfig('channels');
		$videolist = $this->fetchYT($channels, $maxResults, $query);

		$has_live = false;
		$count = 0;
		
		// Live events
		if ( count($videolist['live']) ) {
			foreach ( $videolist['live'] AS $item ) {
				$flag = "🎙️";
				$count++;
				$has_live = true;
				$response .= $flag." <b>{$item['channel']}</b> sobre <a href='https://www.youtube.com/watch?v={$item['id']}'>{$item['title']}</a>\n";
			}
		}

		// Scheduled events
		if ( count($videolist['upcoming']) ) {
			foreach ( $videolist['upcoming'] AS $item ) {
				$flag = "⏰";
				$count++;
				$has_live = true;
				$response .= $flag." <b>{$item['channel']}</b> sobre <a href='https://www.youtube.com/watch?v={$item['id']}'>{$item['title']}</a>\n";
			}
		}

		if ( $event_type == "live" ) {
			if ( $has_live ) return $response;
			else return "En este momento no hay directos activos o programados.";
		}

		if ( $has_live ) $response .= "\n";

		// Just videos
		foreach ( $videolist['none'] AS $item ) {
			$time = $this->humanTiming( strtotime($item['date']));
			
			//If time > a month, lets skip
			if ( strtotime($item['date']) < 2592000 ) continue;

			// Or if we have too many entries, skip the rest
			if ( $count++ > OUTPUTLINES ) break;

			$flag = "📺";
			$response .= $flag." <a href='https://www.youtube.com/watch?v={$item['id']}'>{$item['title']}</a> por {$item['channel']}  ◷ {$time[1]}\n";
		}

		$response .= "\n🎙️ en directo  ⏰ programado  📺 diferido";

		return $response;
	}


	/**
	 * Go fetch the latest video of the list of channels
	 * Sort'em based on date
	 * @var array $channels
	 * @var int $maxResults
	 * @return array
	 */
	private function fetchYT($channels, $maxResults = 1, $search = "") {
		//Get videos from channels by YouTube Data API
		$API_key = $this->getConfig('yt_api_key');
		// Perhaps there is a search query?
		$searchq = "";
		if ( $search ) $searchq = "&q=".$search;

		$videolist = array();
		
		// Extract only useful information
		foreach ($channels as $name => $chan) {
			$video = json_decode(file_get_contents('https://www.googleapis.com/youtube/v3/search?order=date&part=snippet&channelId='.$chan.$searchq.'&fields=items(id,snippet)&maxResults='.$maxResults.'&key='.$API_key));

			foreach ( $video->items AS $item ) {
				$category = $item->snippet->liveBroadcastContent;
				$videolist[$category][] = array(
					"id" => $item->id->videoId,
					"channel" => $item->snippet->channelTitle,
					"title" => $item->snippet->title,
					"date" => $item->snippet->publishedAt
				);
			}
		};

		// Sorting
		usort($videolist['none'], function ($a, $b) {
			return strcmp ($b['date'], $a['date']);	
		});

		return $videolist;

	}

	// I'm not copy/paste from https://stackoverflow.com/questions/2915864/php-how-to-find-the-time-elapsed-since-a-date-time I'm not!
	private function humanTiming ($time) {
		$time = time() - $time; // to get the time since that moment
		$time = ($time<1)? 1 : $time;
		$tokens = array (
			31536000 => 'año',
			2592000 => 'mes',
			604800 => 'semana',
			86400 => 'día',
			3600 => 'hora',
			60 => 'minuto',
			1 => 'segundos'
		);
	
		foreach ($tokens as $unit => $text) {
			if ($time < $unit) continue;
			$numberOfUnits = floor($time / $unit);
			if ( $text == "mes" ) $prural = (($numberOfUnits>1)?'ses':'');
			else $prural = (($numberOfUnits>1)?'s':'');
			return array ($time, $numberOfUnits.' '.$text.$prural);
		}
	
	}

}