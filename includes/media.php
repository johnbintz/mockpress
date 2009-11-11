<?php

function _set_image_downsize_result($id, $size, $result) {
  global $wp_test_expectations;
  $wp_test_expectations['image_downsize']["${id}-${size}"] = $result;
}

function image_downsize($id, $size) {
  global $wp_test_expectations;
  $key = "${id}-${size}";
  if (isset($wp_test_expectations['image_downsize'][$key])) {
    return $wp_test_expectations['image_downsize'][$key];
  }
  return false;
}