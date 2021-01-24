<?php
$GLOBAL_BASE = "..";
require_once($GLOBAL_BASE."/system/system.php");
session_set_cookie_params($SYSTEM["session_duration"]);
session_start();

require_once($GLOBAL_BASE."/system/include/helpers.php");
setGlobalData($GLOBAL_BASE);

require_once($GLOBAL_BASE."/system/config.php");
require_once($GLOBAL_BASE."/system/include/db.php");

nocacheHeader();

$entries = db_getOldGames($CONFIG["backups"]["db_backup_directory"], $CONFIG["backups"]["db_backup_prefix"]);

echo "{\n";
$n = 1;
foreach($entries AS $entry => $entry_val) {
  if($n > 1) {
    echo ",\n";
  }
  echo " \"".$n."\": \"".$entry."\"";
  $n++;
}
echo "\n}\n";

?>
