<?php

require_once($GLOBAL_BASE."/system/include/fs.php");

function dbcount_read($dbcount_path, $board) {
  if(file_exists($dbcount_path) == false) {
    return dbcount_reset($dbcount_path);
  }

  if( ($lines = fs_file($dbcount_path)) === false) {
    throw new Exception();
  }

  $res = array("B" => array(), "W" => array());
  foreach($lines AS $line) {
    list($atype, $rx, $ry) = explode(" ", $line);
    $x = $rx-1;
    $y = ord($ry)-ord('A');
    switch($atype) {
    case "ADD":
      if(isset($board[$x][$y])) {
	$res[$board[$x][$y]][$x][$y] = true;
      }
      break;
    case "REMOVE":
      if(isset($board[$x][$y])) {
	if(isset($res[$board[$x][$y]][$x][$y])) {
	  unset($res[$board[$x][$y]][$x][$y]);
	}
      }
      break;
    }
  }

  return $res;
}

function dbcount_reset($dbcount_path) {
  if( ($fd = fopen($dbcount_path, "w")) === false) {
    throw new Exception();
  }
  if(fclose($fd) === false) {
    throw new Exception();
  }

  return array("B" => array(), "W" => array());
}

function dbcount_action($dbcount_path, &$dbcount, $game, $x, $y) {
  if(isset($game["board"][$x][$y]) == false) {
    throw new Exception();
  }

  if(isset($dbcount[$game["board"][$x][$y]][$x][$y])) {
    $atype = "REMOVE";
  } else {
    $atype = "ADD";
  }

  $dead = array();
  board_mark_dead($game, $dead, $x, $y);

  // only if some changes appears
  if(count($dead) > 0) {
    foreach($dead AS $dx => $ly) {
      foreach($ly AS $dy => $vtmp) {
	if($atype == "ADD") {
	  $dbcount[$game["board"][$x][$y]][$dx][$dy] = true;
	} else {
	  unset($dbcount[$game["board"][$x][$y]][$dx][$dy]);
	}
	$rx = $dx+1;
	$ry = chr(ord('A')+$dy);
	if(fs_fwrite($dbcount_path, $atype." ".$rx." ".$ry."\n", "a") === false) {
	  throw new Exception();
	}
      }
    }
  }
}

function board_mark_dead($game, &$dead, $x, $y) {
  if(isset($dead[$x][$y])) { // already set as dead
    return;
  }

  // mark as dead
  $dead[$x][$y] = true;

  // find others
  if($x < $game["steps"]) {
    if(isset($game["board"][$x+1][$y]) == true) {              // must be a stone
      if($game["board"][$x+1][$y] == $game["board"][$x][$y]) { // must be the same color
	if(isset($dead[$x+1][$y]) == false) {                  // not already taken
	   board_mark_dead($game, $dead, $x+1, $y);
	}
      }
    }
  }
  if($x > 0) {
    if(isset($game["board"][$x-1][$y]) == true) {
      if($game["board"][$x-1][$y] == $game["board"][$x][$y]) {
	if(isset($dead[$x-1][$y]) == false) {
	   board_mark_dead($game, $dead, $x-1, $y);
	}
      }
    }
  }
  if($y < $game["steps"]) {
    if(isset($game["board"][$x][$y+1]) == true) {
      if($game["board"][$x][$y+1] == $game["board"][$x][$y]) {
	if(isset($dead[$x][$y+1]) == false) {
	   board_mark_dead($game, $dead, $x, $y+1);
	}
      }
    }
  }
  if($y > 0) {
    if(isset($game["board"][$x][$y-1]) == true) {
      if($game["board"][$x][$y-1] == $game["board"][$x][$y]) {
	if(isset($dead[$x][$y-1]) == false) {
	   board_mark_dead($game, $dead, $x, $y-1);
	}
      }
    }
  }

}

function board_find_points($game, $dead) {
  $res = array("B" => array(), "W" => array());

  // $points : "UNKOWN" = unknown ; "USED" = used ; "B" = black ; "W" = white ; "BOTH" = black and white ; "DEAD" = dead
  $points = array();

  // consider at least once each point
  for($x=0; $x<=$game["steps"]; $x++) {
    for($y=0; $y<=$game["steps"]; $y++) {
      board_mark_points($game, $dead, $points, $x, $y);
    }
  }

  foreach($points AS $x => $ly) {
    foreach($ly AS $y => $vtmp) {
      switch($points[$x][$y]) {
      case "B":
      case "W":
	$res[$points[$x][$y]][$x][$y] = true;
	break;
      }
    }
  }

  return $res;
}

function board_mark_points($game, $dead, &$points, $x, $y) {

  if(isset($game["board"][$x][$y])) {
    if(isset($dead["B"][$x][$y]) || isset($dead["W"][$x][$y])) {
      $points[$x][$y] = "DEAD";
    } else {
      $points[$x][$y] = "USED";
    }
  } else {
    if(isset($points[$x][$y]) == false) {
      $points[$x][$y] = "UNKNOWN";
    }
  }

  // find others
  if($x < $game["steps"]) {
    board_mark_points_contamination($game, $dead, $points, $x, $y, $x+1, $y);
  }

  if($x > 0) {
    board_mark_points_contamination($game, $dead, $points, $x, $y, $x-1, $y);
  }

  if($y < $game["steps"]) {
    board_mark_points_contamination($game, $dead, $points, $x, $y, $x, $y+1);
  }

  if($y > 0) {
    board_mark_points_contamination($game, $dead, $points, $x, $y, $x, $y-1);
  }
}

function board_mark_points_contamination($game, $dead, &$points, $x, $y, $nx, $ny) {
  if(isset($points[$nx][$ny])) {
    switch ($points[$nx][$ny]) {

    case "B":
    case "W":
      switch($points[$x][$y]) {
      case "B":
      case "W":
	if($points[$x][$y] != $points[$nx][$ny]) {
	  $points[$x][$y] = "BOTH";
	  board_mark_points($game, $dead, $points, $x,  $y); // reconsider all my neighbours
	  board_mark_points($game, $dead, $points, $nx, $ny);
	}
	break;
      case "USED":
	if($game["board"][$x][$y] != $points[$nx][$ny]) {
	  board_mark_points($game, $dead, $points, $nx, $ny);
	}
	break;
      case "DEAD":
	if($game["board"][$x][$y] == $points[$nx][$ny]) {
	  board_mark_points($game, $dead, $points, $nx, $ny);
	}
	break;
      case "UNKNOWN":
	$points[$x][$y] = $points[$nx][$ny];
	board_mark_points($game, $dead, $points, $x, $y); // reconsider all my neighbours
	break;
      case "BOTH":
	board_mark_points($game, $dead, $points, $nx, $ny);
	break;
      }
    break;
    
    case "USED":
    case "DEAD":
      switch($points[$x][$y]) {
      case "B":
      case "W":
	if( ($points[$x][$y] != $game["board"][$nx][$ny] && $points[$nx][$ny] == "USED") ||
	    ($points[$x][$y] == $game["board"][$nx][$ny] && $points[$nx][$ny] == "DEAD")
	   ) {
	  $points[$x][$y] = "BOTH";
	  board_mark_points($game, $dead, $points, $x, $y); // reconsider all my neighbours
	}
        break;
      case "USED":
	// nothing to do
	break;
      case "DEAD":
	// nothing to do
	break;
      case "UNKNOWN":
	$points[$x][$y] = $points[$nx][$ny] == "USED" ? $game["board"][$nx][$ny] : ($game["board"][$nx][$ny] == "B" ? "W" : "B");
	board_mark_points($game, $dead, $points, $x, $y); // reconsider all my neighbours
	break;
      case "BOTH":
	// nothing to do
	break;
      }
      break;
      
    case "UNKNOWN":
      switch($points[$x][$y]) {
      case "B":
      case "W":
	board_mark_points($game, $dead, $points, $nx, $ny);
        break;
      case "USED":
      case "DEAD":
	board_mark_points($game, $dead, $points, $nx, $ny);
	break;
      case "UNKNOWN":
	// nothing to do
	break;
      case "BOTH":
	board_mark_points($game, $dead, $points, $nx, $ny);
	break;
      }
      break;

    case "BOTH":
      switch($points[$x][$y]) {
      case "B":
      case "W":
	$points[$x][$y] = "BOTH";
        board_mark_points($game, $dead, $points, $x, $y); // reconsider all my neighbours
	break;
      case "USED":
      case "DEAD":
	// nothing to do
	break;
      case "UNKNOWN":
	$points[$x][$y] = "BOTH";
	board_mark_points($game, $dead, $points, $x, $y); // reconsider all my neighbours
	break;
      case "BOTH":
	// nothing to do
	break;
      }
      break;
    }
  }

}

?>
