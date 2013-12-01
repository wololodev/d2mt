<?php
	require_once('php/simple_html_dom.php');
	$html = file_get_contents('http://www.gosugamers.net/dota2/gosubet/results');
	$matchList = new simple_html_dom();
	$matchList->load($html);
	$titleList = new simple_html_dom();
	$upcomingLiveList = array();
	$i = 0;

	//LIVE GAMES
	foreach($matchList->find('div[id=box_latest_gosubets_started_gosubets] .last_middle') as $aGame) {
		if ($i < 10) {
			$img1 = "http://www.gosugamers.net".$aGame->find('a div img', 0)->src;
			$img1tit = $aGame->find('a div img', 0)->title;
			$team1 = trim($aGame->find('a div', 0)->plaintext);
			$img2  = "http://www.gosugamers.net".$aGame->find('a div img', 1)->src;
			$img2tit  = $aGame->find('a div img', 1)->title;
			$team2 = trim($aGame->find('a div', 2)->plaintext);
			$date = $aGame->find('div', 3)->plaintext;
			$linkID = "http://www.gosugamers.net".$aGame->find('a', 0)->href;
			
			$html = file_get_contents($linkID);
			$titleList->load($html);
			$eventName = $titleList->find('.cont_middle b a', 0)->plaintext;
			$fullDate = $titleList->find('.cont_middle span[style=cursor: help;]', 0)->title;

			$timestamp = strtotime($fullDate);
			date_default_timezone_set('CET');
			$time_cet = date("H:i T", $timestamp);
			date_default_timezone_set('EST');
			$time_est = date("H:i T", $timestamp);
			date_default_timezone_set('PST');
			$time_pst = date("H:i T", $timestamp);

			$time_alt_title = "{$time_cet} - {$time_est} - {$time_pst}";
			
			$upcomingLiveList["game{$i}"][] = $eventName;
			$upcomingLiveList["game{$i}"][] = $time_alt_title;
			$upcomingLiveList["game{$i}"][] = $img1;
			$upcomingLiveList["game{$i}"][] = $img1tit;
			$upcomingLiveList["game{$i}"][] = $team1;
			$upcomingLiveList["game{$i}"][] = "vs";
			$upcomingLiveList["game{$i}"][] = $img2;
			$upcomingLiveList["game{$i}"][] = $img2tit;
			$upcomingLiveList["game{$i}"][] = $team2;
			$upcomingLiveList["game{$i}"][] = "live";
			$upcomingLiveList["game{$i}"][] = $linkID;

			$i++;
		} else {
			break;
		}
	}

	//UPCOMING GAMES
	foreach($matchList->find('div[id=box_latest_gosubets_upcoming_matches] .last_middle') as $aGame) {
		if ($i < 10) {
			$img1 = "http://www.gosugamers.net".$aGame->find('a div img', 0)->src;
			$img1tit = $aGame->find('a div img', 0)->title;
			$team1 = trim($aGame->find('a div', 0)->plaintext);
			$img2  = "http://www.gosugamers.net".$aGame->find('a div img', 1)->src;
			$img2tit  = $aGame->find('a div img', 1)->title;
			$team2 = trim($aGame->find('a div', 2)->plaintext);
			$date = $aGame->find('div', 3)->plaintext;
			$linkID = "http://www.gosugamers.net".$aGame->find('a', 0)->href;
			
			$html = file_get_contents($linkID);
			$titleList->load($html);
			$eventName = $titleList->find('.cont_middle b a', 0)->plaintext;
			$fullDate = $titleList->find('.cont_middle span[style=cursor: help;]', 0)->title;
			
			$timestamp = strtotime($fullDate);
			date_default_timezone_set('CET');
			$time_cet = date("H:i T", $timestamp);
			date_default_timezone_set('EST');
			$time_est = date("H:i T", $timestamp);
			date_default_timezone_set('PST');
			$time_pst = date("H:i T", $timestamp);

			$time_alt_title = "{$time_cet} - {$time_est} - {$time_pst}";
			
			$upcomingLiveList["game{$i}"][] = $eventName;
			$upcomingLiveList["game{$i}"][] = $time_alt_title;
			$upcomingLiveList["game{$i}"][] = $img1;
			$upcomingLiveList["game{$i}"][] = $img1tit;
			$upcomingLiveList["game{$i}"][] = $team1;
			$upcomingLiveList["game{$i}"][] = "vs";
			$upcomingLiveList["game{$i}"][] = $img2;
			$upcomingLiveList["game{$i}"][] = $img2tit;
			$upcomingLiveList["game{$i}"][] = $team2;
			$upcomingLiveList["game{$i}"][] = $date;
			$upcomingLiveList["game{$i}"][] = $linkID;

			$i++;
		} else {
			break;
		}
	}
	


	$str = trim(json_encode($upcomingLiveList));
	echo $str;
	$filestr    = "redditapi.json";
	$fp=@fopen($filestr, 'w');
	fwrite($fp, $str);
	fwrite($fp, ""); 
	fclose($fp);
?>