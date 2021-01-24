<?php

// locks shoud be added when writing/reading fs, but it has almost no chance to happend :
// - players play one after the others
// - fwrite are very very small

function fs_file($path) {
  global $SYSTEM;
  if($SYSTEM["nfs"]) {
    $tmp_link = dirname($path)."/nfs.".$SYSTEM["nfs_unique_key"].".".basename($path);
    if(link($path, $tmp_link) === false) {
      return false;
    }
    $res = file($tmp_link, FILE_IGNORE_NEW_LINES);
    unlink($tmp_link);
    return $res;
  } else {
    return file($path, FILE_IGNORE_NEW_LINES);
  }
}

function fs_lstat($path) {
  global $SYSTEM;
  if($SYSTEM["nfs"]) {
    $tmp_link = dirname($path)."/nfs.".$SYSTEM["nfs_unique_key"].".".basename($path);
    if(link($path, $tmp_link) === false) {
      return false;
    }
    $res = lstat($path);
    unlink($tmp_link);
    return $res;
  } else {
    return lstat($path);
  }
}

// mode = w or a
// $path can exist or not
function fs_fwrite($path, $str, $mode) {
  if( ($fd = fopen($path, $mode)) === false) {
    return false;
  }

  if(fwrite($fd, $str) === false) {
    return false;
  }

  if(fclose($fd) === false) {
    return false;
  }
}

?>
