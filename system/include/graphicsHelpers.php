<?php
function getStepWidth($max_image_width, $max_step_width, $steps, $offset) {
  $v = $max_image_width / ($steps+$offset*2);
  return $v < $max_step_width ? $v : $max_step_width;
}
?>