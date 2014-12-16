<?php
	error_reporting(0);
	date_default_timezone_set("CET");

	$matchList = file_get_contents('http://dailydota2.com/match-api');
	$data = json_decode($matchList, true); 

	foreach ($data['matches'] as $match) {
		$img1 = "http://dailydota2.com".$match['team1']['logo_url'];
		$img2 = "http://dailydota2.com".$match['team2']['logo_url'];
			
		$linkID = $match['link'];

		$team1 = $match['team1']['team_name'];
		$team2 =  $match['team2']['team_name'];

		$bestof = $match['series_type'];

        $eventName = $match['league']['name']." [BO{$bestof}]";

		$timeStamp = $match['starttime_unix'];

		if ($match['status'] == 1) {
			$date = "Live";
			$gameArray["eventLive"][] = "<tr class='d2mtrow eventLive' href='{$linkID}' title='{$eventName}' rel='tooltip'><td alt='{$timeStamp}' class='push-tt gg_date'><b>{$date}</b></td><td><img src='{$img1}' width='14px' height='9px'> {$team1}</td><td>v</td><td><img src='{$img2}' width='14px' height='9px'> {$team2}</td></tr>";
	    }
	    if ($match['status'] == 0) {
	    	$date = nice_time($match['timediff']);
       		$gameArray["eventSoon"][] =  "<tr class='d2mtrow eventSoon' href='{$linkID}' title='{$eventName}' rel='tooltip'><td alt='{$timeStamp}' class='push-tt gg_date'>{$date}</td><td><img src='{$img1}' width='14px' height='9px'> {$team1}</td><td>v</td><td><img src='{$img2}' width='14px' height='9px'> {$team2}</td></tr>";
        }
        if ($i == 13) break;
        $i++;
	}
	
	$gameArray["eventDone"][] = "<tr class='d2mtrow eventDone' href='#' title='title' rel='tooltip'><td alt='timestamp' class='push-tt gg_date series'>Will be available soon(tm).</td></tr>";
    

	$str = trim(json_encode($gameArray));
	$filestr = "api.json";
	$fp=@fopen($filestr, 'w');
	fwrite($fp, $str);
	fwrite($fp, ""); 
	fclose($fp);
	echo $str;

	function nice_time($t) {
		$h = floor($t/3600);
		if ($h < 10) { $h = '0'.$h; }
		$m = floor($t/60)%60;
		if ($m < 10) { $m = '0'.$m; }
		$s = floor($t%60);
		if ($s < 10) { $s = '0'.$s; }

		if ($t > 3600) {
			return $h.'h '.$m.'m';
		} else {
			return $m.'m '.$s.'s';
		}
	}

?>
