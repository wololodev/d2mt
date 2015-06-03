<?php
	error_reporting(0);
	require_once('php/simple_html_dom.php');
	date_default_timezone_set("America/Montreal");
	$url = "http://www.joindota.com/en/start";
	$matchList = file_get_html($url);
	$output = array();
	$finishedList = array();
	
	$output = array("eventSoon" => array(), "eventLive" => array(), "eventDone" => array());

	$i = 0;
	foreach($matchList->find('div[id=in_matches_list] ul[class=widget-matches-list-live] li') as $aGame) {
		if ($i < 16) {
			$aGameTeams = $aGame->children(0);

			$team1 = $aGameTeams->find('.widget-matches-opp1', 0)->plaintext;
			$team1Flag = $aGameTeams->find('img', 0);
			$team2 = $aGameTeams->find('.widget-matches-opp2', 0)->plaintext;
			$team2Flag = $aGameTeams->find('img', 1);
			$linkID = $aGame->find('a', 0)->href;
			preg_match("/\/matches\/([0-9]+)\-/",$linkID,$matches);
			$id = $matches[1];

			$aGameTime = $aGame->children(1)->title;

			$gamePage = file_get_html($linkID);
			$titleStr = $gamePage->find('.match_head .left', 0)->plaintext;

			if (strpos($aGameTime,"(upcoming)") === false) {
				$timeStamp = strtotime(str_replace(" (live)", "", $aGameTime));
				$date = "Live";
				$output["eventLive"][] = "<tr href='{$linkID}' class='d2mtrow eventLive' title='{$titleStr}' rel='tooltip' id='{$id}'><td alt='{$timeStamp}' class='push-tt jd_date'><b>{$date}</b></td><td><img title='{$team1Flag->alt}' src='{$team1Flag->src}' width='14px' height='9px'> {$team1}</td><td>v</td><td><img title='{$team2Flag->title}' src='{$team2Flag->src}' width='14px' height='9px'> {$team2}</td></tr>";
			}
			else {
				$timeStamp = strtotime(str_replace(" (upcoming)", "", $aGameTime));
				$date = $aGame->children(1)->find('.widget-matches-score-time', 0)->plaintext;
				$output["eventSoon"][] =  "<tr class='d2mtrow eventSoon' href='{$linkID}' title='{$titleStr}' rel='tooltip' id='{$id}'><td alt='{$timeStamp}' class='push-tt jd_date'>{$date}</td><td><img src='{$team1Flag->src}' title='{$team1Flag->alt}' width='14px' height='9px'> {$team1}</td><td>v</td><td><img title='{$team2Flag->title}' src='{$team2Flag->src}' width='14px' height='9px'> {$team2}</td></tr>";
			}

			$i++;
		}
		else {
			break 1;
		}
	}
	//$output["eventSoon"] = array_reverse($output["eventSoon"]);

	
	$i = 0;
	$doneMatches = $matchList->find('div[id=in_matches_list] ul[class=widget-matches-list]',1);
	foreach($doneMatches->find('li') as $aGame) {
		if ($i < 16) {
			$aGameTeams = $aGame->children(0);

			$team1 = $aGameTeams->find('.widget-matches-opp1', 0)->plaintext;
			$team1Flag = $aGameTeams->find('img', 0);
			$team2 = $aGameTeams->find('.widget-matches-opp2', 0)->plaintext;
			$team2Flag = $aGameTeams->find('img', 1);
			$linkID = $aGame->find('a', 0)->href;
			preg_match("/\/matches\/([0-9]+)\-/",$linkID,$matches);
			$id = $matches[1];

			$timeStamp = strtotime(str_replace(" (finished)", "", $aGame->children(1)->title));

			$gamePage = file_get_html($linkID);
			$titleStr = $gamePage->find('.match_head .left', 0)->plaintext;

			$winner = $aGame->find('.widget-matches-score', 0)->plaintext;
			$winnerCheck = explode(":", $winner);
			$winnerCheck[0] = intval($winnerCheck[0]);
			$winnerCheck[1] = intval($winnerCheck[1]);
			if ($winnerCheck[0] > $winnerCheck[1]) {
				$team1 = "<b>{$team1}</b>";
				$vs = ">";
			}
			else if ($winnerCheck[1] > $winnerCheck[0]) {
				$team2 = "<b>{$team2}</b>";
				$vs = "<";
			}
			else {
				$vs = "x";
			}

			$finishedList["eventDone"][] =  "<tr class='d2mtrow eventDone' href='{$linkID}' title='{$titleStr}' rel='tooltip' id='{$id}'><td class='jd_date push-tt series' alt='{$timeStamp}'>{$winner}</td><td><img title='{$team1Flag->alt}' src='{$team1Flag->src}' width='14px' height='9px'> <span>{$team1}</span></td><td class='winResult' data-winner='{$vs}'>{$vs}</td><td><img title='{$team2Flag->alt}' src='{$team2Flag->src}' width='14px' height='9px'> <span>{$team2}</span></td>
			</tr>";

			$i++;
		}
		else {
			break 1;
		}
	}

	$output = array_merge($output, $finishedList);
	$str = trim(json_encode($output));
	$filestr    = "api.json";
	$fp=@fopen($filestr, 'w');
	fwrite($fp, $str);
	fwrite($fp, ""); 
	fclose($fp);
	echo $str;
?>