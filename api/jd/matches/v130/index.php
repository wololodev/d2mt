<?php
	//error_reporting(0);
	require_once('php/simple_html_dom.php');
	date_default_timezone_set("America/Montreal");
	$url = "http://www.joindota.com/en/matches/&c1=&c2=&c3=&archiv_page=";
	$page = 1;
	$html = file_get_contents($url.$page);
	$matchList = new simple_html_dom();
	$matchList->load($html);
	$titleList = new simple_html_dom();
	$output = array();
	$finishedList = array();
	$i = 0;
	foreach($matchList->find('div[id=matchticker] .item') as $aGame) {
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
	while ($i < 10) {
		foreach($matchList->find('.pad .item') as $aGame) {
			if ($i < 10) {
				
				$winner = str_replace(' ', '', $aGame->find('.sub', 2)->plaintext);
				if (strlen($winner) != 3)
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
				$titleExt = file_get_contents($linkID);
				$titleList->load($titleExt);
				$titleStr = $titleList->find('.match_head .left', 0)->plaintext;
				$date = $titleList->find('.match_head .right', 0)->plaintext;
				
				$timeStamp = strtotime($date);
				//$whenAdv = date('j/m/Y G:i T', $timeStamp);
				$finishedList["eventDone"][] =  "<tr class='d2mtrow eventDone' href='{$linkID}' title='{$titleStr}' rel='tooltip' id='{$id}'><td class='jd_date push-tt series' alt='{$timeStamp}'>{$winner}</td><td><img title='{$img1tit}' src='{$img1}' width='14px' height='9px'> <span>{$team1}</span></td><td class='winResult' data-winner='{$vs}'>{$vs}</td><td><img title='{$img2tit}' src='{$img2}' width='14px' height='9px'> <span>{$team2}</span></td></tr>";
				/**/
				print $i;
				$i++;
			} else {
				break 2;
			}
		}
		$page++;
		$html = file_get_contents($url.$page);
		print $url.$page."<br />";
		$matchList->load($html);
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