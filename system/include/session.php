<?php

function session_isLogged($data) {
  if(isset($_SESSION["registered"][$data]) == false) {
    return false;
  }
  return ($_SESSION["registered"][$data] === true);
}

function session_login($data, $password) {
  global $CONFIG;

  if($CONFIG["password"] == md5($password)) {
    $_SESSION["registered"][$data] = true;
  } else {
    throw new Exception("Invalid password");
  }
}

function session_logout($data) {
  unset($_SESSION["registered"][$data]);
}
?>
