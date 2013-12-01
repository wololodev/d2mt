<?php

	$twitch_json = json_decode(file_get_contents("https://api.twitch.tv/kraken/streams?game=Dota+2&limit=15"));
	$owned_json = json_decode(file_get_contents("http://api.own3d.tv/rest/live/list.json?gameid=605&limit=15"));

	$combinedList = array();
	$ultimateList = array();
	
	foreach($twitch_json->streams as $aStream) {
		if ($aStream->viewers <= 1)
			continue;
			
		$title = $aStream->channel->status;
		$link = $aStream->channel->url;
		$name = $aStream->channel->display_name;
		$timeStamp = strtotime($aStream->channel->updated_at);
		$viewers = $aStream->viewers;
		
		$combinedList[$viewers][] ="<tr class='streams twitch' title='{$title}' rel='tooltip'><td class='vod_date' alt='{$timeStamp}'><span class='iconTwitch'></span></td><td><a href='{$link}' target='_blank'>{$name}</a></td><td>{$viewers}</td></tr>";
	}
	
	foreach($owned_json as $aStream) {
		if ($aStream->live_viewers <= 1)
			continue;
		
		$title = $aStream->live_description;
		$link = $aStream->link;
		$name = $aStream->live_name;
		$viewers = $aStream->live_viewers;
		$timeStamp = strtotime($aStream->live_since);
		
		$combinedList[$viewers][] = "<tr class='streams owned' title='{$title}' rel='tooltip'><td class='vod_date' alt='{$timeStamp}'><span class='iconOwned'></span></td><td><a href='{$link}' target='_blank'>{$name}</a></td><td>{$viewers}</td></tr>";
	}
	
	krsort($combinedList);
	$combinedList = array_slice($combinedList, 0, 10);
	

	$youtube_json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/users/joindota/uploads?&v=2&alt=jsonc&max-results=10"));
	$ytList = array();
	
	foreach($youtube_json->data->items as $aVOD) {
		$timeStamp = strtotime($aVOD->uploaded);
		$link = $aVOD->player->default;
		$duration = secToTime($aVOD->duration);
		$likes = $aVOD->likeCount;
		$comments = $aVOD->commentCount;
		$name = $aVOD->title." [{$duration}]";
		$viewers = $aVOD->viewCount;
		$title = "$likes likes - $comments comments";
		
		$ytList[$aVOD->id][] = "<tr class='vod youtube' title='{$title}' rel='tooltip'><td class='vod_date' alt='{$timeStamp}'><span class='iconYT'></span></td><td><a href='{$link}' target='_blank'>{$name}</a></td><td>{$viewers}</td></tr>";
	}
	
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
?>