<?php
	/*
	$twitch_json = json_decode(file_get_contents("https://api.twitch.tv/kraken/streams?game=Dota+2&limit=15"));
	$owned_json = json_decode(file_get_contents("http://api.own3d.tv/rest/live/list.json?gameid=605&limit=15"));

	$combinedList = array();
	
	foreach($twitch_json->streams as $aStream) {
		if ($aStream->viewers <= 1)
			continue;
		$combinedList[$aStream->viewers."_".$aStream->channel->name][] = $aStream->viewers;
		$combinedList[$aStream->viewers."_".$aStream->channel->name][] = $aStream->channel->display_name;
		$combinedList[$aStream->viewers."_".$aStream->channel->name][] = $aStream->channel->status;
		$combinedList[$aStream->viewers."_".$aStream->channel->name][] = $aStream->channel->url;
	}
	
	foreach($owned_json as $aStream) {
		if ($aStream->live_viewers <= 1)
			continue;
		$combinedList[$aStream->live_viewers."_".$aStream->channel_name][] = $aStream->live_viewers;
		$combinedList[$aStream->live_viewers."_".$aStream->channel_name][] = $aStream->live_name;
		$combinedList[$aStream->live_viewers."_".$aStream->channel_name][] = $aStream->live_description;
		$combinedList[$aStream->live_viewers."_".$aStream->channel_name][] = $aStream->link;
	}
	
	arsort($combinedList);
	echo json_encode($combinedList);
	*/

	$youtube_json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/users/joindota/uploads?&v=2&alt=jsonc&max-results=10"));
	$ytList = array();
	
	foreach($youtube_json->data->items as $aVOD) {
		$ytList[$aVOD->id][] = $aVOD->uploaded;
		$ytList[$aVOD->id][] = $aVOD->title;
		$ytList[$aVOD->id][] = $aVOD->viewCount;
		$ytList[$aVOD->id][] = $aVOD->duration;
		$ytList[$aVOD->id][] = $aVOD->player->default;
	}
	echo json_encode($ytList);
?>