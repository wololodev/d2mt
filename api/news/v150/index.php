<?php
	date_default_timezone_set('CET');
	require_once('php/simple_html_dom.php');

	$newsList = file_get_html('http://www.joindota.com/en/start');
	$newsArray = array();

	foreach($newsList->find('.news_item') as $aNews) {
		$comments = $aNews->find(".right", 0)->plaintext + 0;
		$title = trim($aNews->find('.news_title_new', 0)->plaintext);
		$link = $aNews->find('a', 0)->href;
		$timeago = explode(' ', trim($aNews->find('.maketip', 0)->plaintext));
		$timeago = $timeago[0].substr($timeago[1], 0, 1)." ago";
		$date = $aNews->find('.maketip', 0)->title;
		$timeStamp = strtotime($date);
		$titleIfNameis2Long = '';
		if (strlen($title) > 40) {
			$titleIfNameis2Long = str_replace(array("'", '"'), "", $title);
			$title = substr($title, 0, 40)."..";
		}
		if ($comments >= 50)
			$title = "<b>{$title}</b>";
		$newsArray["jd"][] = "<tr class='d2mtrow news_jd' href='{$link}' title='{$comments} comments' rel='tooltip'><td alt='{$timeStamp}' class='push-tt news_date muted'>{$timeago}</td><td title='{$titleIfNameis2Long}'>{$title}</td></tr>";
	}

	//$html = file_get_contents('http://www.gosugamers.net/dota2/news/archive');
	//$newsList->load($html);
	$newsList = file_get_html('http://www.gosugamers.net/dota2/news/archive');
	$i = 0;
	// fix time bug next time
	foreach($newsList->find('.content tr') as $aNews) {
		print $i."<br />";
		if ($i < 18) {
			$i++;
			if ($i == 1)
				continue;
			$comments = substr(trim($aNews->find('.numbers', 0)->plaintext), 0, 3) + 0;
			$title = trim($aNews->find('a', 0)->plaintext);
			$link = "http://www.gosugamers.net".trim($aNews->find('a', 0)->href);
			// $timeago = explode(' ', trim($aNews->find('td', 1)->plaintext));
			// $timeago = $timeago[0].substr($timeago[1], 0, 1)." ago";
			$date = $aNews->find('td', 1)->plaintext;
			$timeStamp = strtotime($date);
			$timeago = _ago($timeStamp);
			$titleIfNameis2Long = '';
			if (strlen($title) > 40) {
				$titleIfNameis2Long = str_replace(array("'", '"'), "", $title);
				$title = substr($title, 0, 40)."..";
			}
			if ($comments >= 50)
				$title = "<b>{$title}</b>";
			$newsArray["gg"][] = "<tr class='d2mtrow news_gg' href='{$link}' title='{$comments} comments' rel='tooltip'><td alt='{$timeStamp}' class='push-tt news_date muted'>{$timeago}</td><td title='{$titleIfNameis2Long}'>{$title}</td></tr>";
		} else {
			break;
		}
	}
	$str = json_encode($newsArray);
	$filestr    = "api.json";
	$fp=@fopen($filestr, 'w');
	fwrite($fp, $str);
	fwrite($fp, ""); 
	fclose($fp);
	echo $str;

function _ago($tm,$rcs = 0) {
    $cur_tm = time(); 
    $dif = $cur_tm-$tm;
    $pds = array('s','m','h','d','w','m','y','d');
    $lngh = array(1,60,3600,86400,604800,2630880,31570560,315705600);

    for($v = sizeof($lngh)-1; ($v >= 0)&&(($no = $dif/$lngh[$v])<=1); $v--); if($v < 0) $v = 0; $_tm = $cur_tm-($dif%$lngh[$v]);
        $no = floor($no);
        $x = sprintf("%d%s ",$no,$pds[$v]);
        if(($rcs == 1)&&($v >= 1)&&(($cur_tm-$_tm) > 0))
            $x .= time_ago($_tm);
        return $x;
 }
?>