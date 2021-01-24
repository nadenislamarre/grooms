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

nocacheHeader();

$action_num = html_getParameter("action_num", "-1");

$dbfile = choose_db_file(html_getParameter("file"),
			 $CONFIG["db"]["db_path"], $CONFIG["backups"]["db_backup_directory"],
			 $CONFIG["backups"]["db_backup_prefix"]);

$game = db_read($dbfile, $CONFIG["game"]["handicapInit"], $CONFIG["game"]["komiInit"], $CONFIG["game"]["stepsInit"], $action_num, $all_moves);

echo "{\n";
echo "\"key\": \"".$game["partykey"]."\",\n";
echo "\"status\": \"".getFileStatus($dbfile)."\",\n";

echo "\"stones\": {\n";

$first = true;
foreach($all_moves as $n => $actions) {
  $move = $actions["move"];

  if($first) {
    $first = false;
  } else {
    echo ",\n";
  }
  echo "\"".$n."\": {\n";
  if($CONFIG["show_times_if_not_connected"] || session_isLogged($GLOBAL_DATA)) {
    echo " \"timestamp\": \"".$move[0]."\",\n";
  } else {
    echo " \"timestamp\": \"\",\n";
  }
  if(count($move) == 1) {
    echo " \"move\": [-1, -1],\n";
  } else {
    echo " \"move\": [".$move[1].", ".$move[2]."],\n";
  }
  echo " \"removed_stones\": [";
  $first_removed = true;
  foreach($actions["kill"] AS $removed) {
    if($first_removed) {
      $first_removed = false;
    } else {
      echo ", ";
    }
    echo "[".$removed[0].", ".$removed[1]."]";
  }
  echo "],\n";
  echo " \"clock_black\": ".$actions["clock"]["B"].",\n";
  echo " \"clock_white\": ".$actions["clock"]["W"]."\n";
  echo "} ";
}
echo "\n}\n";
echo "}\n";

?>
