<?php
	// error_reporting(E_ALL | E_PARSE);
	require_once('php/simple_html_dom.php');
	date_default_timezone_set("America/Montreal");
	$url = "http://www.joindota.com/en/matches/&c1=&c2=&c3=&archiv_page=";
	$page = 1;
	$matchList = file_get_html($url.$page);
	$output = array();
	$finishedList = array();
	$i = 0;
	foreach(array_reverse($matchList->find('div[id=matchticker] .item')) as $aGame) {
		if ($i < 16) {
			$img1 = $aGame->find('img', 0)->src;
			$img1tit = $aGame->find('img', 0)->title;
			$team1 = $aGame->find('.sub', 0)->plaintext;
			$img2  = $aGame->find('img', 1)->src;
			$img2tit  = $aGame->find('img', 1)->title;
			$team2 = $aGame->find('.sub', 2)->plaintext;
			$date = $aGame->find('.sub', 3)->plaintext;
			$id = $aGame->find('div', 3)->id;
			
			$date = trim($date);
			//check to see if there's a ':' in the string (denonating a finished game)
			if (!strpos($date, ':')) {
				$id = str_replace('score_','',$id);
				$linkID = "http://www.joindota.com/en/matches/{$id}";
				$titleList = file_get_html($linkID);
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
			$i++;
		}
		else {
			break 1;
		}
	}
	$output["eventSoon"] = array_reverse($output["eventSoon"]);

	$i = 0;
	while ($i < 16) {
		foreach($matchList->find('.pad .item') as $aGame) {
			if ($i < 16) {
				
				$winner = trim(str_replace("(def)","",str_replace(' ', '', $aGame->find('.sub', 2)->plaintext)));
				if (strlen($winner) != 3 || $winner == "tba")
					continue;
				$img1 = $aGame->find('img', 0)->src;
				$img1tit = $aGame->find('img', 0)->title;
				$team1 = $aGame->find('.sub', 1)->plaintext;

				$img2  = $aGame->find('img', 1)->src;
				$img2tit  = $aGame->find('img', 1)->title;
				$team2 = $aGame->find('.sub', 3)->plaintext;

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
				
				$linkID = $aGame->find('a', 0)->href;
				$titleList = file_get_html($linkID);
				$titleStr = $titleList->find('.match_head .left', 0)->plaintext;
				$date = $titleList->find('.match_head .right', 0)->plaintext;
				$timeStamp = strtotime($date);
				if ($winner == "tba") 
					continue;
				//$whenAdv = date('j/m/Y G:i T', $timeStamp);
				$finishedList["eventDone"][] =  "<tr class='d2mtrow eventDone' href='{$linkID}' title='{$titleStr}' rel='tooltip' id='{$id}'><td class='jd_date push-tt series' alt='{$timeStamp}'>{$winner}</td><td><img title='{$img1tit}' src='{$img1}' width='14px' height='9px'> <span>{$team1}</span></td><td class='winResult' data-winner='{$vs}'>{$vs}</td><td><img title='{$img2tit}' src='{$img2}' width='14px' height='9px'> <span>{$team2}</span></td></tr>";
				/**/
				$i++;
			} else {
				break 2;
			}
		}
		$page++;
		$matchList = file_get_html($url.$page);
	}

	$output["eventSoon"] = array_reverse($output["eventSoon"]); 

	$output = array_merge($output, $finishedList);
	$str = trim(json_encode($output));
	$filestr    = "api.json";
	$fp=@fopen($filestr, 'w');
	fwrite($fp, $str);
	fwrite($fp, ""); 
	fclose($fp);
	echo $str;
?>