<?php
	$twitch_json = json_decode(file_get_contents("https://api.twitch.tv/kraken/streams?game=Dota+2&limit=15"));
	$dota2vd_json = json_decode(file_get_contents("http://www.dotacinema.com/feed_shoutcast_match_list_search.php?casters=&tournaments=&heroes=&teams=&rates=&descriptions=&dates=&actualPage=1&JSON=Y"));

	$combinedList = array();
	$ultimateList = array();
	$dota2vodslist = array();
	
	$i = 0;
	foreach($twitch_json->streams as $aStream) {
		if ($aStream->viewers <= 1)
			continue;

		if ($i >= 14)
			break;
			
		$title = str_replace(array("'", '"'), "", $aStream->channel->status);
		$link = $aStream->channel->url;
		$name = $aStream->channel->display_name;
		$logo = $aStream->channel->logo;
		$id = $aStream->channel->name;
		$timeStamp = strtotime($aStream->channel->updated_at);
		$viewers = $aStream->viewers;

		$now = strtotime(date("Y-m-d H:i:s"));
		$then = $now - $timeStamp;
		$hours = abs(floor($then / 3600));
		$mins = abs(floor(($then - ($hours * 3600)) / 60));
		$since = "Streaming for: {$hours}h {$mins}m";

		$combinedList[$viewers][] ="<tr href='{$link}' data-id='{$id}' class='d2mtrow streams twitch' title='{$title}<br>{$since}' rel='tooltip'><td class='stream_date' alt='{$timeStamp}'><img src='{$logo}' width='16px' height='16px'></td><td>{$name}</td><td class='textRight'>{$viewers}</td></tr>";
		$i++;
	}

	krsort($combinedList);
	
	$jd_json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/users/joindota/uploads?&v=2&alt=jsonc&max-results=15"));
	$sltv_json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/users/dotasltv/uploads?&v=2&alt=jsonc&max-results=15"));
	$sheever_json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/users/sheevergaming/uploads?&v=2&alt=jsonc&max-results=15"));
	$purge_json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/users/PurgeGamers/uploads?&v=2&alt=jsonc&max-results=15"));
	$bts_json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/users/beyondthesummittv/uploads?&v=2&alt=jsonc&max-results=15"));
	$ld_json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/users/ldDOTA/uploads?&v=2&alt=jsonc&max-results=15"));
	$godz_json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/users/GoDzStudios/uploads?&v=2&alt=jsonc&max-results=15"));
	$sagan_json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/users/sagan9ne/uploads?&v=2&alt=jsonc&max-results=15"));
	$tpl_json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/users/thepremierrleague/uploads?&v=2&alt=jsonc&max-results=15"));
	$eg_json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/users/myEGnet/uploads?&v=2&alt=jsonc&max-results=15"));
	$lumi_json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/users/luminousinverse/uploads?&v=2&alt=jsonc&max-results=15"));
	$epi_json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/users/SocialPathology/uploads?&v=2&alt=jsonc&max-results=15"));
	$neo_json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/users/neodota2/uploads?&v=2&alt=jsonc&max-results=15"));
	$d2_json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/users/dota2/uploads?&v=2&alt=jsonc&max-results=15"));
	$dc_json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/users/DotaCinema/uploads?&v=2&alt=jsonc&max-results=15"));
	$wo_json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/users/WoDotA/uploads?&v=2&alt=jsonc&max-results=15"));
	$dh_json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/users/DreamhackMedia/uploads?&v=2&alt=jsonc&max-results=15"));
	$ytList = array();
	
	foreach($jd_json->data->items as $aVOD) {
		parseVods("joinDota", 'icons iconJD', $aVOD);
	}
	foreach($sltv_json->data->items as $aVOD) {
		parseVods("StarLadder.TV", 'icon-star', $aVOD);
	}
	foreach($sheever_json->data->items as $aVOD) {
		parseVods("SheeverGaming", 'icons iconSHEEV', $aVOD);
	}
	foreach($purge_json->data->items as $aVOD) {
		parseVods("Purge Gamers", 'icons iconPURGE', $aVOD);
	}
	foreach($bts_json->data->items as $aVOD) {
		parseVods("Beyond The Summit", 'icons iconBTS', $aVOD);
	}
	foreach($ld_json->data->items as $aVOD) {
		parseVods("LDdota", 'icons iconLD', $aVOD);
	}
	foreach($godz_json->data->items as $aVOD) {
		parseVods("GoDz Studios", 'icons iconGODZ', $aVOD);
	}
	foreach($sagan_json->data->items as $aVOD) {
		parseVods("sagan9ne", 'icons iconSAGAN', $aVOD);
	}
	foreach($tpl_json->data->items as $aVOD) {
		parseVods("The Premier League", 'icons iconTPL', $aVOD);
	}
	foreach($eg_json->data->items as $aVOD) {
		parseVods("Evil Geniuses", 'icons iconEG', $aVOD);
	}
	foreach($lumi_json->data->items as $aVOD) {
		parseVods("Luminous", 'icons iconLUMI', $aVOD);
	}
	foreach($epi_json->data->items as $aVOD) {
		parseVods("EpiCommentary", 'icons iconEPI', $aVOD);
	}
	foreach($neo_json->data->items as $aVOD) {
		parseVods("NEO Dota", 'icons iconNEO', $aVOD);
	}
	foreach($d2_json->data->items as $aVOD) {
		parseVods("DotA2 Official", 'icons iconD2', $aVOD);
	}
	foreach($dc_json->data->items as $aVOD) {
		parseVods("DotaCinema", 'icons iconDC', $aVOD);
	}
	foreach($wo_json->data->items as $aVOD) {
		parseVods("WoDotA", 'icons iconWO', $aVOD);
	}
	foreach($dh_json->data->items as $aVOD) {
		parseVods("DreamHack", 'icons iconDH', $aVOD);
	}

	krsort($ytList);
	$ytList = array_slice($ytList, 0, 15);

	$arr = array('/', 'Date', '(', ')', '-0500');
	foreach($dota2vd_json as $aD2Vod) {
		$bestof = $aD2Vod->best_of;
		$bo = "BO".$bestof;
		$caster1 = $aD2Vod->caster_nickname;
		$caster2 = $aD2Vod->co_caster_nickname;

		if (!$caster1 || $caster1 == "N/A")
			$caster1 = "";
		else
			$caster1 = "Casted by {$caster1}";
		if (!$caster2 || $caster2 == "N/A")
			$caster2 = "";
		else
			$caster2 = " and {$caster2}";

		$comp = $aD2Vod->tournament_title;

		if (!$comp || $comp == "N/A")
			$comp = "". $caster1.$caster2;
		else
			$comp = $comp . " - " . $caster1.$caster2;
		$date = $aD2Vod->played_from;
		$timeStamp = strtotime($date);
		$ago = ago($timeStamp);
		$title = $comp;
		
		$id = $aD2Vod->id;
		$link = "http://www.dotacinema.com/vods/{$id}";

		$team1 = $aD2Vod->team1_short_name;
		$team2 = $aD2Vod->team2_short_name;

		$img1 = "<img width='16px' height='16px' src='{$aD2Vod->team1_big_logo_file_name}'>";
		$img2 = "<img width='16px' height='16px' src='{$aD2Vod->team2_big_logo_file_name}'>";
		$vs = $img1. "&nbsp;".$team1 . ' <span class=\'muted\'> vs </span>' . $img2."&nbsp;".$team2;

		$dota2vodslist[$id][] ="<tr href='{$link}' data-id='{$id}' class='d2mtrow dota2vods' title='{$title}' rel='tooltip'><td class='muted' alt='{$timeStamp}'>{$ago}</td><td>{$vs}</td><td class='textRight'>{$bo}</td></tr>";
	}


	foreach($combinedList as $aStream) {
		$ultimateList["stream"][] = $aStream;
	}
	foreach($ytList as $aStream) {
		$ultimateList["vod"][] = $aStream;
	}
	foreach($dota2vodslist as $aStream) {
		$ultimateList["dota2vods"][] = $aStream;
	}
	
	$str = json_encode($ultimateList);
	$filestr    = "api.json";
	$fp=@fopen($filestr, 'w');
	fwrite($fp, $str);
	fwrite($fp, ""); 
	fclose($fp);
	echo $str;
	
	function secToTime($duration) {
		return $duration <= 3600 ? gmdate("i:s", $duration) : gmdate("H:i:s", $duration);
	}
	
	function parseVods($dude, $dudeIcon, $aVOD) {
		global $ytList;
		$timeStamp = strtotime($aVOD->uploaded);
		$link = $aVOD->player->default;
		$duration = secToTime($aVOD->duration);
		$likes = $aVOD->likeCount;
		$comments = $aVOD->commentCount;
		$name = $aVOD->title;
		$viewers = $aVOD->viewCount;
		$title = "$likes likes - $comments comments [$duration]";
		
		$titleIfNameis2Long = '';
		if (strlen($name) > 38) {
			$titleIfNameis2Long = str_replace(array("'", '"'), "", $name);
			$name = substr($name, 0, 38)."..";
		}
		$ytList[$timeStamp][] = "<tr href='{$link}' class='d2mtrow vod youtube' title='{$title}' rel='tooltip'><td title='{$dude}' class='push-tt vod_date' alt='{$timeStamp}'><span class='{$dudeIcon}'></span></td><td title='{$titleIfNameis2Long}'>{$name}</td><td class='textRight'>{$viewers}</td></tr>";
	}

	function ago($time)
	{
	   $periods = array("s", "m", "h", "d", "w", "m", "y", "d");
	   $lengths = array("60","60","24","7","4.35","12","10");

	   $now = time();

	       $difference     = $now - $time;
	       $tense         = "ago";

	   for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
	       $difference /= $lengths[$j];
	   }

	   $difference = round($difference);

	   return "$difference{$periods[$j]} ago";
	}
?>