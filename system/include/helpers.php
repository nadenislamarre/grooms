<?php

require_once($GLOBAL_BASE."/system/include/fs.php");

function move2string($x, $y) {
  return ($x+1).chr(ord('A') + $y);
}

function nowOwn() {
  return gmdate("Ymd-H\hi.s");
}

function txt2htmlOneLine($txt) {
  $txt = str_replace(" ", "&nbsp;", str_replace("<", "&lt;", str_replace(">", "&gt;", str_replace("\"", "&quot;", str_replace("&", "&amp;", $txt)))));
  return str_replace("\r", "", str_replace("\n", "<br/>", $txt));
}

function html_getParameter($parameter_name, $default = "") {
  global $_GET, $_POST;

  if(isset($_GET[$parameter_name]) == false || $_GET[$parameter_name] == "") {
    if(isset($_POST[$parameter_name])) {
      return $_POST[$parameter_name];
    }
    return $default;
  }

  return $_GET[$parameter_name];
}

function nocacheHeader() {
  header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");              // Date in the past
  header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
  header ("Cache-Control: no-cache, must-revalidate");            // HTTP/1.1
  header ("Pragma: no-cache");                                    // HTTP/1.0
}

function getFileStatus($path) {
  if(file_exists($path) === false) {
    return "0";
  }

  if( ($stat=fs_lstat($path)) === false) {
    return "0";
  } else {
    return $stat["mtime"];
  }
}

function time2Ts($str) {
  // "20120525-22h00.44"
  return gmmktime(substr($str, 9, 2), substr($str, 12, 2), substr($str, 15, 2), // hour/minutes/seconds
		  substr($str, 4, 2), substr($str, 6, 2), substr($str, 0, 4));
}

function room_exists($base, $data) {
  return is_dir($base."/".$data);
}

function checkRoomName($base, $data, $must_exist = true) {
  if($data == "") {
    throw new Exception("Invalid room (empty name)");
  }

  // check the room
  if($data == "system") {
    throw new Exception("Invalid room (unauthorized name)");
  }

  if($must_exist) {
    if(room_exists($base, $data) == false) {
      throw new Exception("Invalid room (unexisting room)");
    }
  }

  // check for invalid keys
  if (preg_match("/^[a-zA-Z0-9]*$/", $data) !== 1) {
    throw new Exception("Invalid room (invalid characters found (A-Z a-z 0-9 allowed only))");
  }

  // check for length
  if(strlen($data) > 16) {
    throw new Exception("Invalid room (length > 16)");
  }
}

function setGlobalData($base) {
  $data = html_getParameter("room");

  checkRoomName($base, $data);

  global $GLOBAL_DATA;
  $GLOBAL_DATA = $data;
}

function delta_time_html($time1, $time2, $light = false) {
  $n = $time2 - $time1;
  $seconds = $n % 60;
  $n = ($n - $seconds) / 60;
  $minutes = $n % 60;
  $n = ($n - $minutes) / 60;
  $hours = $n % 24;
  $n = ($n - $hours) / 24;
  $days = $n;
  
  if($light && $days > 1) { // light
    return js_w("Gettext.strargs(gt.ngettext(\"%1 day ago\", \"%1 days ago\", ".$days."), \"".$days."\")");
  }
  
  if($light && ($days > 0 || $hours > 1)) { // light
    if($days > 0) {
      return js("x_days  = Gettext.strargs(gt.ngettext(\"%1 day\", \"%1 days\", ".$days."), \"".$days."\")\n".
		"x_hours = Gettext.strargs(gt.ngettext(\"%1 hour\", \"%1 hours\", ".$hours."), \"".$hours."\")\n".
		"document.write(Gettext.strargs(_(\"%1, %2 ago\"), [x_days, x_hours]))\n"
	       );
    } else {
      return js_w("Gettext.strargs(gt.ngettext(\"%1 hour ago\", \"%1 hours ago\", ".$hours."), \"".$hours."\")");
    }
  }

  $res = "";
  if($days > 0) {
    return js("x_days    = Gettext.strargs(gt.ngettext(\"%1 day\", \"%1 days\", ".$days."), \"".$days."\")\n".
              "x_hours   = Gettext.strargs(gt.ngettext(\"%1 hour\", \"%1 hours\", ".$hours."), \"".$hours."\")\n".
              "x_minutes = Gettext.strargs(gt.ngettext(\"%1 minute\", \"%1 minutes\", ".$minutes."), \"".$minutes."\")\n".
  	      "document.write(Gettext.strargs(_(\"%1, %2, %3 ago\"), [x_days, x_hours, x_minutes]))\n"
	     );
  }
  if($hours > 0) {
   return js("x_hours   = Gettext.strargs(gt.ngettext(\"%1 hour\", \"%1 hours\", ".$hours."), \"".$hours."\")\n".
             "x_minutes = Gettext.strargs(gt.ngettext(\"%1 minute\", \"%1 minutes\", ".$minutes."), \"".$minutes."\")\n".
	     "document.write(Gettext.strargs(_(\"%1, %2 ago\"), [x_hours, x_minutes]))\n"
	    );
  }

  return js_w("Gettext.strargs(gt.ngettext(\"%1 minute ago\", \"%1 minutes ago\", ".$minutes."), \"".$minutes."\")");
}

function js_w($txt) {
  return "<script type=\"text/javascript\">document.write(".$txt.")</script>";
}

function js($txt) {
  return "<script type=\"text/javascript\">".$txt."</script>";
}

?>
