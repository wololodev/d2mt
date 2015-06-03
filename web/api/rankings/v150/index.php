<?php
	// error_reporting(0);
	require_once('php/simple_html_dom.php');

	$rankArray = array();

	$rankList = file_get_html('http://www.joindota.com/en/edb/teams');
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

	$rankList = file_get_html('http://www.gosugamers.net/dota2/rankings');
	$i = 0;
	//http://www.gosugamers.net/dota2/rankings/show/team/2930
	// last one is "data-id" on each row in the rankings table. ugh!
	$dota_suffixs = array('DotA2','Dota 2','.Dota2','.Dota 2','-Dota2','-Dota 2');
	foreach($rankList->find('.ranking-link') as $aTeam) {
		if ($i < 18) {
			$i++;
			$teamName = trim($aTeam->children(1)->children(0)->children(0)->children(1)->plaintext);
			foreach($dota_suffixs as $k)
				$teamName = str_replace($k,'',$teamName);
			$elo = str_replace(",","",trim($aTeam->children(2)->plaintext));
			$id = $aTeam->getAttribute('data-id');
			$linkID = "http://www.gosugamers.net/dota2/rankings/show/team/".$id;
			$teamPage = file_get_html($linkID);
			$rankPage = $teamPage->find('.rank-box');
			$rankPage = $rankPage[0];
			$base = $rankPage->children(0);
			$img  = str_replace("');","",str_replace("background-image: url('","http://www.gosugamers.net",$base->children(0)->getAttribute('style')));
			$link = "http://www.gosugamers.net/dota2/".$base->children(1)->children(0)->href;
			$statsBase = $rankPage->children(2)->children(0);
			$winPrc = $statsBase->children(1)->children(3)->plaintext;
			//$winPrc = substr($winPrc,1,(strpos($winPrc,"%")));
			$stats = str_replace(" ","",$statsBase->children(2)->children(3)->plaintext);

			$rankArray["gg"][] = "<tr class='d2mtrow rank_gd' href='{$link}' title='Statistics: {$stats} ({$winPrc})' rel='tooltip'><td class='muted'>{$i}.</td><td><img src='{$img}' width='14px'> {$teamName}</td><td class='textRight'>{$elo}</td></tr>";
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