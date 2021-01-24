<?php
$GLOBAL_BASE = "..";
require_once($GLOBAL_BASE."/system/system.php");
session_set_cookie_params($SYSTEM["session_duration"]);
session_start();

require_once($GLOBAL_BASE."/system/include/helpers.php");
setGlobalData($GLOBAL_BASE);

require_once($GLOBAL_BASE."/system/config.php");

nocacheHeader();

$mode = html_getParameter("mode");

switch($mode) {
 case "play": // live status
   echo getFileStatus($CONFIG["db"]["db_path"]);
   break;

 case "count": // live count status
   // concatenate the 2 status to update when the game updates too
   echo getFileStatus($CONFIG["db"]["db_path"])."x".getFileStatus($CONFIG["db"]["dbcount_path"]);
   break;
}
?>