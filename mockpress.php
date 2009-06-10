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
    'post_meta' => array(),
    'themes' => array(),
    'plugin_domains' => array(),
    'enqueued' => array(),
    'all_tags' => array(),
    'sidebar_widgets' => array(),
    'widget_controls' => array(),    
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
  if (!isset($wp_test_expectations['options'][$key])) {
    $wp_test_expectations['options'][$key] = $value;
    return true;
  } else {
    if ($wp_test_expectations['options'][$key] == $value) {
      return false;
    } else {
      $wp_test_expectations['options'][$key] = $value;
      return true;
    }
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

function get_post($id, $output = "") {
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

function add_menu_page($page_title, $menu_title, $access_level, $file, $function, $icon) {
  global $wp_test_expectations;
  $parent = "";
  
  $wp_test_expectations['pages'][] = compact('parent', 'page_title', 'menu_title', 'access_level', 'file', 'function', 'icon');
  
  return "hook name";
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

function post_exists($title, $content, $date) {
  global $wp_test_expectations;
  foreach ($wp_test_expectations['posts'] as $post_id => $post) {
    if (
      ($post->post_title == $title) &&
      ($post->post_date == $date)
    ) {
      return $post_id;
    }
  }
  return 0;
}

function get_permalink($post) {
  return $post->post_name;
}

function __($string, $namespace) {
  return $string;
}

function _e($string, $namespace) {
  echo $string;
}

function plugin_basename($file) { return $file; }

function get_theme($name) {
  global $wp_test_expectations;
  if (isset($wp_test_expectations['themes'][$name])) {
    return $wp_test_expectations['themes'][$name];
  } else {
    return null;
  }
}

function get_current_theme() {
  global $wp_test_expectations;
  return $wp_test_expectations['current_theme'];
}

function _set_current_theme($theme) {
  global $wp_test_expectations;
  $wp_test_expectations['current_theme'] = $theme;
}

function load_plugin_textdomain($domain, $path) {
  global $wp_test_expectations;
  $wp_test_expectations['plugin_domains'][] = "${domain}-${path}";
}

function wp_enqueue_script($script) {
  global $wp_test_expectations;
  $wp_test_expectations['enqueued'][$script] = true;
}

function _did_wp_enqueue_script($script) {
  global $wp_test_expectations;
  return isset($wp_test_expectations['enqueued'][$script]);
}

function _setup_query($string) {
  $_SERVER['QUERY_STRING'] = $string;
}

function add_query_arg($parameter, $value) {
  $separator = (strpos($_SERVER['QUERY_STRING'], "?") === false) ? "?" : "&";
  return $_SERVER['QUERY_STRING'] . $separator . $parameter . "=" . urlencode($value);
}

function _set_all_tags($tags) {
  global $wp_test_expectations;
  $wp_test_expectations['all_tags'] = $tags;
}

function get_tags() {
  global $wp_test_expectations;
  return $wp_test_expectations['all_tags'];
}

function _set_user_can_richedit($can) {
  global $wp_test_expectations;
  $wp_test_expectations['user_can_richedit'] = $can;
}

function user_can_richedit() {
  global $wp_test_expectations;
  return $wp_test_expectations['user_can_richedit'];
}

function the_editor($content) {
  echo $content;
}

function wp_register_sidebar_widget($id, $name, $output_callback, $options = array()) {
  register_sidebar_widget($id, $name, $output_callback, $options);
}

function register_sidebar_widget($id, $name, $output_callback, $options = array()) {
  global $wp_test_expectations; 

  $wp_test_expectations['sidebar_widgets'][] = compact('id', 'name', 'output_callback', 'options');
}

function register_widget_control($name, $control_callback, $width = '', $height = '') {
  global $wp_test_expectations; 
  $params = array_slice(func_get_args(), 4);

  $wp_test_expectations['widget_controls'][] = compact('id', 'name', 'output_callback', 'options', 'params');
}

// For use with SimpleXML

$_xml_cache = array();

function _to_xml($string, $show_exception = false) {
  global $_xml_cache;
  
  $key = md5($string);
  if (!isset($_xml_cache[$key])) {
    try {
      $_xml_cache[$key] = new SimpleXMLElement("<x>" . str_replace(
                                                         array("&mdash;"),
                                                         array("--"),
                                                         $string
                                                       ) . "</x>");
    } catch (Exception $e) {
      if ($show_exception) {
        echo $e->getMessage() . "\n\n";
        
        $lines = explode("\n", $string);
        for ($i = 0, $il = count($lines); $i < $il; ++$i) {
          echo str_pad(($i + 1), strlen($il), " ", STR_PAD_LEFT) . "# " . $lines[$i] . "\n";
        }
        echo "\n";
      }
      $_xml_cache[$key] = false;
    }    
  }
  return $_xml_cache[$key];
}

function _xpath_test($xml, $xpath, $value) {
  if ($value === true) { $value = "~*exists*~"; }
  if ($value === false) { $value = "~*not exists*~"; }
  switch ($value) {
    case "~*exists*~":
      return _node_exists($xml, $xpath);
      break; 
    case "~*not exists*~":
      return !(_node_exists($xml, $xpath));
      break; 
    default:
      return _get_node_value($xml, $xpath) == $value;
  }
  return false;
}

function _node_exists($xml, $xpath) {
  $result = $xml->xpath($xpath);
  if (is_array($result)) {
    return count($xml->xpath($xpath)) > 0;
  } else {
    return false;
  }
}

function _get_node_value($xml, $xpath) {
  $result = $xml->xpath($xpath);
  if (is_array($result)) {
    return (count($result) > 0) ? trim((string)reset($result)) : null;
  } else {
    return false;
  }
}

function _wrap_xml($string) {
  return new SimpleXMLElement("<x>" . $string . "</x>");
}

?>