<?php

require_once($GLOBAL_BASE."/system/include/fs.php");

function updateRss($link, $data, $game, $outputfile) {

  // check the rss directory
  $rss_directory = dirname($outputfile);
  if(is_dir($rss_directory) == false) {
    if(mkdir($rss_directory) == false) {
      throw new Exception("Unable to create the rss directory");
    }
  }

  $move = $game["nbplayedstones"].". ";
  if(count($game["last_move"]) == 1) {
    $move .= "Pass";
  } else {
    $move .= move2string($game["last_move"][1], $game["last_move"][2]);
    $move .= " by ".($game["board"][$game["last_move"][1]][$game["last_move"][2]] == "B" ? "blacks" : "whites");
  }
  $title = $move;
  $date=date("D, d M Y H:i:s O", time2Ts($game["last_move"][0]));

  $rss = "<?xml version=\"1.0\" encoding=\"utf8\" ?>\n";
  $rss .= "<rss version=\"2.0\">\n";
  $rss .= "<channel>\n";
  $rss .= "<title>".$data."</title>\n";
  $rss .= "<lastBuildDate>".$date."</lastBuildDate>\n";
  $rss .= "<description>Last go moves</description>\n";
  $rss .= "<language>en</language>\n";
  $rss .= "<ttl>10</ttl>\n";
  $rss .= "<item>\n";
  $rss .= "<title>".$title."</title>\n";
  $rss .= "<pubDate>".$date."</pubDate>\n";
  //$rss .= "<link>".$CONFIGURATION["link_to_web_site"]."</link>\n";
  $rss .= "<description>".$title."</description>\n";
  $rss .= "<link>".$link."</link>\n";
  $rss .= "</item>\n";
  $rss .= "</channel>\n";
  $rss .= "</rss>\n";

  if(fs_fwrite($outputfile, $rss, "w") === false) {
    throw new Exception();
  }
}

?>
