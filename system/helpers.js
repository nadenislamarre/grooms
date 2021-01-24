function showHideId(obj_button, id_text, captionOn, captionOff) {
  var layer = document.getElementById(id_text)
  if (layer != null) {
    if(layer.style.display == 'block') {
      layer.style.display = 'none'
      obj_button.value = captionOn
      return false
    } else {
      layer.style.display = 'block'
      obj_button.value = captionOff
      return true
    }
  }
}

function getInternetExplorerVersion()
// Returns the version of Windows Internet Explorer or a -1
// (indicating the use of another browser).
{
   var rv = -1; // Return value assumes failure.
   if (navigator.appName == 'Microsoft Internet Explorer')
   {
      var ua = navigator.userAgent;
      var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
      if (re.exec(ua) != null)
         rv = parseFloat( RegExp.$1 );
   }
   return rv;
}

function do_nothing() {
}

function seconds2str(s) {
 rest = s

 // seconds
 ds = rest%60
 rest -= ds
 rest /= 60
 if(ds < 10) { ds = "0" + ds}

 // minutes
 dm = rest%60
 rest -= dm
 rest /= 60
 if(dm < 10) { dm = "0" + dm}

 // hours
 dh = rest%24
 rest -= dh
 rest /= 24
 if(dh < 10) { dh = "0" + dh}

 if(rest == 0) {
  return dh+":"+dm+":"+ds
 }

 // days
 dd = Gettext.strargs(gt.ngettext("%1 day", "%1 days", rest), rest)
 return dd+", "+dh+":"+dm+":"+ds
}
