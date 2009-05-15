<?php

/**
 * Simulate enough of WordPress to test plugins and themes using PHPUnit, SimpleTest, or any other PHP unit testing framework.
 * @author John Bintz
 */

$wp_test_expectations = array();

/**
 * Reset the WordPress test expectations.
 */
function _reset_wp() {
	global $wp_test_expectations;
	$wp_test_expectations = array(
    'options' => array(),
    'categories' => array(),
    'post_categories' => array(),
    'get_posts' => array(),
    'pages' => array(),
    'actions' => array(),
    'filters' => array(),
    'posts' => array(),
    'post_meta' => array()
  );
}

/* WordPress Test Doubles */

function get_option($key) {
  global $wp_test_expectations;
  if (isset($wp_test_expectations['options'][$key])) {
    return $wp_test_expectations['options'][$key];
  } else {
    return false;
  }
}
                
function update_option($key, $value) {
  global $wp_test_expectations;
  if ($wp_test_expectations['options'][$key] == $value) {
    return false;
  } else {
    $wp_test_expectations['options'][$key] = $value;
    return true;
  }
}
                                    
function delete_option($key) {
  global $wp_test_expectations;
  if (isset($wp_test_expectations['options'][$key])) {
    unset($wp_test_expectations['options'][$key]);
    return true;
  } else {
    return false;
  }
}
                    
function untrailingslashit($string) {
  return preg_replace('#/$#', '', $string);
}

function add_category($id, $object) {
  global $wp_test_expectations;
  $object->cat_ID = $object->term_id = $id;
  $wp_test_expectations['categories'][$id] = $object;
}
    
function get_category($id) {
  global $wp_test_expectations;
  if (!isset($wp_test_expectations['categories'])) {
    return null;
  } else {
    return $wp_test_expectations['categories'][$id];
  }
}
        
function get_all_category_ids() {
  global $wp_test_expectations;
  return array_keys($wp_test_expectations['categories']);
}

function get_gmt_from_date($date_string) {
  return $date_string;
}
  
function get_cat_name($id) {
  global $wp_test_expectations;
  return $wp_test_expectations['categories'][$id]->name;
}
                  
function wp_set_post_categories($post_id, $categories) {
  global $wp_test_expectations;
  if (!is_array($categories)) { $categories = array($categories); }
  $wp_test_expectations['post_categories'][$post_id] = $categories;
}

function wp_get_post_categories($post_id) {
  global $wp_test_expectations;
  if (!isset($wp_test_expectations['post_categories'][$post_id])) {
    return array();
  } else {
    return $wp_test_expectations['post_categories'][$post_id];
  }
}

function _set_up_get_posts_response($query, $result) {
  global $wp_test_expectations;
  $wp_test_expectations['get_posts'][$query] = $result;
}

function get_posts($query) {
  global $wp_test_expectations;
  
  if (isset($wp_test_expectations['get_posts'][$query])) {
    return $wp_test_expectations['get_posts'][$query];
  } else {
    return array();
  }
}

function wp_insert_post($array) {
  global $wp_test_expectations;

  $array = (array)$array;
  if (isset($array['ID'])) {
    $id = $array['ID'];
  } else {
    if (count($wp_test_expectations['posts']) == 0) {
      $id = 1;
    } else {
      $id = max(array_keys($wp_test_expectations['posts'])) + 1;
    }
    $array['ID'] = $id;
  }
  $wp_test_expectations['posts'][$id] = (object)$array;    
  return $id;
}

function get_post($id) {
  global $wp_test_expectations;
  
  if (isset($wp_test_expectations['posts'][$id])) {
    return $wp_test_expectations['posts'][$id];
  } else {
    return null; 
  }
}

function add_options_page($page_title, $menu_title, $access_level, $file, $function = "") {
  add_submenu_page('options-general.php', $page_title, $menu_title, $access_level, $file, $function);
}

function add_submenu_page($parent, $page_title, $menu_title, $access_level, $file, $function = "") {
  global $wp_test_expectations;
  
  $wp_test_expectations['pages'][] = compact('parent', 'page_title', 'menu_title', 'access_level', 'file', 'function');
  
  return "hook name";
}

function add_action($name, $callback) {
  global $wp_test_expectations;
  $wp_test_expectations['actions'][$name] = $callback;
}

function add_filter($name, $callback) {
  global $wp_test_expectations;
  $wp_test_expectations['filters'][$name] = $callback;
}

function wp_nonce_field($name) {
  echo "<input type=\"hidden\" name=\"${name}\" value=\"" . md5(rand()) . "\" />";
}

function update_post_meta($post_id, $field, $value) {
  global $wp_test_expectations;
  if (!isset($wp_test_expectations['post_meta'][$post_id])) {
    $wp_test_expectations['post_meta'][$post_id] = array();
  }
  $wp_test_expectations['post_meta'][$post_id][$field] = $value;
}

function get_post_meta($post_id, $field, $single = false) {
  global $wp_test_expectations;

  if (!isset($wp_test_expectations['post_meta'][$post_id])) { return ""; }
  if (!isset($wp_test_expectations['post_meta'][$post_id][$field])) { return ""; }
  
  return ($single) ? $wp_test_expectations['post_meta'][$post_id][$field] : array($wp_test_expectations['post_meta'][$post_id][$field]);
}

function __($string, $namespace) {
  return $string;
}

// For use with SimpleXML

function _node_exists($xml, $xpath) {
  return count($xml->xpath($xpath)) > 0;
}

function _get_node_value($xml, $xpath) {
  $result = $xml->xpath($xpath);
  return (count($result) > 0) ? (string)reset($result) : null;
}

function _wrap_xml($string) {
  return new SimpleXMLElement("<x>" . $string . "</x>");
}

?>
