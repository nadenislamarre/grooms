<?php
$GLOBAL_BASE = "..";
require_once($GLOBAL_BASE."/system/system.php");
session_set_cookie_params($SYSTEM["session_duration"]);
session_start();

require_once($GLOBAL_BASE."/system/include/helpers.php");
setGlobalData($GLOBAL_BASE);

require_once($GLOBAL_BASE."/system/config.php");
require_once($GLOBAL_BASE."/system/include/session.php");
require_once($GLOBAL_BASE."/system/include/db.php");

nocacheHeader();

$action = html_getParameter("action");
try {
  switch($action) {
  case "login":
    session_login($GLOBAL_DATA, html_getParameter("password"));
    echo "success";
    exit(0);
    break;
  case "logout":
    // allow logout even if already done with no right
    // so that if a logout is forced on error (even already disconnected, people are sure to be disconnected)
    session_logout($GLOBAL_DATA);
    echo "success";
    exit(0);
    break;
  }
} catch (Exception $e) {
  echo $e->getMessage();
  exit(0);
}

// no more if no login
if(session_isLogged($GLOBAL_DATA) == false) {throw new Exception("Right error");}

try {
  switch($action) {
  case "reset":
    $stepsInit = isset($_GET["board_size"]) ? ((int) $_GET["board_size"])-1 : $CONFIG["game"]["stepsInit"];
    if($stepsInit > $CONFIG["game"]["stepsInit"]) $stepsInit = $CONFIG["game"]["stepsInit"]; // force a limitation for security reason
    $handicapInit = isset($_GET["handicap"])   ? ((int) $_GET["handicap"]) : $CONFIG["game"]["handicapInit"];
    $komiInit     = isset($_GET["komi"])       ? ((float) $_GET["komi"])   : $CONFIG["game"]["komiInit"];
    db_backup($CONFIG["db"]["db_path"], $CONFIG["db"]["dbcount_path"], $CONFIG["backups"]);
    db_clean($CONFIG["db"]["db_path"], $CONFIG["db"]["dbcount_path"]);
    $game = db_reset($CONFIG["db"]["db_path"], $handicapInit, $komiInit, $stepsInit);
    break;

  case "cancelpass":
    $p_n = (int) html_getParameter("n");
    $game = db_read($CONFIG["db"]["db_path"], $CONFIG["game"]["handicapInit"], $CONFIG["game"]["komiInit"], $CONFIG["game"]["stepsInit"], -1);
    if($p_n == $game["nbactions"]) { // ignore redo links
      db_cancelpass($CONFIG["db"]["db_path"], $game);
    }
    break;
    
  case "pass":
    $p_n = (int) html_getParameter("n");
    $game = db_read($CONFIG["db"]["db_path"], $CONFIG["game"]["handicapInit"], $CONFIG["game"]["komiInit"], $CONFIG["game"]["stepsInit"], -1);
    if($p_n == $game["nbactions"]) { // ignore redo links
      db_pass($CONFIG["db"]["db_path"], $game);
    }
    break;
    
  case "comment":
    $sender = html_getParameter("sender");
    if($sender == "") {
      echo "Please fill your name";
    } else {
      $comment = html_getParameter("comment");
      if($comment == "") {
	echo "Please fill a comment";
      } else {
	$type = html_getParameter("type");
	if($type == "") {
	  echo "Please specify the type of comment";
	} else {
	  db_comment($CONFIG["db"]["db_path"], html_getParameter("type"), html_getParameter("sender"), html_getParameter("comment"));
	}
      }
    }
    break;
    
  case "count":
    require_once($GLOBAL_BASE."/system/include/dbcount.php");
    $game = db_read($CONFIG["db"]["db_path"], $CONFIG["game"]["handicapInit"], $CONFIG["game"]["komiInit"], $CONFIG["game"]["stepsInit"], -1);
    $dbcount = dbcount_read($CONFIG["db"]["dbcount_path"], $game["board"]);
    dbcount_action($CONFIG["db"]["dbcount_path"], $dbcount, $game,
		   (int) html_getParameter("x"), (int) html_getParameter("y"));
    break;
    
  case "resetcount":
    require_once($GLOBAL_BASE."/system/include/dbcount.php");
    dbcount_reset($CONFIG["db"]["dbcount_path"]);
    break;
  }

  echo "success";

} catch (Exception $e) {
  echo $e->getMessage();
}

?>
