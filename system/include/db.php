<?php

require_once($GLOBAL_BASE."/system/include/exceptions.php");
require_once($GLOBAL_BASE."/system/include/helpers.php");
require_once($GLOBAL_BASE."/system/include/fs.php");

function db_backup($db_path, $dbcount_path, $db_backup) {
  // backup
  if(is_dir($db_backup["db_backup_directory"]) == false) {
    if(mkdir($db_backup["db_backup_directory"]) == false) {
      throw new Exception("Unable to create backup directory");
    }
  }

  //
  $backup_name = nowOwn();

  // rename is disabled on free.fr
  if(copy($db_path, $db_backup["db_backup_directory"]."/".backup_file($db_backup["db_backup_prefix"], $backup_name)) === false) {
    throw new Exception("Unable to backup file");
  }
  if(file_exists($dbcount_path)) {
    if(copy($dbcount_path, $db_backup["db_backup_directory"]."/".backup_file($db_backup["dbcount_backup_prefix"], $backup_name)) === false) {
      throw new Exception("Unable to backup file");
    }
  }
}

function db_clean($db_path, $dbcount_path) {
  if(file_exists($db_path)) {
    if(unlink($db_path) == false) {
      throw new Exception("Unable to remove the file");
    }
  }
  if(file_exists($dbcount_path)) {
    if(unlink($dbcount_path) == false) {
      throw new Exception("Unable to remove the file");
    }
  }
}

function db_reset($db_path, $handicap, $komi, $steps) {
  $partykey = nowOwn();
  $str = "";
  $str .= "KEY ".$partykey."\n";
  $str .= "HANDICAP ".$handicap."\n";
  $str .= "KOMI ".$komi."\n";
  $str .= "BOARD_SIZE ".($steps+1)."\n";

  if(fs_fwrite($db_path, $str, "w") === false) {
    throw new Exception("fwrite failed");
  }

  return newGame($partykey, $handicap, $komi, $steps);
}

function newGame($partykey, $handicap, $komi, $steps) {
  return array(
               "partykey"       => $partykey, // id of the party
               "board"          => array(),   // board[x][y] = "W" or "B"
               "next_is_black"  => true,      // who is the next player to play ?
               "handicap"       => $handicap, // handicap of the party (0 to 9)
	       "komi"           => $komi,     // komi value
               "steps"          => $steps,    // board size
               "last_move"      => array(),   // date, x, y is last move or empty for passing
               "previous_move"  => array(),   // n-1 movement
               "previous_kill"  => array(),   // stones killed during the last move
               "prison_black"   => 0,         // number of black prisonners
               "prison_white"   => 0,         // number of white prisonners
               "nbactions"      => 0,         // number of actions done including revert
               "nbstones"       => 0,         // number of stones on the board
               "nbplayedstones" => 0,         // number of stones really played
               "nbpassedstones" => 0,         // number of stones really played
	       "clock"          => array("B" => 0, "W" => 0), // times played
	       "comments"       => array()    // comment[action_num][n] = array(author => $author, comment => $comment, type => $type)
               );
}

function db_read($db_path, $handicapInit, $komiInit, $stepsInit, $stopAtActionNum, &$all_last = array()) {
  $all_last = array(); // "move" => array(date[, x, y]), "kill" => array(array(x, y), ...), "clock" => array("B" => s, "W" => s))

  if(file_exists($db_path) == false) {
    $game = db_reset($db_path, $handicapInit, $komiInit, $stepsInit);
  } else {
    if( ($lines = fs_file($db_path)) === false) {
      throw new Exception();
    }
    $docontinue = true;
    $nlines = count($lines);
    $previous_play_mktime = "";
    $i = 0;
    while($i < $nlines && $docontinue) {
      $line_values = explode(" ", $lines[$i]);
      $action = $line_values[0];

      switch($action) {

	// headers
      case "KEY":
	$value = $line_values[1];
	$game = newGame($value, $handicapInit, $komiInit, $stepsInit);
	break;
      case "HANDICAP":
	$value = $line_values[1];
	$game["handicap"] = $value;
	break;
      case "KOMI":
	$value = $line_values[1];
	$game["komi"] = $value;
	break;
      case "BOARD_SIZE":
	$value = $line_values[1];
	$game["steps"] = $value-1;
	break;

	// comments
      case "COMMENT":
	$type    = $line_values[1];
	$author  = $line_values[2];
	$comment = $line_values[3];
	$date    = $line_values[4];
	if(isset($game["comments"][$game["nbactions"]]) == false) {
	  $game["comments"][$game["nbactions"]] = array();
	}
	$game["comments"][$game["nbactions"]][count($game["comments"][$game["nbactions"]])] = array("author"  => $author,
												   "type"    => $type,
												   "comment" => $comment);
	break;

	// playing actions
      case "PLAY":
      case "PASS":
      case "CANCELPASS":
      case "REVERT":
      case "KILL":
      case "RESURRECT":
        if($action == "PASS" || $action == "CANCELPASS") { // don't read coords for PASS
          $date = $line_values[1];
        } else {
	  $rx   = $line_values[1];
	  $ry   = $line_values[2];
	  $date = $line_values[3];
	  $x = $rx-1;
	  $y = ord($ry)-ord('A');
	}

        switch($action) {
        case "PLAY":
          if($game["nbactions"]+1 <= $stopAtActionNum || $stopAtActionNum == -1) {
            $game["board"][$x][$y] = $game["next_is_black"] ? "B" : "W";
            $game["nbactions"]++;
            $game["nbstones"]++;
            $game["nbplayedstones"]++;
	    $game["clock"][$game["next_is_black"] ? "B" : "W"] += ($previous_play_mktime == "" ? 0 : time2Ts($date) - $previous_play_mktime);
            $all_last[count($all_last)] = array("move" => array($date, $x, $y), "kill" => array(), "clock" => $game["clock"]);
            if($game["nbstones"] > $game["handicap"]) {
              $game["next_is_black"] = !$game["next_is_black"];
            } else {
              $game["next_is_black"] = true;
            }
	    $previous_play_mktime = time2Ts($date);
          } else {
            $docontinue = false;
          }
          break;
	case "PASS":
	  if($game["nbactions"]+1 <= $stopAtActionNum || $stopAtActionNum == -1) {
	    $game["nbactions"]++;
            $game["nbpassedstones"]++;
	    $game["clock"][$game["next_is_black"] ? "B" : "W"] += ($previous_play_mktime == "" ? 0 : time2Ts($date) - $previous_play_mktime);
	    $all_last[count($all_last)] = array("move" => array($date), "kill" => array(), "clock" => $game["clock"]);
	    if($game["nbstones"] > $game["handicap"]) {
              $game["next_is_black"] = !$game["next_is_black"];
            } else {
              $game["next_is_black"] = true;
            }
	    $previous_play_mktime = time2Ts($date);
	  } else {
            $docontinue = false;
          }
	  break;
	case "CANCELPASS":
	  if($game["nbactions"]+1 <= $stopAtActionNum || $stopAtActionNum == -1) {
            if(count($all_last) > 0) {
              unset($all_last[count($all_last) - 1]);
            }
	    $game["nbactions"]++;
            $game["nbpassedstones"]--;
	    if($game["nbstones"] > $game["handicap"]) {
              $game["next_is_black"] = !$game["next_is_black"];
            } else {
              $game["next_is_black"] = true;
            }
	  } else {
            $docontinue = false;
          }
	  break;
        case "REVERT":
          if($game["nbactions"]+1 <= $stopAtActionNum || $stopAtActionNum == -1) {
            board_remove($game["board"], $x, $y);
            if(count($all_last) > 0) {
              unset($all_last[count($all_last) - 1]);
            }
            $game["nbactions"]++;
            $game["nbstones"]--;
            $game["nbplayedstones"]--;
            if($game["nbstones"] > $game["handicap"]) {
              $game["next_is_black"] = !$game["next_is_black"];
            } else {
              $game["next_is_black"] = true;
            }
          } else {
            $docontinue = false;
          }
          break;
        case "KILL":
          $n = count($all_last)-1;
          $all_last[$n]["kill"][count($all_last[$n]["kill"])] = array($x, $y);
          board_remove($game["board"], $x, $y);
          if($game["next_is_black"]) {
            $game["prison_black"]++;
          } else {
            $game["prison_white"]++;
          }
          $game["nbstones"]--;
          break;
        case "RESURRECT":
          $game["board"][$x][$y] = $game["next_is_black"] ? "W" : "B";
          if($game["next_is_black"]) {
            $game["prison_white"]--;
          } else {
            $game["prison_black"]--;
          }
          $game["nbstones"]++;
          break;
        }
      }
      $i++;
    }
  }

  $game["last_move"]     = count($all_last) > 0 ? $all_last[count($all_last)-1]["move"] : array();
  $game["previous_move"] = count($all_last) > 1 ? $all_last[count($all_last)-2]["move"] : array();
  $game["previous_kill"] = count($all_last) > 0 ? $all_last[count($all_last)-1]["kill"] : array();

  return $game;
}

function board_remove(&$board, $x, $y) {
  unset($board[$x][$y]);
  if(count($board[$x]) == 0) {
    unset($board[$x]);
  }
}

function board_hasNeighbourInListWithColor($board, $list, $x, $y, $color) {
  foreach($list AS $item) {
    if($board[$item[0]][$item[1]] == $color &&
       (
        ($item[0] == $x && ($item[1] == $y-1 || $item[1] == $y+1)) ||
        ($item[1] == $y && ($item[0] == $x-1 || $item[0] == $x+1)))
       ) {
      return true;
    }
  }

  return false;
}

function db_checkBasicMove($game, $x, $y) {
  // in case of handicap, check color is different
  $previous_is_white = $game["next_is_black"];
  if($game["handicap"] >= $game["nbstones"]-1) {
    $previous_is_white = false;
  }
  return (isset($game["board"][$x][$y]) == false ||
          ($game["last_move"][1] == $x &&
	   $game["last_move"][2] == $y &&
           (($game["board"][$x][$y] == "W" &&  $previous_is_white) ||
            ($game["board"][$x][$y] == "B" && !$previous_is_white))));
}

function db_play($db_path, &$game, $x, $y) {
  if(db_checkBasicMove($game, $x, $y) == false) {
    throw new InvalidMoveException("Invalid move (".$x.", ".$y.")");
  }
  
  if(isset($game["board"][$x][$y])) { // update current values
    // delete the move -- this is unplay
    board_remove($game["board"], $x, $y);
    $game["nbstones"]--;
    $game["nbplayedstones"]--;
    $game["last_move"] = $game["previous_move"];
    db_action($db_path, $x, $y, "REVERT");

    if($game["next_is_black"]) {
      $game["prison_black"]-=count($game["previous_kill"]);
    } else {
      $game["prison_white"]-=count($game["previous_kill"]);
    }

    foreach($game["previous_kill"] AS $s) {
      $game["board"][$s[0]][$s[1]] = $game["next_is_black"] ? "B" : "W";
      db_action($db_path, $s[0], $s[1], "RESURRECT");
    }

    if($game["handicap"] < $game["nbstones"]+1) {
      $game["next_is_black"] = !$game["next_is_black"];
    }
  } else {
    // add
    $game["board"][$x][$y] = $game["next_is_black"] ? "B" : "W";
    $dead = board_find_dead($game);

    // ko ?
    $ko = false;
    if(count($dead) == 2 && count($game["previous_kill"]) == 1) { // == 2 because kill+suicide
      // previous kill is current move &&
      // previous move is current kill
      if($game["previous_kill"][0][0] == $x && $game["previous_kill"][0][1] == $y) {
	if(
	   ($game["last_move"][1] == $dead[0][0] && $game["last_move"][2] == $dead[0][1] && $x == $dead[1][0] && $y == $dead[1][1]) ||
	   ($game["last_move"][1] == $dead[1][0] && $game["last_move"][2] == $dead[1][1] && $x == $dead[0][0] && $y == $dead[0][1])
	   ) {
	  $ko = true;
	}
      }
    }
    if($ko) {
      board_remove($game["board"], $x, $y); // rollback the move
      throw new InvalidMoveException("KO");
    }

    // suicide move ?
    $ad = false;
    if(count($dead) > 0) {
      foreach($dead AS $d) {
        if($d[0] == $x && $d[1] == $y) {
          $ad = true;
        }
      }
    }
    if($ad) {
      // ok, this is a suicide move, however, one special case : when a neighbour of $x, $y with a different color is a prisoner too
      // => mark $x, $y as free and recompute prisoners
      if(board_hasNeighbourInListWithColor($game["board"], $dead, $x, $y, $game["next_is_black"] ? "W" : "B")) {
        $dead = board_find_dead($game, $x, $y);
      } else {
        board_remove($game["board"], $x, $y); // rollback the move
        throw new InvalidMoveException("Suicide");
      }
    }

    $game["nbstones"]++;
    $game["nbplayedstones"]++;
    $game["last_move"] = array(nowOwn(), $x, $y);
    db_action($db_path, $x, $y, "PLAY");

    if($game["next_is_black"]) {
      $game["prison_white"]+=count($dead);
    } else {
      $game["prison_black"]+=count($dead);
    }
    $game["nbstones"]-=count($dead);

    if(count($dead) > 0) {
      foreach($dead AS $d) {
        db_action($db_path, $d[0], $d[1], "KILL");
        board_remove($game["board"], $d[0], $d[1]);
      }
    }

    if($game["handicap"] < $game["nbstones"]) {
      $game["next_is_black"] = !$game["next_is_black"];
    }
  }

  $game["nbactions"]++;
}

function db_pass($db_path, &$game) {
  $game["last_move"] = array(nowOwn());
  db_actionline($db_path, "PASS");

  if($game["handicap"] < $game["nbstones"]) {
    $game["next_is_black"] = !$game["next_is_black"];
  }

  $game["nbactions"]++;
}

function db_cancelpass($db_path, &$game) {
  $game["last_move"] = array(nowOwn());
  db_actionline($db_path, "CANCELPASS");

  if($game["handicap"] < $game["nbstones"]) {
    $game["next_is_black"] = !$game["next_is_black"];
  }

  $game["nbactions"]++;
}

function board_find_dead($game, $force_free_x=-1, $force_free_y=-1) {
  // free[x][y] : (0 = false, 1 = true, unset : unknown)

  // mark them all as dead
  for($i=0; $i<=$game["steps"]; $i++) {
    for($j=0; $j<=$game["steps"]; $j++) {
      $free[$i][$j] = 0;
    }
  }

  if(isset($free[$force_free_x][$force_free_y])) {
    board_mark_free($game, $free, $force_free_x, $force_free_y);
  }

  // find alive stones
  for($i=0; $i<=$game["steps"]; $i++) {
    for($j=0; $j<=$game["steps"]; $j++) {
      if(isset($game["board"][$i][$j]) == false) {
        if($free[$i][$j] == 0) { // still set as dead
          board_mark_free($game, $free, $i, $j);
        }
      }
    }
  }

  $dead = array();
  for($i=0; $i<=$game["steps"]; $i++) {
    for($j=0; $j<=$game["steps"]; $j++) {
      if($free[$i][$j] == 0) {
        $dead[count($dead)] = array($i, $j);
      }
    }
  }

  return $dead;
}

function db_actionline($db_path, $line) {
  if(fs_fwrite($db_path, $line." ".nowOwn()."\n", "a") === false) {
    throw new Exception("fwrite failed");
  }
}

function db_action($db_path, $x, $y, $action) {
  if($x == -1) {
    $rx = "-";
  } else {
    $rx = $x+1;
  }
  if($y == -1) {
    $ry = "-";
  } else {
    $ry = chr(ord('A')+$y);
  }
  db_actionline($db_path, $action." ".$rx." ".$ry);
}

function board_mark_free($game, &$free, $x, $y) {
  if($free[$x][$y] == 1) { // already set as alive
    return;
  }

  $free[$x][$y] = 1;

  // if x,y is empty, mark the 4 around as free
  if(isset($game["board"][$x][$y]) == false) {
    if($x < $game["steps"]) board_mark_free($game, $free, $x+1, $y);
    if($x > 0)              board_mark_free($game, $free, $x-1, $y);
    if($y < $game["steps"]) board_mark_free($game, $free, $x,   $y+1);
    if($y > 0)              board_mark_free($game, $free, $x,   $y-1);
    return;
  }

  // if x,y is black, mark the blacks around as free
  foreach(array("B", "W") AS $color) {
    if($game["board"][$x][$y] == $color) {
      if($x < $game["steps"] && isset($game["board"][$x+1][$y]) && $game["board"][$x+1][$y] == $color) board_mark_free($game, $free, $x+1, $y);
      if($x > 0              && isset($game["board"][$x-1][$y]) && $game["board"][$x-1][$y] == $color) board_mark_free($game, $free, $x-1, $y);
      if($y < $game["steps"] && isset($game["board"][$x][$y+1]) && $game["board"][$x][$y+1] == $color) board_mark_free($game, $free, $x,   $y+1);
      if($y > 0              && isset($game["board"][$x][$y-1]) && $game["board"][$x][$y-1] == $color) board_mark_free($game, $free, $x,   $y-1);
      return;
    }
  }
}

function db_comment($db_path, $type, $author, $comment) {
  if( !($type == "VISIBLE" || $type == "HIDDEN") ||
      $author  == "" ||
      $comment == "") {
    throw new Exception("");
  }

  db_actionline($db_path, "COMMENT ".$type." ".txt2htmlOneLine($author)." ".txt2htmlOneLine($comment));
}

function comment_db2html($str) {
  return str_replace("&nbsp;", " ", $str);
}

function comment_db2htmljs($str) {
  return str_replace("\\", "\\\\", comment_db2html($str));
}

function backup_checkFile($db_backup_directory, $file, $mustExists = true) {
 // check that the file is really a file destinated to backups
 $pm = preg_match("/^[0-9a-zA-Z-\.]*$/", $file); // no / or \ or special char
 if($pm === FALSE || $pm != 1) {
   throw new Exception("");
 }
 if($mustExists) {
   if(file_exists($db_backup_directory."/".$file) == false) {
     throw new Exception("Invalid file");
   }
 }
}

function backup_file($db_backup_prefix, $db_backup_name) {
  return $db_backup_prefix.$db_backup_name;
}

function backup_countfile($dbcount_backup_prefix, $db_backup_name) {
  return $dbcount_backup_prefix.$db_backup_name;
}

function choose_db_file($pfile, $dbpath, $db_backup_directory, $db_backup_prefix) {
  $pfile = html_getParameter("file");
  if($pfile == "") {
    return $dbpath;
  } else {
    $rfile = backup_file($db_backup_prefix, $pfile);
    backup_checkFile($db_backup_directory, $rfile);
    return $db_backup_directory."/".$rfile;
  }
}

function choose_dbcount_file($pfile, $dbcountpath, $db_backup_directory, $db_backup_prefix, $dbcount_backup_prefix) {
  $pfile = html_getParameter("file");
  if($pfile == "") {
    return $dbcountpath;
  } else {
    $rfile = backup_countfile($dbcount_backup_prefix, $pfile);
    backup_checkFile($db_backup_directory, $rfile, false);
    // the file can not exist but the associated db must exist
    backup_checkFile($db_backup_directory, backup_file($db_backup_prefix, $pfile));

    return $db_backup_directory."/".$rfile;
  }
}

function db_getOldGames($db_backup_directory, $db_backup_prefix) {
  $entries = array();
  
  if ( ($handle = opendir($db_backup_directory)) === false) {
    return $entries;
  }
  while(($entry = readdir($handle)) !== false) {
    if ($entry != "." && $entry != "..") {
      if(is_file($db_backup_directory."/".$entry)) {
	$pm = preg_replace("/^".$db_backup_prefix."/", "", $entry, 1, $n); // backup files are based on the dbfile name
	if($n == 1) {
	  $entries[$pm] = true;
	}
      }
    }
  }
  krsort($entries);
  closedir($handle);

  return $entries;
}

?>
