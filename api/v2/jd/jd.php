<?php
  set_time_limit (0);
  error_reporting(0);

  require_once('../simple_html_dom.php');
  date_default_timezone_set("America/Montreal");
  $html = file_get_contents('http://www.joindota.com/en/matches');
  $matchList = new simple_html_dom();
  $matchList->load($html);
  $titleList = new simple_html_dom();
  $gameArray = array();
  $i = 0;
  foreach($matchList->find('div[id=matchticker] .item') as $aGame) {
    $img1 = $aGame->find('img', 0)->src;
    $img1tit = $aGame->find('img', 0)->title;
    $team1 = $aGame->find('.sub', 0)->plaintext;
    $img2  = $aGame->find('img', 1)->src;
    $img2tit  = $aGame->find('img', 1)->title;
    $team2 = $aGame->find('.sub', 2)->plaintext;
    $date = $aGame->find('.sub', 3)->plaintext;
    $id = $aGame->find('div', 3)->id;

    $date = trim($date);
    //check to see if there's a ':' in the string (denonating a finished game)
    if (!strpos($date, ':')) {
      $id = substr($id, -5);
      $linkID = "http://www.joindota.com/en/matches/{$id}";
      $titleExt = file_get_contents($linkID);
      $titleList->load($titleExt);
      $titleStr = $titleList->find('.match_head .left', 0)->plaintext;
      $timeStampOrig = $titleList->find('.match_head .right', 0)->plaintext;
      $timeStamp = strtotime($timeStampOrig);
      if ($date !== 'LIVE' && $date !== 'now') {
        $game['id'] = $id;
        $game['img1'] = $img1;
        $game['img1tit'] = $img1tit;
        $game['team1'] = $team1;
        $game['img2'] = $img2;
        $game['img2tit'] = $img2tit;
        $game['team2'] = $team2;
        $game['linkID'] = $linkID;
        $game['eventName'] = $titleStr;
        $game['fullDate'] = $timeStampOrig;
        $game['timeStamp'] = $timeStamp;
        $game['liveIn'] = $date;
        $gameArray["upcoming"][] = $game;
      } else {
        $date = "Live";
        $game['id'] = $id;
        $game['img1'] = $img1;
        $game['img1tit'] = $img1tit;
        $game['team1'] = $team1;
        $game['img2'] = $img2;
        $game['img2tit'] = $img2tit;
        $game['team2'] = $team2;
        $game['linkID'] = $linkID;
        $game['eventName'] = $titleStr;
        $game['fullDate'] = $timeStampOrig;
        $game['timeStamp'] = $timeStamp;
        $game['liveIn'] = $date;
        $gameArray["started"][] = $game;
      }
    }
  }

  foreach($matchList->find('.pad .item') as $aGame) {
    if ($i < 10) {
      $winner = str_replace(' ', '', $aGame->find('.sub', 2)->plaintext);
      if (strlen($winner) != 3)
        continue;
      $img1 = $aGame->find('img', 0)->src;
      $img1tit = $aGame->find('img', 0)->title;
      $team1 = $aGame->find('.sub', 1)->plaintext;

      $img2  = $aGame->find('img', 1)->src;
      $img2tit  = $aGame->find('img', 1)->title;
      $team2 = $aGame->find('.sub', 3)->plaintext;

      $id = substr($aGame->href, 35);
      $winnerCheck = explode(':', $winner);

      $linkID = $aGame->find('a', 0)->href;
      $titleExt = file_get_contents($linkID);
      $titleList->load($titleExt);
      $titleStr = $titleList->find('.match_head .left', 0)->plaintext;
      $date = $titleList->find('.match_head .right', 0)->plaintext;

      $timeStamp = strtotime($date);
      //$whenAdv = date('j/m/Y G:i T', $timeStamp);
      $i++;

      $game['img1'] = $img1;
      $game['img1tit'] = $img1tit;
      $game['team1'] = $team1;
      $game['score1'] = (int)$winnerCheck[0];
      $game['img2'] = $img2;
      $game['img2tit'] = $img2tit;
      $game['team2'] = $team2;
      $game['score2'] = (int)$winnerCheck[1];
      $game['linkID'] = $linkID;
      $game['eventName'] = $titleStr;
      $game['fullDate'] = $date;
      $game['timeStamp'] = $timeStamp;
      $gameArray["ended"][] = $game;
    } else {
      break;
    }
  }

  $gameArray["upcoming"] = array_reverse($gameArray["upcoming"]);
  if (!$gameArray["upcoming"]) {
    $gameArray["upcoming"] = array();
  }
  $str = json_encode($gameArray);
  $filestr    = "index.html";
  $fp=@fopen($filestr, 'w');
  fwrite($fp, $str);
  fwrite($fp, "");
  fclose($fp);
  echo $str;
?>