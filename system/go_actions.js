function comment_verify(sender, type, comment) {
  if(sender == "") {
    alert(_("Please fill your name"))
    return false
  }
  if(comment == "") {
    alert(_("Please fill a comment"))
    return false
  }
  return true
}

function board_action_comment(action_url, obj_comment, areas) {
 if(comment_verify(document.getElementById("comment_sender").value,
                   document.getElementById("comment_type").value,
		   document.getElementById("comment_comment").value)) {
  g_board_action_postStandard(action_url, "comment_form")
  html_preloadComments(obj_comment, document.getElementById("comment_type").value, document.getElementById("comment_sender").value, document.getElementById("comment_comment").value)

  showHideId(document.getElementById('showHide_comment'), areas.comment.id, _('Comment'), _('Hide commenting'))
  layer = document.getElementById(areas.comment.id)
  areas.comment.visible = layer.style.display == 'block'
  
  // reset fields
  document.getElementById("comment_comment").value = ""
  document.getElementById("comment_type").value = ""
 }
}
