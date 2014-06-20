<?php
	error_reporting(0);
	require_once('php/simple_html_dom.php');
	date_default_timezone_set("CET");
	$matchList = file_get_html('http://www.gosugamers.net/dota2/gosubet');
	$done = $matchList->find('.matches', 2);
	if ($done) {
		$d0 = 0;
		$d1 = 1;
		$d2 = 2;
		$live = true;
	} else {
		$d0 = 0;
		$d1 = 0;
		$d2 = 1;
		$live = false;
	}
	//started
	if ($live) {
		$started = $matchList->find('.matches', $d0);
		foreach($started->find('tr') as $aGame) {
			$img1 = strtolower(substr($aGame->find('.flag', 0)->class,-2));
			$img1 = ($img1 == "un") ? "world" : $img1;
			$img1 = "http://flags.cdn.gamesports.net/".$img1.".gif";
			$team1 =  trim($aGame->find('.opp1', 0)->plaintext);
			if (!$team1) {
				continue;
			}
			$img2 = strtolower(substr($aGame->find('.flag', 1)->class,-2));
			$img2 = ($img2 == "un") ? "world" : $img2;
			$img2 = "http://flags.cdn.gamesports.net/".$img2.".gif";
			$team2 =  trim($aGame->find('.opp2', 0)->plaintext);
			$linkID = "http://www.gosugamers.net".$aGame->find('a', 0)->href;
			
			$titleList = file_get_html($linkID);
            $bestof = $titleList->find('.match-extras .bestof', 0)->plaintext;
            $bestof = current(array_slice(explode(' ', $bestof), 2, 1));
            if(!is_numeric($bestof)) $bestof = '?';
            $eventName = $titleList->find('.box-match-page > h2 a', 0)->plaintext . " [BO{$bestof}]";
            $fullDate = $titleList->find('.match-extras .datetime', 0)->plaintext;
            $fullDate = str_replace("at", "", $fullDate);
            $fullDate = $fullDate . "Europe/Berlin";
			$timeStamp = strtotime($fullDate);
			
			$date = "Live";
			$gameArray["eventLive"][] = "<tr class='d2mtrow eventLive' href='{$linkID}' title='{$eventName}' rel='tooltip'><td alt='{$timeStamp}' class='push-tt gg_date'><b>{$date}</b></td><td><img src='{$img1}' width='14px' height='9px'> {$team1}</td><td>v</td><td><img src='{$img2}' width='14px' height='9px'> {$team2}</td></tr>";
		}
	}

	//upcoming
	$upcoming = $matchList->find('.matches', $d1);
	foreach($upcoming->find('tr') as $aGame) {
		$img1 = strtolower(substr($aGame->find('.flag', 0)->class,-2));
		$img1 = ($img1 == "un") ? "world" : $img1;
		$img1 = "http://flags.cdn.gamesports.net/".$img1.".gif";
		$team1 =  trim($aGame->find('.opp1', 0)->plaintext);
		if (!$team1) {
			continue;
		}
		$img2 = strtolower(substr($aGame->find('.flag', 1)->class,-2));
		$img2 = ($img2 == "un") ? "world" : $img2;
		$img2 = "http://flags.cdn.gamesports.net/".$img2.".gif";
		$team2 =  trim($aGame->find('.opp2', 0)->plaintext);
		$linkID = "http://www.gosugamers.net".$aGame->find('a', 0)->href;
		$date = trim($aGame->find('.live-in', 0)->plaintext);
		
		$titleList = file_get_html($linkID);
		$bestof = $titleList->find('.match-extras .bestof', 0)->plaintext;
        $bestof = current(array_slice(explode(' ', $bestof), 2, 1));
        if(!is_numeric($bestof)) $bestof = '?';
		$eventName = $titleList->find('.box-match-page > h2 a', 0)->plaintext . " [BO{$bestof}]";
		$fullDate = $titleList->find('.match-extras .datetime', 0)->plaintext;
		$fullDate = str_replace("at", "", $fullDate);
		$fullDate = $fullDate . "Europe/Berlin";
		$timeStamp = strtotime($fullDate);
		
		$gameArray["eventSoon"][] =  "<tr class='d2mtrow eventSoon' href='{$linkID}' title='{$eventName}' rel='tooltip'><td alt='{$timeStamp}' class='push-tt gg_date'>{$date}</td><td><img src='{$img1}' width='14px' height='9px'> {$team1}</td><td>v</td><td><img src='{$img2}' width='14px' height='9px'> {$team2}</td></tr>";
	}
	
	//done
	$done = $matchList->find('.matches', $d2);
	$i = 0;
	foreach($done->find('tr') as $aGame) {
		if ($i > 15)
			break;
		$img1 = strtolower(substr($aGame->find('.flag', 0)->class,-2));
		$img1 = ($img1 == "un") ? "world" : $img1;
		$img1 = "http://flags.cdn.gamesports.net/".$img1.".gif";
		$team1 =  trim($aGame->find('.opp1', 0)->plaintext);
		if (!$team1) {
			continue;
		}
		$img2 = strtolower(substr($aGame->find('.flag', 1)->class,-2));
		$img2 = ($img2 == "un") ? "world" : $img2;
		$img2 = "http://flags.cdn.gamesports.net/".$img2.".gif";
		$team2 =  trim($aGame->find('.opp2', 0)->plaintext);
		$linkID = "http://www.gosugamers.net".$aGame->find('a', 0)->href;

		$titleList = file_get_html($linkID);
		$bestof = $titleList->find('.match-extras .bestof', 0)->plaintext;
        $bestof = current(array_slice(explode(' ', $bestof), 2, 1));
        if(!is_numeric($bestof)) $bestof = '?';
		$eventName = $titleList->find('.box-match-page > h2 a', 0)->plaintext . " [BO{$bestof}]";
		$fullDate = $titleList->find('.match-extras .datetime', 0)->plaintext;
		$fullDate = str_replace("at", "", $fullDate);
		$fullDate = $fullDate . "Europe/Berlin";
		$timeStamp = strtotime($fullDate);
		$score1 = 0 + trim($titleList->find('.match-extras .hidden span', 0)->plaintext);
		$score2 = 0 + trim($titleList->find('.match-extras .hidden span', 1)->plaintext);
		$series = "{$score1}:{$score2}";
		if ($score1 == $score2)
			$winner = "x";
		else if ($score1 > $score2) {
			$team1 = '<b>'.$team1.'</b>';
			$winner = ">";
		} else {
			$team2 = '<b>'.$team2.'</b>';
			$winner = "<";
		}
		$gameArray["eventDone"][] = "<tr class='d2mtrow eventDone' href='{$linkID}' title='{$eventName}' rel='tooltip'><td alt='{$timeStamp}' class='push-tt gg_date series'>{$series}</td><td><img src='{$img1}' width='14px' height='9px'> {$team1}</td><td class='winResult' data-winner='{$winner}'>{$winner}</td><td><img src='{$img2}' width='14px' height='9px'> {$team2}</td></tr>";
		$i++;
	}

	$str = trim(json_encode($gameArray));
	$filestr    = "api.json";
	$fp=@fopen($filestr, 'w');
	fwrite($fp, $str);
	fwrite($fp, ""); 
	fclose($fp);
	echo $str;
?>
