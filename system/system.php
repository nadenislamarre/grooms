<?php
$SYSTEM["session_duration"] = 3600*24*7; // one week
$SYSTEM["nfs"] = false; // set to true if the server filesystem is shared
$SYSTEM["nfs_unique_key"] = $_SERVER["REMOTE_ADDR"]."_".gmmktime()."_".rand(1,1000000);
?>
