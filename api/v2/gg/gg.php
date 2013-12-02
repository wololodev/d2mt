<?php
  set_time_limit (0);
  error_reporting(0);

  require_once('../simple_html_dom.php');

  date_default_timezone_set("CET");

  $html = file_get_contents('http://www.gosugamers.net/dota2/gosubet');
  $matchList = new simple_html_dom();
  $matchList->load($html);
  $titleList = new simple_html_dom();
  $gameArray = array();
  $output = array();
  $done = $matchList->find('.matches', 2);
  if ($done) {
    $d0 = 0;
    $d1 = 1;
    $d2 = 2;
    $live = true;
  } else {
    $d0 = 0;
    $d1 = 0;
    $d2 = 1;
    $live = false;
  }
  //started
  if ($live) {
    $started = $matchList->find('.matches', $d0);
    $i = 0;
    foreach($started->find('tr') as $aGame) {
      $img1 = "http://www.gosugamers.net".$aGame->find('img', 0)->src;
      $team1 =  trim($aGame->find('.opp1', 0)->plaintext);
      if (!$team1) {
        continue;
      }
      $img2  = "http://www.gosugamers.net".$aGame->find('img', 1)->src;
      $team2 =  trim($aGame->find('.opp2', 0)->plaintext);
      $linkID = "http://www.gosugamers.net".$aGame->find('a', 0)->href;

      $html = file_get_contents($linkID);
      $titleList->load($html);
      $bestof = $titleList->find('.match-extras .bestof', 0)->plaintext;
      $bestof = explode(' ', $bestof)[2];
      if(!is_numeric($bestof)) $bestof = '?';
      $eventName = $titleList->find('.box-match-page > h2 a', 0)->plaintext . " [BO{$bestof}]";
      $fullDate = $titleList->find('.match-extras .datetime', 0)->plaintext;
      $fullDate = str_replace("at", "", $fullDate);
      $fullDate = $fullDate . "Europe/Berlin";
      $timeStamp = strtotime($fullDate);

      $date = "Live";

      $game['img1'] = $img1;
      $game['team1'] = $team1;
      $game['img2'] = $img2;
      $game['team2'] = $team2;
      $game['linkID'] = $linkID;
      $game['bestof'] = $bestof;
      $game['eventName'] = $eventName;
      $game['fullDate'] = $fullDate;
      $game['timeStamp'] = $timeStamp;
      $game['liveIn'] = "Live";
      $output["started"][] = $game;
    }
  }

  //upcoming
  $upcoming = $matchList->find('.matches', $d1);
  $game = null;
  foreach($upcoming->find('tr') as $aGame) {
    $img1 = "http://www.gosugamers.net".$aGame->find('img', 0)->src;
    $team1 =  trim($aGame->find('.opp1', 0)->plaintext);
    if (!$team1) {
      continue;
    }
    $img2  = "http://www.gosugamers.net".$aGame->find('img', 1)->src;
    $team2 =  trim($aGame->find('.opp2', 0)->plaintext);
    $linkID = "http://www.gosugamers.net".$aGame->find('a', 0)->href;
    $date = trim($aGame->find('.live-in', 0)->plaintext);

    $html = file_get_contents($linkID);
    $titleList->load($html);
    $bestof = $titleList->find('.match-extras .bestof', 0)->plaintext;
    $bestof = explode(' ', $bestof)[2];
    if(!is_numeric($bestof)) $bestof = '?';
    $eventName = $titleList->find('.box-match-page > h2 a', 0)->plaintext . " [BO{$bestof}]";
    $fullDate = $titleList->find('.match-extras .datetime', 0)->plaintext;
    $fullDate = str_replace("at", "", $fullDate);
    $fullDate = $fullDate . "Europe/Berlin";
    $timeStamp = strtotime($fullDate);

    $game['img1'] = $img1;
    $game['team1'] = $team1;
    $game['img2'] = $img2;
    $game['team2'] = $team2;
    $game['linkID'] = $linkID;
    $game['bestof'] = $bestof;
    $game['eventName'] = $eventName;
    $game['fullDate'] = $fullDate;
    $game['timeStamp'] = $timeStamp;
    $game['liveIn'] = $date;
    $output["upcoming"][] = $game;
  }

  //done
  $done = $matchList->find('.matches', $d2);
  $game = null;
  foreach($done->find('tr') as $aGame) {
    $img1 = "http://www.gosugamers.net".$aGame->find('img', 0)->src;
    $team1 =  trim($aGame->find('.opp1', 0)->plaintext);
    if (!$team1) {
      continue;
    }
    $img2  = "http://www.gosugamers.net".$aGame->find('img', 1)->src;
    $team2 =  trim($aGame->find('.opp2', 2)->plaintext);
    $linkID = "http://www.gosugamers.net".$aGame->find('a', 0)->href;

    $html = file_get_contents($linkID);
    $titleList->load($html);
    $bestof = $titleList->find('.match-extras .bestof', 0)->plaintext;
    $bestof = explode(' ', $bestof)[2];
    if(!is_numeric($bestof)) $bestof = '?';
    $eventName = $titleList->find('.box-match-page > h2 a', 0)->plaintext . " [BO{$bestof}]";
    $fullDate = $titleList->find('.match-extras .datetime', 0)->plaintext;
    $fullDate = str_replace("at", "", $fullDate);
    $fullDate = $fullDate . "Europe/Berlin";
    $timeStamp = strtotime($fullDate);
    $score1 = 0 + trim($titleList->find('.match-extras .hidden span', 0)->plaintext);
    $score2 = 0 + trim($titleList->find('.match-extras .hidden span', 1)->plaintext);


    $game['img1'] = $img1;
    $game['team1'] = $team1;
    $game['score1'] = $score1;
    $game['img2'] = $img2;
    $game['team2'] = $team2;
    $game['score2'] = $score2;
    $game['linkID'] = $linkID;
    $game['bestof'] = $bestof;
    $game['eventName'] = addslashes($eventName);
    $game['fullDate'] = $fullDate;
    $game['timeStamp'] = $timeStamp;
    $output["ended"][] = $game;
  }


  $str = json_encode($output);
  $filestr    = "index.html";
  $fp=@fopen($filestr, 'w');
  fwrite($fp, $str);
  fwrite($fp, "");
  fclose($fp);
  echo $str;
?>
