
function readStonesListsToMatrix(steps, lists) {
 vstones = new Array()

 for(i=0; i<=steps; i++) {
  vstones[i] = new Array()
  for(j=0; j<=steps; j++) {
   vstones[i][j] = 'X'
  }
 }

 $.each(lists, function(color, stones) {
  $.each(stones, function(n, coords) {
   vstones[coords[0]][coords[1]] = color
  });
 })

 return vstones
}

function readStonesPointsListsToMatrix(steps, vstones, tdeads, tpoints) {
 vpoints = new Array()

 // init
 for(i=0; i<=steps; i++) {
  vpoints[i] = new Array()
  for(j=0; j<=steps; j++) {
   vpoints[i][j] = 'X'
  }
 }

 // deads
 $.each(tdeads, function(color, stones) {
  $.each(stones, function(n, coords) {
   vpoints[coords[0]][coords[1]] = 'D' + vstones[coords[0]][coords[1]]
  })
 })

 // points
 $.each(tpoints, function(color, stones) {
  $.each(stones, function(n, coords) {
   vpoints[coords[0]][coords[1]] = 'P' + color
  })
 })

 return vpoints
}

function game_loadBoard(f_success, f_complete, go_url, go_room, go_file, mode) {
 url_extra = ""
 ieversion = getInternetExplorerVersion()
 if(ieversion >= 0 && ieversion <= 6) {
  url_extra = "&png=false"
 }

 $.getJSON(go_url+"?room="+go_room+"&file="+go_file+"&mode="+mode+url_extra, function(data) {

  // init cause can be empty
  vpoints = new Array()
  vscores = new Array()

  // read board & stones & scores
  $.each(data, function(obj, values) {
   switch(obj) {

   case "access"  : access   = values ; break;
   case "graphics": graphics = values ; break;
   case "game"    : game     = values ; break;
   case "stones":
    vstones = readStonesListsToMatrix(game["steps"], values)
    break;
   case "comments": vcomments = values; break;

   case "counts":
    $.each(values, function(subpoint_type, subpoint_values) {
     switch(subpoint_type) {
      case "deads":
       tdeads = subpoint_values
      break;
      case "points":
       tpoints = subpoint_values
      break;
      case "scores":
       vscores = subpoint_values
      break;
     }
    })
    vpoints = readStonesPointsListsToMatrix(game["steps"], vstones, tdeads, tpoints)
   break;
   }
  })

  f_success(access, game, vstones, vpoints, vscores, vcomments, graphics)
 }).complete(f_complete)
}

function history_get_color(n, handicap) {
 if(n <= handicap) {
  return 'B'
 }
 return ((n-handicap) %2 == 0 ? 'B' : 'W')
}

function history_action_forward_1(game, stones, ghistory, access, graphics, stones_id_prefix) {
 if(ghistory.current+1 >= game["nb_played_stones"] + game["nb_passed_stones"]) {
  return
 }

 newcurrent = ghistory.current+1
 ghistory.current = newcurrent

 // previous
 if(newcurrent-1 >= 0) {
  move = ghistory.moves[newcurrent-1]
  if(move[0] != -1 && move[1] != -1) {
   color = history_get_color(newcurrent-1, game["handicap"])
   objimg = document.getElementById(html_getId_img(stones_id_prefix, move[0], move[1], 1))
   objimg.src = color == 'B' ? graphics["img_black"] : graphics["img_white"]
   objimg = document.getElementById(html_getId_img(stones_id_prefix, move[0], move[1], 2))
   objimg.src = graphics["img_shadow"]
  }
 }

 // last
 move = ghistory.moves[newcurrent]
 if(move[0] != -1 && move[1] != -1) {
  color = history_get_color(newcurrent, game["handicap"])
  objimg  = document.getElementById(html_getId_img(stones_id_prefix, move[0], move[1], 1))
  objimg.src = color == 'B' ? graphics["img_lblack"] : graphics["img_lwhite"]
  objimg = document.getElementById(html_getId_img(stones_id_prefix, move[0], move[1], 2))
  objimg.src = graphics["img_shadow"]
 }

 // played
 if(move[0] != -1 && move[1] != -1) {
  ghistory.nb_played_stones++
 }

 if(color == "B") {
  ghistory.prison_white += ghistory.removed[newcurrent].length
 } else {
  ghistory.prison_black += ghistory.removed[newcurrent].length
 }
 $.each(ghistory.removed[newcurrent], function(n, coords) {
  objimg  = document.getElementById(html_getId_img(stones_id_prefix, coords[0], coords[1], 1))
  objimg.src = graphics["img_none"]
  objimg  = document.getElementById(html_getId_img(stones_id_prefix, coords[0], coords[1], 2))
  objimg.src = graphics["img_none"]
 })
}

function history_action_backward_1(game, ghistory, graphics, stones_id_prefix) {
 if(ghistory.current == -1) {
  return
 }

 newcurrent = ghistory.current-1
 ghistory.current = newcurrent

 // remove last move
 move = ghistory.moves[newcurrent+1]
 if(move[0] != -1 && move[1] != -1) {
  color = history_get_color(newcurrent+1, game["handicap"])
  objimg  = document.getElementById(html_getId_img(stones_id_prefix, move[0], move[1], 1))
  objimg.src = graphics["img_none"]
  objimg  = document.getElementById(html_getId_img(stones_id_prefix, move[0], move[1], 2))
  objimg.src = graphics["img_none"]
 }

 // update played
 if(move[0] != -1 && move[1] != -1) {
  ghistory.nb_played_stones--
 }

 // update prisonniers
 if(color == "B") {
  ghistory.prison_white -= ghistory.removed[newcurrent+1].length
 } else {
  ghistory.prison_black -= ghistory.removed[newcurrent+1].length
 }

 // reput dead stones
 $.each(ghistory.removed[newcurrent+1], function(n, coords) {
  objimg  = document.getElementById(html_getId_img(stones_id_prefix, coords[0], coords[1], 1))
  objimg.src = color == 'B' ? graphics["img_white"] : graphics["img_black"]
  objimg  = document.getElementById(html_getId_img(stones_id_prefix, coords[0], coords[1], 2))
  objimg.src = graphics["img_shadow"]
 })

 // reput previous move (after reput dead stones in case the last move is in the dead stones)
 if(newcurrent >= 0) {
  move = ghistory.moves[newcurrent]
  if(move[0] != -1 && move[1] != -1) {
   color = history_get_color(newcurrent, game["handicap"])
   objimg  = document.getElementById(html_getId_img(stones_id_prefix, move[0], move[1], 1))
   objimg.src = color == 'B' ? graphics["img_lblack"] : graphics["img_lwhite"]
   objimg  = document.getElementById(html_getId_img(stones_id_prefix, move[0], move[1], 2))
   objimg.src = graphics["img_shadow"]
  }
 }

}

function history_update(go_room, go_file, game, ghistory, f_success) {
 if(ghistory.status != -1) {
  f_success()
  return
 }

 $.getJSON(game["history_url"]+"?room="+go_room+"&file="+go_file+"&action_num="+game["action_num"],
 function(data) {
  ghistory.moves      = new Array()
  ghistory.timestamps = new Array()
  ghistory.removed = new Array()
  ghistory.clock_black = new Array()
  ghistory.clock_white = new Array()

  $.each(data, function(obj, val) {
   switch(obj) {
   case "key":
    ghistory.key = val
    break;
   case "status":
    ghistory.status = val
    break;
   case "stones":
    $.each(val, function(n, nvalues) {
     $.each(nvalues, function(obj, objval) {
      switch(obj) {
       case "move":
        ghistory.moves[n] = objval
        break;
       case "timestamp":
         ghistory.timestamps[n] = objval
        break;
       case "removed_stones":
        ghistory.removed[n] = objval
       break;
       case "clock_black":
        ghistory.clock_black[n] = objval
       break;
       case "clock_white":
        ghistory.clock_white[n] = objval
       break;
      }
     })
    })
    break;
   }
  })

  if(game["key"] == ghistory["key"]) {
   f_success()
  }
 })
 .error(function() {
  alert(_("Unable to load history"))
 })
}

function history_action_forward(go_room, go_file, game, ghistory, stones, access, board_infos_id, graphics, stones_id_prefix, f_play, f_next_color, n, f_success) {
 if(ghistory.status == -1) {
  f_success()
  return
 }

 history_update(go_room, go_file, game, ghistory,
  function() {
   for(i=0; i<n; i++) {
    history_action_forward_1(game, stones, ghistory, access, graphics, stones_id_prefix)
   }

   if(ghistory.current+1 == game["nb_played_stones"] + game["nb_passed_stones"]) {
    ghistory.status = -1
    html_updateBoard_play_events(game["steps"], false, stones, stones, access, game["last_move"], game["last_move"], stones_id_prefix, graphics, f_play, f_next_color)
    html_loadInfosBar_play(document.getElementById(board_infos_id), game)
   } else {
     html_loadInfosBar_history(document.getElementById(board_infos_id), ghistory, game["handicap"])
   }

   f_success()
  }
 )
}

function history_action_forwardall(go_room, go_file, game, ghistory, stones, access, board_infos_id, graphics, stones_id_prefix, f_play, f_next_color, f_success) {
 if(ghistory.status == -1) {
  f_success()
  return
 }

 if(ghistory.current+1 >= game["nb_played_stones"] + game["nb_passed_stones"]) {
  f_success()
  return
 }

 history_action_forward(go_room, go_file, game, ghistory, stones, access, board_infos_id, graphics, stones_id_prefix, f_play, f_next_color, game["nb_played_stones"] + game["nb_passed_stones"] - ghistory.current - 1, f_success)
}

function history_action_backwardall(go_room, go_file, game, ghistory, board_infos_id, graphics, stones_id_prefix, f_success) {
 if(ghistory.status == -1) {
  history_action_backward(go_room, go_file, game, ghistory, board_infos_id, graphics, stones_id_prefix, game["nb_played_stones"] + game["nb_passed_stones"], f_success)
 } else {
  history_action_backward(go_room, go_file, game, ghistory, board_infos_id, graphics, stones_id_prefix, ghistory.current+1, f_success)
 }
}

function history_action_backward(go_room, go_file, game, ghistory, board_infos_id, graphics, stones_id_prefix, n, f_success) {
 previous_history_status = ghistory.status

 history_update(go_room, go_file, game, ghistory,
  function() {
   if(ghistory.moves.length == 0) {
     ghistory.status = -1 // nothing happend
     return
   }
   if(previous_history_status == -1) { // first backward
    ghistory.current = game["nb_played_stones"] + game["nb_passed_stones"] -1
    ghistory.nb_played_stones = game["nb_played_stones"]
    ghistory.prison_black = game["prison_black"]
    ghistory.prison_white = game["prison_white"]
    html_resetBoard_play_events(game["steps"], stones_id_prefix)
   }

   for(i=0; i<n; i++) {
    history_action_backward_1(game, ghistory, graphics, stones_id_prefix)
   }
   html_loadInfosBar_history(document.getElementById(board_infos_id), ghistory, game["handicap"])

   f_success()
  }
 )
}
