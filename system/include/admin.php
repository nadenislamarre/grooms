<?php

require_once($GLOBAL_BASE."/system/include/fs.php");

function admin_getRooms($parent_dir) {
  $res = array();

  if ($dh = opendir($parent_dir)) {
    while (($file = readdir($dh)) !== false) {
      if($file != "." && $file != ".." && $file != "system") {
	if(is_dir($file)) {
	  $res[count($res)] = $file;
	}
      }
    }
    closedir($dh);
  }
  return $res;
}

function admin_create_room($parent_dir, $room, $password) {
  // check room name
  checkRoomName($parent_dir, $room, false);

  // check existance
  if(room_exists($parent_dir, $room)) {
    throw new Exception("Room already exists");
  }

  // create the room directory
  $room_directory = $parent_dir."/".$room;
  if(mkdir($room_directory) == false) {
    throw new Exception("Unable to create the room (mkdir failed)");
  }

  try {
    // create the config file
    fs_fwrite($room_directory."/config.php", "<?php\n\$CONFIG[\"password\"] = \"".md5($password)."\";\n?>\n", "w");
    // create the index file
    fs_fwrite($room_directory."/index.php", "<?php\nrequire_once(\"../system/home.php\");\nhome(\"..\", basename(dirname(\$_SERVER[\"SCRIPT_NAME\"])));\n?>\n", "w");
  } catch(Exception $e) {
    throw new Exception("Unable to create the room (config files creation failed)");
  }
}

function room_lastUpdateDate($base, $room, $dbfile) {
  $rdir = $base."/".$room;
  $dbpath = $rdir."/".$dbfile;
  
  // db
  if(file_exists($dbpath)) {
    if( ($stats = stat($dbpath)) === FALSE) { // no need of fs_stat -- can read the cache
      throw new Exception("");
    }
    return $stats[9];
  }

  // still no db
  if( ($stats = stat($rdir)) === FALSE) { // no need of fs_stat -- can read the cache
    throw new Exception("");
  }
  return $stats[9];
}

function room_lastUpdated($base, $dbfile) {
  if( ($dh = opendir($base)) === false) {
    throw new Exception("");
  }
  
  $tmp = array();
  while (($file = readdir($dh)) !== false) {
    if(is_dir($file)) {
      if($file != "." && $file != ".." && $file != "system") {
        try {
          $stamp = room_lastUpdateDate($base, $file, $dbfile);
          $tmp[$stamp."_".$file] = array("room" => $file, "stamp" => $stamp);
        } catch(Exception $e) {
        }
      }
    }
  }
  closedir($dh);
  krsort($tmp);
  
  $res = array();
  $n = 0;
  foreach($tmp AS $key => $vals) {
    $res[$n++] = $vals;
  }
  return $res;
}

function admin_configure_room($parent_dir, $room, $password, $password_md5, $show_times_if_not_connected, $show_comments_if_not_connected) {
  $room_directory = $parent_dir."/".$room;

  $str = "";

  // password
  if($password != "") {
    $str .= "<?php\n\$CONFIG[\"password\"] = \"".md5($password)."\";\n";
  } else {
    $str .= "<?php\n\$CONFIG[\"password\"] = \"".$password_md5."\";\n";
  }

  // show_times_if_not_connected
  $str .= "\$CONFIG[\"show_times_if_not_connected\"] = ".($show_times_if_not_connected ? "true" :  "false").";\n";

  // show_comments_if_not_connected
  $str .= "\$CONFIG[\"show_comments_if_not_connected\"] = ".($show_comments_if_not_connected ? "true" :  "false").";\n";

  $str .= "?>\n";
  fs_fwrite($room_directory."/config.php", $str, "w");
}

?>
