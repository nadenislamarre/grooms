<?php
global $GLOBAL_LOCALES;
$GLOBAL_LOCALES = array(
 "en" => "English",
 "fr" => "Français"
);

function locale_set($lang) {
  global $GLOBAL_LOCALES;

  if(isset($GLOBAL_LOCALES[$lang])) {
    $_SESSION["language"] = $lang;
    return;
  }

  $_SESSION["language"] = "en";
}

function locale_get() {
  if(isset($_SESSION["language"])) {
    return $_SESSION["language"];
  }
  return "en";
}

function locale_getFavoriteLanguage($accept_language) {
  $langs = explode(",", $accept_language);
  $favorite_lang_vals = explode(";", $langs[0]);
  $favorite_lang_vals = explode("-", $favorite_lang_vals[0]);
  $favorite_lang_vals = explode("_", $favorite_lang_vals[0]);
  $favorite_lang = $favorite_lang_vals[0];
  return $favorite_lang;
}

function locale_getFavoriteAvailableLlanguage($accept_language) {
  global $GLOBAL_LOCALES;

  $lang = locale_getFavoriteLanguage($accept_language);
  if(isset($GLOBAL_LOCALES[$lang])) {
    return $lang;
  }
  return "en";
}

function locale_head($parent, $accept_language) {
  // choose and set the language
  if(isset($_SESSION["language"])) {
    $lang = $_SESSION["language"];
  } else {
    $lang = locale_getFavoriteAvailableLlanguage($accept_language);
    locale_set($lang); // save it in the session
  }

  // include associated js
  if($lang != "en") { // include the language
    echo "<script language=\"javascript\" src=\"".$parent."/system/LC_MESSAGES/".$lang."/grooms.js\"></script>\n";
  }
  echo "<script language=\"javascript\" src=\"".$parent."/system/Gettext.js\"></script>\n";
  echo "<script type=\"text/javascript\">\n";
  if($lang == "en") {
    echo "var json_locale_data = null // english, no translation\n";
  }
  echo "var gt = new Gettext({\"domain\" : \"grooms\", \"locale_data\" : json_locale_data})\n";
  echo "function _ (msgid) { return gt.gettext(msgid); }\n";
  echo "</script>\n";
}

function locale_changeAction($lang) {
  if($lang != "") {
    locale_set($lang);
  }
}

function locale_bar($base, $script, $params = "", $suggest_translation = false, $accept_language = "") {
  global $GLOBAL_LOCALES;

  echo "    <ul id=\"nls\">\n";
  $lang = locale_get();

  foreach($GLOBAL_LOCALES AS $available_nls => $nls_word) {
    if($lang != $available_nls) {
      echo "<li><a href=\"";
      if($params == "") {
	echo $script."?";
      } else {
	echo $script."?".$params."&";
      }
      echo "lang=".$available_nls."\">".$nls_word."</a></li>\n";
    }
  }
  if($suggest_translation) {
    $mylang = locale_getFavoriteLanguage($accept_language);
    if($mylang != "" && isset($GLOBAL_LOCALES[$mylang]) == false) {
      echo "<a href=\"".$base."/system/faq.php\">translate in ".$mylang."</a>";
    }
  }
  echo "    </ul>\n";
}
?>
