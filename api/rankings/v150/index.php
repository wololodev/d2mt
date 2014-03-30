<?php
	// error_reporting(E_ERROR | E_PARSE);
	require_once('php/simple_html_dom.php');

	$rankList = file_get_html('http://www.joindota.com/en/edb/teams');
	$rankArray = array();

	$i = 0;
	foreach($rankList->find('.small') as $aTeam) {
		if ($i < 18) {
			$i++;

			$teamName = $aTeam->find('span', 1)->plaintext;
			$wins = $aTeam->find('.edb_stat_wins', 0)->plaintext;
			$loss = $aTeam->find('.edb_stat_loss', 0)->plaintext;
			$winPrc = round(($wins / ($wins + $loss)) * 100);
			$elo = round($aTeam->find('span', 4)->plaintext / 22);
			$link = $aTeam->href;
			$img = $aTeam->find('.edb_rank_team_logo', 0)->src;

			$rankArray["jd"][] = "<tr class='d2mtrow rank_jd' href='{$link}' title='Statistics: {$wins}-{$loss} ({$winPrc}%)' rel='tooltip'><td class='muted'>{$i}.</td><td><img src='{$img}' width='14px'> {$teamName}</td><td class='textRight'>{$elo}</td></tr>";
		} else {
			break;
		}
	}

	$html = file_get_contents('http://www.gosugamers.net/dota2/rankings');
	$rankList->load($html);
	$i = 0;
	foreach($rankList->find('.profile') as $aTeam) {
		if ($i < 18) {
			$i++;
			$stats = trim($aTeam->find('.details', 0)->plaintext);
			$teamName = trim($aTeam->find('a', 1)->plaintext);
			$elo = trim(str_replace(array('(', ')', ','), '', $aTeam->find('span', 0)->plaintext));
			$link = "http://www.gosugamers.net".$aTeam->find('a', 0)->href;
			$img = "http://www.gosugamers.net".$aTeam->find('img', 0)->src;
		
			$rankArray["gg"][] = "<tr class='d2mtrow rank_gd' href='{$link}' title='{$stats}' rel='tooltip'><td class='muted'>{$i}.</td><td><img src='{$img}' width='14px'> {$teamName}</td><td class='textRight'>{$elo}</td></tr>";
		} else {
			break;
		}
	}

	$str = json_encode($rankArray);
	$filestr    = "api.json";
	$fp=@fopen($filestr, 'w');
	fwrite($fp, $str);
	fwrite($fp, ""); 
	fclose($fp);
	echo $str;
?>