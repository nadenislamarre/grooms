<?php

$GLOBAL_BASE = "..";
require_once($GLOBAL_BASE."/system/system.php");
session_set_cookie_params($SYSTEM["session_duration"]);
session_start();

require_once($GLOBAL_BASE."/system/include/helpers.php");
setGlobalData($GLOBAL_BASE);

require_once($GLOBAL_BASE."/system/config.php");
require_once($GLOBAL_BASE."/system/include/locale.php");

nocacheHeader();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>Go game checks</title>
<?php locale_head($GLOBAL_BASE, $_SERVER["HTTP_ACCEPT_LANGUAGE"]); ?>
</head>
<body>
<style>
.check_failed {
 background-color: red;
}
</style>
<?php

function table_checkWritable_directory($directory, $title) {
  echo "<tr><td>".$title."</td>";
  if(is_writable($directory)) {
    echo "<td>".js_w("_(\"OK\")")."</td>";
  } else {
    echo "<td class=\"check_failed\">".js_w("_(\"FAILED\")")."</td>";
  }
  echo "</tr>\n";
}

function table_checkWritableOrMissing_file($file, $title) {
  echo "<tr><td>".$title."</td>";
  if(file_exists($file)) {
    if(is_writable($file)) {
      echo "<td>".js_w("_(\"OK\")")."</td>";
    } else {
      echo "<td class=\"check_failed\">".js_w("_(\"FAILED\")")."</td>";
    }
  } else {
    echo "<td>".js_w("_(\"OK\")")."</td>";
  }
  echo "</tr>\n";
}

function table_displayInfo($value, $title) {
  echo "<tr><td>".$title."</td>";
  echo "<td>".$value."</td>";
  echo "</tr>\n";
}

echo "<table>\n";
echo "<tr><th>".js_w("_(\"Check\")")."</th><th>".js_w("_(\"Status\")")."</th></tr>\n";

// check dir is writable
table_checkWritable_directory($GLOBAL_BASE."/".$GLOBAL_DATA, js_w("_(\"game directory is writable\")"));

// check go.db is writable
table_checkWritableOrMissing_file($CONFIG["db"]["db_path"], js_w("_(\"db_path is writable\")"));

// check gocount.db is writable
table_checkWritableOrMissing_file($CONFIG["db"]["dbcount_path"], js_w("_(\"dbcount_path is writable\")"));

// check backups dir is writable
table_checkWritable_directory($CONFIG["backups"]["db_backup_directory"], js_w("_(\"backup directory is writable\")"));

// display nfs information
table_displayInfo($SYSTEM["nfs"] ? js_w("_(\"enabled\")") : js_w("_(\"disabled\")"), js_w("_(\"shared network file system status\")"));
table_displayInfo($SYSTEM["nfs_unique_key"], js_w("_(\"shared network file system lock key\")"));

echo "</table>\n";

?>
</body>
</html>
