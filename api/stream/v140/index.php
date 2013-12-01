<?php

	$twitch_json = json_decode(file_get_contents("https://api.twitch.tv/kraken/streams?game=Dota+2&limit=15"));
	$owned_json = json_decode(file_get_contents("http://api.own3d.tv/rest/live/list.json?gameid=605&limit=15"));

	$combinedList = array();
	$ultimateList = array();
	
	foreach($twitch_json->streams as $aStream) {
		if ($aStream->viewers <= 1)
			continue;
			
		$title = str_replace(array("'", '"'), "", $aStream->channel->status);
		$link = $aStream->channel->url;
		$name = $aStream->channel->display_name;
		$timeStamp = strtotime($aStream->channel->updated_at);
		$viewers = $aStream->viewers;
		
		$combinedList[$viewers][] ="<tr href='{$link}' class='streams twitch' title='{$title}' rel='tooltip'><td class='vod_date' alt='{$timeStamp}'><span class='iconTwitch'></span></td><td>{$name}</td><td>{$viewers}</td></tr>";
	}
	
	foreach($owned_json as $aStream) {
		if ($aStream->live_viewers <= 1)
			continue;
		
		$title = str_replace(array("'", '"'), "", $aStream->live_description);
		$link = $aStream->link;
		$name = $aStream->live_name;
		$viewers = $aStream->live_viewers;
		$timeStamp = strtotime($aStream->live_since);
		
		$combinedList[$viewers][] = "<tr href='{$link}' class='streams owned' title='{$title}' rel='tooltip'><td class='vod_date' alt='{$timeStamp}'><span class='iconOwned'></span></td><td>{$name}</td><td>{$viewers}</td></tr>";
	}
	
	krsort($combinedList);
	$combinedList = array_slice($combinedList, 0, 11);
	

	$jd_json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/users/joindota/uploads?&v=2&alt=jsonc&max-results=20"));
	$sltv_json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/users/dotasltv/uploads?&v=2&alt=jsonc&max-results=20"));
	$sheever_json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/users/sheevergaming/uploads?&v=2&alt=jsonc&max-results=20"));
	$purge_json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/users/PurgeGamers/uploads?&v=2&alt=jsonc&max-results=20"));
	$bts_json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/users/beyondthesummittv/uploads?&v=2&alt=jsonc&max-results=20"));
	$ld_json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/users/ldDOTA/uploads?&v=2&alt=jsonc&max-results=20"));
	$godz_json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/users/GoDzStudios/uploads?&v=2&alt=jsonc&max-results=20"));
	$ytList = array();
	
	foreach($jd_json->data->items as $aVOD) {
		parseVods("joinDota", 'iconJD', $aVOD);
	}
	foreach($sltv_json->data->items as $aVOD) {
		parseVods("StarLadder.TV", 'icon-star', $aVOD);
	}
	foreach($sheever_json->data->items as $aVOD) {
		parseVods("SheeverGaming", 'iconSHEEV', $aVOD);
	}
	foreach($purge_json->data->items as $aVOD) {
		parseVods("Purge Gamers", 'iconPURGE', $aVOD);
	}
	foreach($bts_json->data->items as $aVOD) {
		parseVods("Beyond The Summit", 'iconBTS', $aVOD);
	}
	foreach($ld_json->data->items as $aVOD) {
		parseVods("LDdota", 'iconLD', $aVOD);
	}
	foreach($godz_json->data->items as $aVOD) {
		parseVods("GoDz Studios", 'iconGODZ', $aVOD);
	}
	krsort($ytList);
	$ytList = array_slice($ytList, 0, 15);
	foreach($combinedList as $aStream) {
		$ultimateList["stream"][] = $aStream;
	}
	foreach($ytList as $aStream) {
		$ultimateList["vod"][] = $aStream;
	}
	
	$str = trim(json_encode($ultimateList));
	$filestr    = "api.json";
	$fp=@fopen($filestr, 'w');
	fwrite($fp, $str);
	fwrite($fp, ""); 
	fclose($fp);
	
	
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
		if (strlen($name) > 35) {
			$titleIfNameis2Long = str_replace(array("'", '"'), "", $name);
			$name = substr($name, 0, 35)."..";
		}
		$ytList[$timeStamp][] = "<tr href='{$link}' class='vod youtube' title='{$title}' rel='tooltip'><td title='{$dude}' class='vod_date' alt='{$timeStamp}'><span class='{$dudeIcon}'></span></td><td title='{$titleIfNameis2Long}'>{$name}</td><td>{$viewers}</td></tr>";
	}
?>