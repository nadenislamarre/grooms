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
require_once($GLOBAL_BASE."/system/include/rss.php");

nocacheHeader();

if(session_isLogged($GLOBAL_DATA) == false) {throw new Exception("Right error");}

$p_n = (int) html_getParameter("n");
$p_x = (int) html_getParameter("x");
$p_y = (int) html_getParameter("y");

try {
  $game = db_read($CONFIG["db"]["db_path"], $CONFIG["game"]["handicapInit"], $CONFIG["game"]["komiInit"], $CONFIG["game"]["stepsInit"], -1);

  if($p_n == $game["nbactions"]) { // ignore redo links
    try {
      db_play($CONFIG["db"]["db_path"], $game, $p_x, $p_y);
      updateRss($CONFIG["rss"]["url"], $GLOBAL_DATA, $game, $CONFIG["rss"]["file_all"]);
      if($game["next_is_black"]) {
	updateRss($CONFIG["rss"]["url"], $GLOBAL_DATA, $game, $CONFIG["rss"]["file_black"]);
      } else {
	updateRss($CONFIG["rss"]["url"], $GLOBAL_DATA, $game, $CONFIG["rss"]["file_white"]);
      }
    } catch(InvalidMoveException $e) {
      // ok, just do nothing
    } catch (Exception $e) {
      throw $e;
    }
  }

  echo "success";

} catch (Exception $e) {
  echo $e->getMessage();
}

?>
