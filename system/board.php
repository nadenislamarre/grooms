<?php
$GLOBAL_BASE = "..";
require_once($GLOBAL_BASE."/system/system.php");
session_set_cookie_params($SYSTEM["session_duration"]);
session_start();

require_once($GLOBAL_BASE."/system/include/helpers.php");
setGlobalData($GLOBAL_BASE);

require_once($GLOBAL_BASE."/system/config.php");
require_once($GLOBAL_BASE."/system/include/db.php");
require_once($GLOBAL_BASE."/system/include/session.php");
require_once($GLOBAL_BASE."/system/include/graphicsHelpers.php");

nocacheHeader();

function outputJson($dbfile, $dbcountfile, $game, $graphics, $docount, $readonly, $show_comments, $old_game) {
  global $GLOBAL_BASE;
  global $GLOBAL_DATA;

  $step_width = getStepWidth($graphics["max_image_width"], $graphics["max_step_width"], $game["steps"], $graphics["offset"]);
  $width = (2*$graphics["offset"] + $game["steps"]) * $step_width;

  echo "{\n";
  if(session_isLogged($GLOBAL_DATA) == false) {
    echo " \"access\": \"guest\",\n";
  } else {
    if($readonly) {
      echo " \"access\": \"ro\",\n";
    } else {
      echo " \"access\": \"rw\",\n";
    }
  }
  echo " \"game\": {\n";
  echo "  \"key\": \"" 	      .$game["partykey"]."\",\n";
  echo "  \"steps\": " 	      .$game["steps"].",\n";
  echo "  \"handicap\": "     .$game["handicap"].",\n";
  echo "  \"action_num\": "   .$game["nbactions"].",\n";
  echo "  \"next_color\": \"" .($game["next_is_black"] ? "B" : "W")."\",\n";

  if(count($game["last_move"]) == 3) {
    // normal move
    echo "  \"last_move\": "  ."[".$game["last_move"][1].", ".$game["last_move"][2]."]".",\n";
    $last_move_date = $game["last_move"][0];
  } else {
    if(isset($game["last_move"][0])) {
      // pass move
      echo "  \"last_move\": "  ."[-1, -1]".",\n"; // -1 -1 : pass
      $last_move_date = $game["last_move"][0];
    } else {
      // no last move
      echo "  \"last_move\": "  ."[-2, -2]".",\n"; // -2, -2 : no move
      $last_move_date = "";
    }
  }
  echo "  \"last_move_date\": \"".$last_move_date."\",\n";
  echo "  \"nb_played_stones\": ".$game["nbplayedstones"].",\n";
  echo "  \"nb_passed_stones\": ".$game["nbpassedstones"].",\n";
  echo "  \"prison_black\": "   .$game["prison_black"].",\n";
  echo "  \"prison_white\": "   .$game["prison_white"].",\n";
  echo "  \"clock_black\": "   .$game["clock"]["B"].",\n";
  echo "  \"clock_white\": "   .$game["clock"]["W"].",\n";
  if($docount) {
    echo "  \"status\": \""     .getFileStatus($dbfile).'x'.getFileStatus($dbcountfile)."\",\n";
  } else {
    echo "  \"status\": \""     .getFileStatus($dbfile)."\",\n";
  }
  echo "  \"play_url\": \"   ".$GLOBAL_BASE."/system/play.php"."\",\n";
  echo "  \"action_url\": \" ".$GLOBAL_BASE."/system/action.php"."\",\n";
  echo "  \"history_url\": \"".$GLOBAL_BASE."/system/history.php"."\"\n";

  echo " },\n";

  // graphics
  echo " \"graphics\": {\n";
  echo "  \"width\": " 	      .$width.",\n";
  echo "  \"offset\": "	      .$graphics["offset"].",\n";
  echo "  \"letters_offset\": "	  .$graphics["letters_offset"].",\n";
  echo "  \"stone_reduction\": "  .$graphics["stone_reduction"].",\n";
  echo "  \"shadow_width\": "   .$graphics["shadow_width"][$game["steps"]+1].",\n";
  echo "  \"shadow_offsetX\": "   .$graphics["shadow_offsetX"][$game["steps"]+1].",\n";
  echo "  \"shadow_offsetY\": "   .$graphics["shadow_offsetY"][$game["steps"]+1].",\n";
  echo "  \"img_board\": \""  .$graphics["board_prefix"].($game["steps"]+1).$graphics["board_suffix"]."\",\n";
  echo "  \"img_black\": \""  .$graphics["sblack_prefix"].($game["steps"]+1).$graphics["sblack_suffix"]."\",\n";
  echo "  \"img_white\": \""  .$graphics["swhite_prefix"].($game["steps"]+1).$graphics["swhite_suffix"]."\",\n";
  echo "  \"img_shadow\": \"" .$graphics["shadow_prefix"].($game["steps"]+1).$graphics["shadow_suffix"]."\",\n";
  echo "  \"img_pblack\": \"" .$graphics["spblack_prefix"].($game["steps"]+1).$graphics["spblack_suffix"]."\",\n";
  echo "  \"img_pwhite\": \"" .$graphics["spwhite_prefix"].($game["steps"]+1).$graphics["spwhite_suffix"]."\",\n";
  echo "  \"img_lblack\": \"" .$graphics["slblack_prefix"].($game["steps"]+1).$graphics["slblack_suffix"]."\",\n";
  echo "  \"img_lwhite\": \"" .$graphics["slwhite_prefix"].($game["steps"]+1).$graphics["slwhite_suffix"]."\",\n";
  echo "  \"img_none\": \""   .$graphics["snone_prefix"].($game["steps"]+1).$graphics["snone_suffix"]."\"\n";
  if($docount) {
    echo "  ,\n";
    echo "  \"img_dblack\": \"" .$graphics["sdblack_prefix"].($game["steps"]+1).$graphics["sdblack_suffix"]."\",\n";
    echo "  \"img_dwhite\": \"" .$graphics["sdwhite_prefix"].($game["steps"]+1).$graphics["sdwhite_suffix"]."\",\n";
    echo "  \"img_ptblack\": \"" .$graphics["sptblack_prefix"].($game["steps"]+1).$graphics["sptblack_suffix"]."\",\n";
    echo "  \"img_ptwhite\": \"" .$graphics["sptwhite_prefix"].($game["steps"]+1).$graphics["sptwhite_suffix"]."\",\n";
    echo "  \"img_nopoint\": \""   .$graphics["snopoint_prefix"].($game["steps"]+1).$graphics["snopoint_suffix"]."\"\n";
  }
  echo " },\n";

  echo " \"stones\": {\n";
  $first_color = true;
  $nbstones["B"] = 0;
  $nbstones["W"] = 0;
  foreach(array("B", "W") AS $color) {
    if($first_color) {
      $first_color = false;
    } else {
      echo ",\n";
    }
    $nstone = 0;
    echo "  \"".$color."\": [";
    foreach($game["board"] AS $x => $y_values) {
      foreach($y_values AS $y => $value) {
       if($color == $value) {
        if($nstone != 0) {
	  echo ",";
	}
	$nstone++;
	if($nstone %20 == 0) {
	  echo "\n        ";
	}
	echo "[".$x.",".$y."]";
	$nbstones[$color]++;
       }
      }
    }
    echo "]";
  }
  echo "\n },\n";

  // comments
  $first_action_pool = true;
  echo " \"comments\": {\n";
  if($show_comments) {
    foreach($game["comments"] AS $nb_actions => $action_comments) {
      if($first_action_pool == false) {
	echo ",\n";
      } else {
	$first_action_pool = false;
      }
      echo "  \"".$nb_actions."\": {\n";
      $first_comment_pool = true;
      foreach($action_comments AS $x => $comment) {
	if($comment["type"] == "VISIBLE" || $old_game) {
	  if($first_comment_pool == false) {
	    echo ",\n";
	  } else {
	    $first_comment_pool = false;
	  }
	  echo "   \"".$x."\": {\n";
	  echo "     \"author\": \"".comment_db2htmljs($comment["author"])."\",\n";
	  echo "     \"type\": \"".$comment["type"]."\",\n";
	  echo "     \"value\": \"".comment_db2htmljs($comment["comment"])."\"\n";
	  echo "   }";
	}
      }
      echo "\n  }";
    }
  }

  if($docount) {
    echo "\n }";
  } else {
    echo "\n }\n";
  }

  // count
  if($docount) {
    // deads
    require_once($GLOBAL_BASE."/system/include/dbcount.php");
    $deads = dbcount_read($dbcountfile, $game["board"]);
    echo ",\n";
    echo " \"counts\": {\n";

    echo "  \"deads\": {\n";
    $first_color = true;
    $nbdead["B"] = 0;
    $nbdead["W"] = 0;
    foreach($deads AS $color => $dead_stones) {
      if($first_color) {
	$first_color = false;
      } else {
	echo ",\n";
      }
      echo "   \"".$color."\": [";
      $first_stone = true;
      foreach($dead_stones AS $x => $y_values) {
	foreach($y_values AS $y => $val) {
	  if($first_stone) {
	    $first_stone = false;
	  } else {
	    echo ", ";
	  }
	  echo "[".$x.",".$y."]";
	  $nbdead[$color]++;
	}
      }
      echo "]";
    }
    echo "\n  },\n";
    
    // points
    $points = board_find_points($game, $deads);
    echo "  \"points\": {\n";
    $first_color = true;
    $nbpoints["B"] = 0;
    $nbpoints["W"] = 0;
    foreach($points AS $color => $points_stones) {
      if($first_color) {
	$first_color = false;
      } else {
	echo ",\n";
      }
      echo "   \"".$color."\": [";
      $first_stone = true;
      foreach($points_stones AS $x => $y_values) {
	foreach($y_values AS $y => $val) {
	  if($first_stone) {
	    $first_stone = false;
	  } else {
	    echo ", ";
	  }
	  echo "[".$x.",".$y."]";
	  $nbpoints[$color]++;
	}
      }
      echo "]";
    }
    echo "\n  },\n";


    echo "  \"scores\": {\n";

    $jscore["B"] = $nbpoints["B"]-$game["prison_black"]-$nbdead["B"] + $nbdead["W"];
    $jscore["W"] = $nbpoints["W"]-$game["prison_white"]-$nbdead["W"] + $nbdead["B"] + $game["komi"];
    $cscore["B"] = $nbpoints["B"] + ($nbstones["B"]-$nbdead["B"]) + $nbdead["W"];
    $cscore["W"] = $nbpoints["W"] + ($nbstones["W"]-$nbdead["W"]) + $nbdead["B"] + $game["komi"];

    echo "   \"komi\": ".$game["komi"].",\n";
    echo "   \"B\": {\n";
    echo "    \"nbprisoners\": ".$game["prison_black"].",\n";

    echo "    \"nbareas\": ".($nbpoints["B"]+$nbdead["W"]).",\n";
    echo "    \"nbdeads\": ".($nbdead["B"]).",\n";
    echo "    \"nblivingstones\": ".($nbstones["B"]-$nbdead["B"]).",\n";
    echo "    \"chinese_rule_score\": ".($cscore["B"]).",\n";
    echo "    \"japanese_rule_score\": ".($jscore["B"])."\n";

    echo "   },\n";
    echo "   \"W\": {\n";
    echo "    \"nbprisoners\": ".$game["prison_white"].",\n";

    echo "    \"nbareas\": ".($nbpoints["W"]+$nbdead["B"]).",\n";
    echo "    \"nbdeads\": ".($nbdead["W"]).",\n";
    echo "    \"nblivingstones\": ".($nbstones["W"]-$nbdead["W"]).",\n";
    echo "    \"chinese_rule_score\": ".($cscore["W"]).",\n";
    echo "    \"japanese_rule_score\": ".($jscore["W"])."\n";
    echo "   }\n";
    echo "  }\n";

    echo " }\n";
  }

  // end
  echo "}\n";
}

// old ie version doesn't support transparent png
$usepng = true;
if(html_getParameter("png") == "false") {
 $usepng = false;
}

$dbfile = choose_db_file(html_getParameter("file"),
			 $CONFIG["db"]["db_path"], $CONFIG["backups"]["db_backup_directory"],
			 $CONFIG["backups"]["db_backup_prefix"]);
$dbcountfile = choose_dbcount_file(html_getParameter("file"),
				   $CONFIG["db"]["dbcount_path"], $CONFIG["backups"]["db_backup_directory"],
				   $CONFIG["backups"]["db_backup_prefix"], $CONFIG["backups"]["dbcount_backup_prefix"]);

$game = db_read($dbfile, $CONFIG["game"]["handicapInit"], $CONFIG["game"]["komiInit"], $CONFIG["game"]["stepsInit"], -1);
outputJson($dbfile, $dbcountfile, $game, $usepng ? $CONFIG["graphics"] : $CONFIG["graphics2"], html_getParameter("mode") == "count",
	   html_getParameter("file") != "", ($CONFIG["show_comments_if_not_connected"] || session_isLogged($GLOBAL_DATA)),
	   html_getParameter("file") != "");

?>
