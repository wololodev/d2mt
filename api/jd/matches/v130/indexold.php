<?php
	require_once('php/simple_html_dom.php');
	date_default_timezone_set("America/Montreal");
	$html = file_get_contents('http://www.joindota.com/en/matches');
	$matchList = new simple_html_dom();
	$matchList->load($html);
	$titleList = new simple_html_dom();
	$output = array();
	$finishedList = array();
	$i = 0;
	foreach($matchList->find('div[id=matchticker] .item') as $aGame) {
		$img1 = $aGame->find('img', 1)->src;
		$img1tit = $aGame->find('img', 1)->title;
		$team1 = $aGame->find('.sub', 1)->plaintext;
		$img2  = $aGame->find('img', 2)->src;
		$img2tit  = $aGame->find('img', 2)->title;
		$team2 = $aGame->find('.sub', 3)->plaintext;
		$date = $aGame->find('.sub', 4)->plaintext;
		$id = $aGame->find('div', 4)->id;
		
		$date = trim($date);
		//check to see if there's a ':' in the string (denonating a finished game)
		if (!strpos($date, ':')) {
			$id = substr($id, -5);
			$linkID = "http://www.joindota.com/en/matches/{$id}";
			$titleExt = file_get_contents($linkID);
			$titleList->load($titleExt);
			$titleStr = $titleList->find('.match_head .left', 0)->plaintext;
			$timeStampOrig = $titleList->find('.match_head .right', 0)->plaintext;
			$timeStamp = strtotime($timeStampOrig);
			if ($date !== 'LIVE' && $date !== 'now') {
				$output["eventSoon"][] =  "<tr class='d2mtrow eventSoon' href='{$linkID}' title='{$titleStr}' rel='tooltip' id='{$id}'><td alt='{$timeStamp}' class='push-tt jd_date'>{$date}</td><td><img src='{$img1}' title='{$img1tit}' width='14px' height='9px'> {$team1}</td><td>v</td><td><img title='{$img2tit}' src='{$img2}' width='14px' height='9px'> {$team2}</td></tr>";
			} else {
				$date = "Live";
				$output["eventLive"][] = "<tr href='{$linkID}' class='d2mtrow eventLive' title='{$titleStr}' rel='tooltip' id='{$id}'><td alt='{$timeStamp}' class='push-tt jd_date'><b>{$date}</b></td><td><img title='{$img1tit}' src='{$img1}' width='14px' height='9px'> {$team1}</td><td>v</td><td><img title='{$img2tit}' src='{$img2}' width='14px' height='9px'> {$team2}</td></tr>";
			}
		}
	}
	foreach($matchList->find('.pad .item') as $aGame) {
		if ($i < 10) {
			$winner = str_replace(' ', '', $aGame->find('.sub', 2)->plaintext);
			if (strlen($winner) != 3)
				continue;
			$img1 = $aGame->find('img', 0)->src;
			$img1tit = $aGame->find('img', 0)->title;
			$team1tit = $aGame->find('.sub', 1)->plaintext;
			if (strlen($team1tit) > 17) {
				$team1 = preg_replace('/\b(\w)\w*\W*/', '\1', $team1tit);
			} else {
				$team1 = $team1tit;
				$team1tit = "";
			}
			$img2  = $aGame->find('img', 1)->src;
			$img2tit  = $aGame->find('img', 1)->title;
			$team2tit = $aGame->find('.sub', 3)->plaintext;
			if (strlen($team2tit) > 17) {
				$team2 = preg_replace('/\b(\w)\w*\W*/', '\1', $team2tit);
			} else {
				$team2 = $team2tit;
				$team2tit = "";
			}
			$id = substr($aGame->href, 35);
			$winnerCheck = explode(':', $winner);
			if ((int)$winnerCheck[0] > (int)$winnerCheck[1]) {
				$team1 = "<b>{$team1}</b>";
				$vs = '>';
			} else if ((int)$winnerCheck[0] < (int)$winnerCheck[1]){
				$team2 = "<b>{$team2}</b>";
				$vs = '<';
			} else {
				$vs = 'x';
			}
			
			$linkID = "http://www.joindota.com/en/matches/{$id}";
			$titleExt = file_get_contents($linkID);
			$titleList->load($titleExt);
			$titleStr = $titleList->find('.match_head .left', 0)->plaintext;
			$date = $titleList->find('.match_head .right', 0)->plaintext;
			
			$timeStamp = strtotime($date);
			//$whenAdv = date('j/m/Y G:i T', $timeStamp);
			$finishedList["eventDone"][] =  "<tr class='d2mtrow eventDone' href='{$linkID}' title='{$titleStr}' rel='tooltip' id='{$id}'><td class='jd_date push-tt series' alt='{$timeStamp}'>{$winner}</td><td><img title='{$img1tit}' src='{$img1}' width='14px' height='9px'> <span title='{$team1tit}'>{$team1}</span></td><td class='winResult' data-winner='{$vs}'>{$vs}</td><td><img title='{$img2tit}' src='{$img2}' width='14px' height='9px'> <span title='{$team2tit}'>{$team2}</span></td></tr>";
			$i++;
		} else {
			break;
		}
	}

	$output["eventSoon"] = array_reverse($output["eventSoon"]); 

	$output = array_merge($output, $finishedList);
	$str = trim(json_encode($output));
	$filestr    = "api.json";
	$fp=@fopen($filestr, 'w');
	fwrite($fp, $str);
	fwrite($fp, ""); 
	fclose($fp);
?>