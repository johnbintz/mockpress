<?php

/**
 * Simulate enough of WordPress to test plugins and themes using PHPUnit, SimpleTest, or any other PHP unit testing framework.
 * @author John Bintz
 */

$wp_test_expectations = array();

require_once('includes/cache.php');
require_once('includes/media.php');
require_once('includes/posts.php');
require_once('includes/filtering.php');

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
    'admin_pages' => array(),
    'pages' => array(),
  	'posts' => array(),
    'actions' => array(),
    'filters' => array(),
    'post_meta' => array(),
    'themes' => array(),
    'plugin_domains' => array(),
    'enqueued' => array(),
    'all_tags' => array(),
    'sidebar_widgets' => array(),
    'widget_controls' => array(),
    'nonce' => array(),
    'wp_widgets' => array(),
    'current' => array(
      'is_feed' => false
    ),
    'plugin_data' => array(),
    'theme' => array(
      'posts' => array()
    ),
    'bloginfo' => array(),
    'user_capabilities' => array(),
    'children' => array(),
    'current_user' => null,
    'users' => array(),
    'user_meta' => array(),
    'image_downsize' => array()
  );

  wp_cache_init();
}

/*** WordPress Test Doubles ***/

function wp_clone($object) {
  return version_compare(phpversion(). '5.0', '>=') ? clone($object) : $object;
}

/** Options **/

/**
 * Get an option from the WP Options table.
 * @param string $key The option to retrieve.
 * @return string|boolean The value of the option, or false if the key doesn't exist.
 */
function get_option($key) {
  global $wp_test_expectations;
  if (is_string($key)) {
    if (isset($wp_test_expectations['options'][$key])) {
      return maybe_unserialize($wp_test_expectations['options'][$key]);
    } else {
      return false;
    }
  } else {
    return false;
  }
}

/**
 * Store an option in the WP Options table.
 * @param string $key The option to store.
 * @param string $value The value to store.
 * @return boolean True if the option was updated, false otherwise.
 */
function update_option($key, $value) {
  global $wp_test_expectations;
  $value = maybe_serialize($value);
  if (is_string($key)) {
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
  } else {
    return false;
  }
}

/**
 * Delete an option from the WP Options table.
 * @param string $key The option to delete.
 * @return boolean True if the option was deleted.
 */
function delete_option($key) {
  global $wp_test_expectations;
  if (is_string($key)) {
    if (isset($wp_test_expectations['options'][$key])) {
      unset($wp_test_expectations['options'][$key]);
      return true;
    } else {
      return false;
    }
  } else {
    return false;
  }
}

function get_alloptions() {
	global $wp_test_expectations;
	return $wp_test_expectations['options'];
}

function wp_load_alloptions() {
	return get_alloptions();
}

/** String Utility Functions **/

/**
 * Remove a trailing slash from a string if it exists.
 * @param string $string The string to check for trailing slashes.
 * @return string The string with a trailing slash removed, if necessary.
 */
function untrailingslashit($string) {
  return preg_replace('#/$#', '', $string);
}

/**
 * Add a trailing slash to a string if it does not exist.
 * @param string $string The string to which a trailing slash should be added.
 * @return string The string with a trailing slash added, if necessary.
 */
function trailingslashit($string) {
  return preg_replace('#([^/])$#', '\1/', $string);
}

function user_trailingslashit($string, $type_of_url = '') {
	$which = 'untrailingslashit';
	if ($permalink_structure = get_option('permalink_structure')) {
		if (preg_match('#/$#', $permalink_structure) > 0) {
			$which = 'trailingslashit';
		}
	}
	return call_user_func($which, $string);
}

/**
 * Get GMT string from date string.
 * Currently does nothing.
 * @param string $date_string The date string to convert.
 * @return string The converted date string in GMT.
 */
function get_gmt_from_date($date_string) {
  return $date_string;
}

/**
 * Return a string that's been internationalized.
 * @param string $string The string to check for i18n.
 * @param string $namespace The namespace to check.
 * @return string The i18n string.
 */
function __($string, $namespace = 'default') {
  return $string;
}

/**
 * Echo an internationalized string.
 * @param string $string The string to check for i18n.
 * @param string $namespace The namespace to check.
 */
function _e($string, $namespace = 'default') {
  echo __($string, $namespace);
}

/**
 * Return a different string if the number of items is 1.
 * @deprecated Is now _n in WordPress 2.8.
 * @param string $single The string to return if only one item.
 * @param string $plural The string to return if not one item.
 * @param string $number The number of items.
 * @param string $domain The text domain.
 * @return string The correct string.
 */
function __ngettext($single, $plural, $number, $domain) {
  return _n($single, $plural, $number, $domain);
}

/**
 * Return a different string if the number of items is 1.
 * @param $single The string to return if only one item.
 * @param $plural The string to return if not one item.
 * @param $number The number of items.
 * @param $domain The text domain.
 * @return string The correct string.
 */
function _n($single, $plural, $number, $domain) {
  return ($number == 1) ? $single : $plural;
}

/**
 * True if the data provided was created by serialize()
 * @param mixed $data The data to check.
 * @return boolean True if the data was created by serialize.
 */
function is_serialized($data) {
  return (@unserialize($data) !== false);
}

/**
 * Try to serialize the data and return the serialized string.
 * @param mixed $data The data to try to serialize.
 * @return mixed The data, possibly serialized.
 */
function maybe_serialize($data) {
  if (is_array($data) || is_object($data) || is_serialized($data)) {
    return serialize($data);
  } else {
    return $data;
  }
}

/**
 * Try to unserialize the data and return the serialized data.
 * @param mixed $data The data to try to unserialize.
 * @return mixed The data, possibly unserialized.
 */
function maybe_unserialize($data) {
  if (is_serialized($data)) {
    if (($gm = unserialize($data)) !== false) { return $gm; }
  }
  return $data;
}

/** Categories **/

/**
 * Add a category.
 * @param int $id The category ID.
 * @param object $object The category object.
 * @throws Error if $id is not numeric or $category is not an object.
 */
function add_category($id, $object) {
  global $wp_test_expectations;
  if (is_object($object)) {
    if (is_numeric($id)) {
      $object->cat_ID = $object->term_id = (int)$id;
      if (!isset($object->parent)) { $object->parent = 0; }
      $wp_test_expectations['categories'][$id] = $object;
    } else {
      trigger_error("ID must be numeric");
    }
  } else {
    trigger_error("Category provided must be an object");
  }
}

function wp_insert_category($catarr) {
  global $wp_test_expectations;

	if (is_object($catarr)) { $catarr = (array)$catarr; }
	if (is_array($catarr)) {
		if (empty($catarr['cat_ID'])) {
			$max_id = 1;
			foreach ($wp_test_expectations['categories'] as $category) {
				if (isset($category->cat_ID)) {
					$max_id = max($max_id, $category->cat_ID + 1);
				}
			}
			add_category($max_id, (object)$catarr);
			return $max_id;
		}
	}
	return 0;
}

/**
 * Get a category.
 * @param int $id The category ID to retrieve.
 * @return object|WP_Error The category object, or a WP_Error object on failure.
 */
function get_category($id) {
  global $wp_test_expectations;
  if (!isset($wp_test_expectations['categories'])) {
    return new WP_Error();
  } else {
  	if (isset($wp_test_expectations['categories'][$id])) {
	    return $wp_test_expectations['categories'][$id];
  	}
  }
}

function wp_delete_category($id) {
  global $wp_test_expectations;

  if (isset($wp_test_expectations['categories'][$id])) {
  	$ok = true;
  	if (($value = get_option('default_category')) !== false) {
  		$ok = ($value != $id);
  	}
  	if ($ok) {
			unset($wp_test_expectations['categories'][$id]);
			return true;
  	}
  }
  return false;
}

/**
 * Get all category IDs.
 * @return array All valid category IDs.
 */
function get_all_category_ids() {
  global $wp_test_expectations;
  return array_keys($wp_test_expectations['categories']);
}

/**
 * Get a category's name.
 * @param int $id The id of the category.
 * @return string|null The name, or null if the category is not found.
 */
function get_cat_name($id) {
  global $wp_test_expectations;
  if (isset($wp_test_expectations['categories'][$id])) {
    return $wp_test_expectations['categories'][$id]->name;
  } else {
    return null;
  }
}

/**
 * Set a post's categories.
 * @param int $post_id The post to modify.
 * @param array $categories The categories to set for this post.
 */
function wp_set_post_categories($post_id, $categories) {
  global $wp_test_expectations;
  if (!is_array($categories)) { $categories = array($categories); }
  $wp_test_expectations['post_categories'][$post_id] = $categories;
}

/**
 * Get a post's categories.
 * @param int $post_id The post to query.
 * @return array The categories for this post.
 */
function wp_get_post_categories($post_id) {
  global $wp_test_expectations;
  if (!isset($wp_test_expectations['post_categories'][$post_id])) {
    return array();
  } else {
    return $wp_test_expectations['post_categories'][$post_id];
  }
}

/**
 * Get the permalink to a category.
 * For MockPress's purposes, the link will look like "/category/${category_id}"
 * @param int $category_id The category ID.
 * @return string|WP_Error The URI or a WP_Error object upon failure.
 */
function get_category_link($category_id) {
  global $wp_test_expectations;
  if (isset($wp_test_expectations['categories'][$category_id])) {
    return "/category/${category_id}";
  } else {
    return new WP_Error();
  }
}

/** Tags **/

/**
 * Get a post's tags.
 * @param int $post_id The post to query.
 * @return array The tags for the post.
 */
function wp_get_post_tags($post_id) {
  global $wp_test_expectations;
  if (!isset($wp_test_expectations['post_tags'][$post_id])) {
    return array();
  } else {
    return $wp_test_expectations['post_tags'][$post_id];
  }
}

/**
 * Set a post's tags.
 * @param int $post_id The post to modify.
 * @param array $tags The tags to set for this post. Note that these should be text strings and not objects. E_USER_WARNING will be raised if you don't pass in a string.
 * @raises E_USER_WARNING if an object other than a string exists in the $tags array.
 */
function wp_set_post_tags($post_id, $tags) {
  global $wp_test_expectations;
  $tags = (array)$tags;
  foreach ($tags as $tag) {
    if (!is_string($tag)) { trigger_error("All tags sent to wp_set_post_tags() need to be strings."); }
  }
  $wp_test_expectations['post_tags'][$post_id] = array();
  foreach ($tags as $tag) {
    $wp_test_expectations['post_tags'][$post_id][] = (object)array(
      'name' => $tag, 'slug' => $tag
    );
  }
}

/**
 * Set the wp_get_post_tags response for the requested post.
 * You can't pass in objects for wp_set_post_tags, so if you need more information beyond name & slug, use this.
 * No checking is done to ensure you're passing in sane data.
 * @param int $post_id The post to modify.
 * @param array $tags The tags to set.
 */
function _set_wp_post_tag_objects($post_id, $tags) {
  global $wp_test_expectations;
  $wp_test_expectations['post_tags'][$post_id] = $tags;
}

/**
 * Set the output for get_tags()
 * @param array $tags The output for get_tags()
 */
function _set_all_tags($tags) {
  global $wp_test_expectations;
  $wp_test_expectations['all_tags'] = $tags;
}

/**
 * Get all tags within WordPress.
 * @return array All the tags within WordPress.
 */
function get_tags() {
  global $wp_test_expectations;
  return $wp_test_expectations['all_tags'];
}

/** Pages **/

function get_pages() {
	global $wp_test_expectations;
	$pages = array();
	if (isset($wp_test_expectations['posts'])) {
		if (is_array($wp_test_expectations['posts'])) {
			foreach ($wp_test_expectations['posts'] as $post) {
				if (isset($post->post_type)) {
					if ($post->post_type == 'page') { $pages[] = $post; }
				}
			}
		}
	}
	return $pages;
}

/** Core **/

/**
 * Attach a callback to an action hook.
 * @param string $name The hook to attach to.
 * @param callback $callback The callback to execute.
 */
function add_action($name, $callback) {
  global $wp_test_expectations;
  $wp_test_expectations['actions'][$name] = $callback;
}

function do_action($name) {

}

/**
 * Attach a callback to a filter hook.
 * @param string $name The hook to attach to.
 * @param callback $callback The callback to execute.
 */
function add_filter($name, $callback, $priority = 10, $parameters = 2) {
  global $wp_test_expectations;
  if (!isset($wp_test_expectations['filters'][$name])) {
    $wp_test_expectations['filters'][$name] = array();
  }
  if (!isset($wp_test_expectations['filters'][$name][$priority])) {
    $wp_test_expectations['filters'][$name][$priority] = array();
  }
  $wp_test_expectations['filters'][$name][$priority] = compact('callback', 'parameter_count');
  ksort($wp_test_expectations['filters'][$name]);
}

/**
 * Run attached filter hooks.
 * @param string $name The hook to call.
 * @param mixed,... $arguments The arguments to the hooks.
 * @return mixed The return value.
 */
function apply_filters() {
  global $wp_test_expectations;

  $parameters = func_get_args();
  $name = array_shift($parameters);

  if (isset($wp_test_expectations['filters'][$name])) {
    // override the normal filter processing
    $override = false;
    if (count($wp_test_expectations['filters'][$name]) == 2) {
      if ($wp_test_expectations['filters'][$name][0] === true) {
        $parameters = $wp_test_expectations['filters'][$name][1];
        $override = true;
      }
    }
    if (!$override) {
      foreach ($wp_test_expectations['filters'][$name] as $priority => $callbacks) {
        foreach ($callbacks as $info) {
          extract($info);
          if (count($parameters) == $parameter_count) {
            $parameters = call_user_func_array($callback, $paremeters);
          } else {
            throw new Exception("Got " . count($parameters) . " parameters, expected ${parameter_count} for filter ${name}, callback " . print_r($callback, true));
          }
        }
      }
    }
  }

  return reset($parameters);
}

/**
 * Set the expected result for a particular filter.
 * @param string $name The name of the filter
 * @param array $result The result of the filter.
 */
function _set_filter_expectation($name, $result) {
  global $wp_test_expectations;

  $result = (array)$result;
  $wp_test_expectations['filters'][$name] = array(true, $result);
}

/**
 * Ensure that input has default values injected into it.
 * @param string|array $input The input values.
 * @param array $defaults The default values.
 * @return array The default values with the provided input merged overtop.
 */
function wp_parse_args($input, $defaults) {
	if (is_string($input)) {
		parse_str($input, $r);
		$input = $r;
	}
	if (is_array($input)) {
		return array_merge($defaults, $input);
	} else {
		return $defaults;
	}
}

/** Admin **/

/**
 * Add a page to the Options menu.
 */
function add_options_page($page_title, $menu_title, $access_level, $file, $function = "") {
  add_submenu_page('options-general.php', $page_title, $menu_title, $access_level, $file, $function);
}

/**
 * Add a page to the main menu.
 */
function add_menu_page($page_title, $menu_title, $access_level, $file, $function, $icon) {
  global $wp_test_expectations;
  $parent = "";

  $wp_test_expectations['admin_pages'][] = compact('parent', 'page_title', 'menu_title', 'access_level', 'file', 'function', 'icon');

  return "hook name";
}

/**
 * Add a page below a main page.
 */
function add_submenu_page($parent, $page_title, $menu_title, $access_level, $file, $function = "") {
  global $wp_test_expectations;

  $wp_test_expectations['admin_pages'][] = compact('parent', 'page_title', 'menu_title', 'access_level', 'file', 'function');

  return "hook name";
}

/**
 * Set whether or not a user can use the rich text editor.
 * @param boolean $can True if the user can use the editor.
 */
function _set_user_can_richedit($can) {
  global $wp_test_expectations;
  $wp_test_expectations['user_can_richedit'] = $can;
}

/**
 * Find out if the user can use the rich text editor.
 * @return boolean True if the user can use the editor.
 */
function user_can_richedit() {
  global $wp_test_expectations;
  return $wp_test_expectations['user_can_richedit'];
}

/**
 * Embed the rich text editor.
 */
function the_editor($content) {
  echo $content;
}

/** Plugin **/

/**
 * Get the basename of a file relative to the plugins directory.
 */
function plugin_basename($file) { return $file; }

/**
 * Load the translation files for the current plugin.
 * @param string $domain The text domain to load files for.
 * @param string $path The path to the translation files.
 */
function load_plugin_textdomain($domain, $path) {
  global $wp_test_expectations;
  $wp_test_expectations['plugin_domains'][] = "${domain}-${path}";
}

/**
 * Enqueue a script library to be loaded.
 * @param string $script The script library to load.
 */
function wp_enqueue_script($script) {
  global $wp_test_expectations;
  $wp_test_expectations['enqueued'][$script] = true;
}

/**
 * Determine if a script library was enqueued.
 * @param string $script The script library to check.
 * @return boolean True if the library was enqueued to be loaded.
 */
function _did_wp_enqueue_script($script) {
  global $wp_test_expectations;
  return isset($wp_test_expectations['enqueued'][$script]);
}

/** Nonce **/

/**
 * Set up a specific valid nonce.
 * @param string $name The name of the nonce.
 * @param string $value The provided nonce value.
 */
function _set_valid_nonce($name, $value) {
  global $wp_test_expectations;
  $wp_test_expectations['nonce'][$name] = $value;
}

/**
 * Get a nonce value.
 * @param string $name The name of the nonce.
 * @return string|boolean The nonce value, or false if no nonce found.
 */
function _get_nonce($name) {
  global $wp_test_expectations;
  if (isset($wp_test_expectations['nonce'][$name])) {
    return $wp_test_expectations['nonce'][$name];
  } else {
    return false;
  }
}

/**
 * Create a random nonce.
 * @param string $name The name of the nonce.
 * @return string The nonce value.
 */
function wp_create_nonce($name) {
  global $wp_test_expectations;

  if (!isset($wp_test_expectations['nonce'][$name])) {
    $wp_test_expectations['nonce'][$name] = md5(rand());
  }
  return $wp_test_expectations['nonce'][$name];
}

/**
 * Verify that the provided nonce value matches.
 * @param string $value The value to check.
 * @param string $name The name of the nonce.
 * @return boolean True if the nonce matches the provided value.
 */
function wp_verify_nonce($value, $name) {
  global $wp_test_expectations;

  if (isset($wp_test_expectations['nonce'][$name])) {
    return $wp_test_expectations['nonce'][$name] == $value;
  }
  return false;
}

/**
 * Create an &lt;input /&gt; field ready for a nonce value.
 * @param string $name The name of both the nonce and the input field.
 */
function wp_nonce_field($name) {
  global $wp_test_expectations;

  echo "<input type=\"hidden\" name=\"${name}\" value=\"" . wp_create_nonce($name) . "\" />";
}

/** Theme **/

/**
 * Get information on the specified theme.
 * @param string $name The name of the theme.
 * @return array|null The theme information as an array, or null if not found.
 */
function get_theme($name) {
  global $wp_test_expectations;
  if (isset($wp_test_expectations['themes'][$name])) {
    return $wp_test_expectations['themes'][$name];
  } else {
    return null;
  }
}

/**
 * Get the name of the current theme.
 * @return string The name of the current theme.
 */
function get_current_theme() {
  global $wp_test_expectations;
  return $wp_test_expectations['current_theme'];
}

/**
 * Set the name of the current theme.
 * @param string $theme The name of the current theme.
 */
function _set_current_theme($theme) {
  global $wp_test_expectations;
  $wp_test_expectations['current_theme'] = $theme;
}

/** Query **/

/**
 * Set up the query string.
 * @param string $string The query string.
 */
function _setup_query($string) {
  $_SERVER['QUERY_STRING'] = $string;
}

/**
 * Add an argument to the query string.
 * @param string $parameter The parameter to add.
 * @param string $value The value to place in the URL.
 * @return string The modified query string.
 */
function add_query_arg($parameter, $value) {
  $separator = (strpos($_SERVER['QUERY_STRING'], "?") === false) ? "?" : "&";
  return $_SERVER['QUERY_STRING'] . $separator . $parameter . "=" . urlencode($value);
}

/**
 * Get the search query from the query string.
 * @return string The search query, or blank if not found.
 */
function get_search_query() {
  $parts = explode("&", preg_replace("#^.*\?#", "", $_SERVER['QUERY_STRING']));
  foreach ($parts as $part) {
    list($param, $value) = explode("=", $part);
    if ($param == "s") {
      return $value;
    }
  }

  return "";
}

/**
 * Echo out the search query.
 */
function the_search_query() {
  echo get_search_query();
}

/** Pre-2.8 Widgets **/

/**
 * Register a widget.
 * Wrapper around register_sidebar_widget.
 */
function wp_register_sidebar_widget($id, $name, $output_callback, $options = array()) {
  register_sidebar_widget($id, $name, $output_callback, $options);
}

/**
 * Register a widget.
 */
function register_sidebar_widget($id, $name, $output_callback = "", $options = array()) {
  global $wp_test_expectations;

  $wp_test_expectations['sidebar_widgets'][] = compact('id', 'name', 'output_callback', 'options');
}

/**
 * Register the controls for a widget.
 */
function register_widget_control($name, $control_callback, $width = '', $height = '') {
  global $wp_test_expectations;
  $params = array_slice(func_get_args(), 4);

  $wp_test_expectations['widget_controls'][] = compact('id', 'name', 'output_callback', 'options', 'params');
}

/** Template Tags and Theme Testing **/

/**
 * Set a theme expectation.
 * @param string $which The expectation to set.
 * @param string $value The value to set the expectation to.
 */
function _set_theme_expectation($which, $value) {
  global $wp_test_expectations;
  $wp_test_expectations['theme'][$which] = $value;
}

/**
 * Set the template directory.
 * @param string $dir The template directory.
 */
function _set_template_directory($dir) {
  global $wp_test_expectations;
  $wp_test_expectations['theme']['template_directory'] = $dir;
}

/**
 * Set the child theme's directory.
 * @param string $dir The template directory.
 */
function _set_stylesheet_directory($dir) {
  global $wp_test_expectations;
  $wp_test_expectations['theme']['stylesheet_directory'] = $dir;
}

/**
 * Set a 'current' expectation, such as if the current page load is an RSS feed.
 * @param string $field The expectation to set.
 * @param mixed $value The value of the expectation. Usually a boolean.
 */
function _set_current_option($field, $value) {
  global $wp_test_expectations;
  $wp_test_expectations['current'][$field] = $value;
}

/**
 * True if currently in an RSS feed.
 * @return boolean True if in a feed.
 */
function is_feed() {
  global $wp_test_expectations;
  return $wp_test_expectations['current']['is_feed'];
}

/**
 * True if the current user is an admin.
 * @return boolean True if an admin.
 */
function is_admin() {
  global $wp_test_expectations;
  return $wp_test_expectations['current']['is_admin'];
}

/**
 * True if the current post is a page.
 * @return boolean True if it's a page.
 */
function is_page() {
	global $post;
	if (!empty($post)) {
		return $post->post_type == "page";
	}
	return false;
}

/**
 * Get plugin data (author, version, etc.)
 * @param string $filepath The path to the file which contains plugin data.
 */
function get_plugin_data($filepath) {
  global $wp_test_expectations;
  return $wp_test_expectations['plugin_data'][$filepath];
}

/**
 * Return the URL to the plugin directory that contains the provided file.
 */
function plugin_dir_url($file) {
  return $file;
}

/**
 * Return the URL to the plugin directory.
 */
function plugins_url($path = '', $plugin = '') {
  return $path;
}

/**
 * Add a post to the main WP_Query Loop.
 * @param object $post A post to add.
 */
function _add_theme_post($post) {
  global $wp_test_expectations;
  $wp_test_expectations['theme']['posts'][] = $post;
}

/**
 * Echo the site header.
 */
function get_header() {
  global $wp_test_expectations;
  echo $wp_test_expectations['theme']['header'];
}

/**
 * Echo the sidebar.
 */
function get_sidebar() {
  global $wp_test_expectations;
  echo $wp_test_expectations['theme']['sidebar'];
}

/**
 * Echo the footer.
 */
function get_footer() {
  global $wp_test_expectations;
  echo $wp_test_expectations['theme']['footer'];
}

/**
 * Are there posts for the theme?
 */
function have_posts() {
  global $wp_test_expectations;
  return is_array($wp_test_expectations['theme']['posts']) && !empty($wp_test_expectations['theme']['posts']);
}

/**
 * Get the current Loop post.
 */
function the_post() {
  global $wp_test_expectations, $post;
  if (is_array($wp_test_expectations['theme']['posts']) && !empty($wp_test_expectations['theme']['posts'])) {
    $post = array_shift($wp_test_expectations['theme']['posts']);
  }
}

/**
 * Echo the ID of the current Loop post.
 */
function the_ID() {
  global $post;
  echo $post->ID;
}

/**
 * Echo the permalink to the current Loop post.
 * For testing purposes, this is just the guid of the current post.
 */
function the_permalink() {
  global $post;
  echo $post->guid;
}

/**
 * Echo the post title of the current Loop post.
 */
function the_title() {
  global $post;
  echo $post->post_title;
}

/**
 * Get the title of the current post.
 */
function get_the_title($override_post = null) {
	global $post;

	$post_to_use = is_null($override_post) ? $post : $override_post;
	$post_to_use = get_post($post_to_use);

	if (is_object($post_to_use)) {
		if (isset($post_to_use->post_title)) {
			return $post_to_use->post_title;
		}
	}
	return '';
}

/**
 * Echo the post title, run through htmlentitles, of the current Loop post.
 */
function the_title_attribute() {
  global $post;
  echo htmlentities($post->post_title);
}

/**
 * Echo the post time of the current Loop post, run through date().
 */
function the_time($format) {
  global $post;
  echo date($format, $post->post_date);
}

/**
 * Echo the post author of the current Loop post.
 */
function the_author() {
  global $post;
  echo $post->post_author;
}

/**
 * Print the content of the post.
 * @param string $more_link_text If the content is multi-page, the text for the next page link.
 */
function the_content($more_link_text = "") {
  global $post;
  echo $post->post_content;

  if (strpos($post->post_content, "<!--more") !== false) {
    echo $more_link_text;
  }
}

/**
 * Print the tags for the post.
 * @param string $start The prefix to the tag listing.
 * @param string $separator The string between each tag.
 * @param string $finish The suffix to the tag listing.
 */
function the_tags($start, $separator, $finish) {
  global $post;

  $tag_output = array();
  foreach (wp_get_post_tags($post->ID) as $tag) {
    $tag_output = '<a href="' . $tag->slug . '">' . $tag->name . '</a>';
  }

  echo $start . implode($separator, $tag_output) . $finish;
}


/**
 * Print the categories for the post.
 * @param string $separator The string between each category.
 */
function the_category($separator) {
  global $post;

  $category_output = array();
  foreach (wp_get_post_tags($post->ID) as $category) {
    $category_output = '<a href="' . $category->slug . '">' . $category->name . '</a>';
  }

  echo implode($separator, $category_output);
}

/**
 * If there are more posts, print a link that links to the subsequent posts.
 * @param string $link_test The text for the link.
 */
function next_posts_link($link_text) {
  global $wp_test_expectations;
  if ($wp_test_expectations['theme']['has_next_posts']) {
    echo '<a href="#mockpress:next">' . $link_text . '</a>';
  }
}

/**
 * Get the theme's root directory.
 * @return string The template directory.
 */
function get_template_directory() {
  global $wp_test_expectations;
  if (isset($wp_test_expectations['theme']['template_directory'])) {
	  return $wp_test_expectations['theme']['template_directory'];
  }
}

/**
 * Get the child theme's root directory.
 * @return string The child theme's root directory.
 */
function get_stylesheet_directory() {
  global $wp_test_expectations;
  if (isset($wp_test_expectations['theme']['stylesheet_directory'])) {
	  return $wp_test_expectations['theme']['stylesheet_directory'];
  }
}

/**
 * Set a bloginfo() field.
 * @param string $field The field to set.
 * @param string $value The value that the bloginfo() call should return.
 */
function _set_bloginfo($field, $value) {
  global $wp_test_expectations;
  $wp_test_expectations['bloginfo'][$field] = $value;
}

/**
 * Echo a bloginfo value.
 * @param string $field The field to return.
 */
function bloginfo($field) {
  echo get_bloginfo($field, 'display');
}

/**
 * Get a bloginfo value.
 * @param string $field The field to return.
 * @param string $display The display method.
 * @return string The bloginfo field value.
 */
function get_bloginfo($field, $filter = 'raw') {
  global $wp_test_expectations;
  return $wp_test_expectations['bloginfo'][$field];
}

/** Media **/

/**
 * Get an &lt;img /> tag for the requested attachment.
 * @param int $id The attachment ID.
 * @param string $size The size of the image to display.
 * @param boolean $icon
 * @return The &lt;img /> tag for the attachment.
 */
function wp_get_attachment_image($id, $size = 'thumbnail', $icon = false) {
  global $wp_test_expectations;
  if (isset($wp_test_expectations['posts'][$id])) {
    return '<img src="' . $wp_test_expectations['posts'][$id]->guid . '" />';
  }
}

/** User roles **/

/**
 * Set a user capability.
 * @param string,... $capabilities The capabilities to give the current user.
 */
function _set_user_capabilities() {
  global $wp_test_expectations;

  $capabilities = func_get_args();
  if (is_array($capabilities[0])) { $capabilities = $capabilities[0]; }
  foreach ($capabilities as $capability) {
    $wp_test_expectations['user_capabilities'][$capability] = true;
  }
}

/**
 * See if the current user can perform all of the requested actions.
 * @param string,... $capabilities The actions the user should be able to perform.
 * @return boolean True if the current user can perform all of the actions.
 */
function current_user_can() {
  global $wp_test_expectations;

  $capabilities = func_get_args();
  $all_valid = true;
  foreach ($capabilities as $capability) {
    if (!$wp_test_expectations['user_capabilities'][$capability]) { $all_valid = false; break; }
  }
  return $all_valid;
}

/** Users **/

/**
 * Set the current user.
 * The user must have already been inserted via wp_insert_user.
 * @param int $id The ID of the user to set.
 * @param string $name The name of the user to set. Not currently used by MockPress.
 */
function wp_set_current_user($id, $name = '') {
  global $wp_test_expectations;

  $wp_test_expectations['current_user'] = (isset($wp_test_expectations['users'][$id]) ? $id : null);
}

/**
 * Get the current user.
 * The user must have already been inserted by wp_insert_user and set via wp_set_current_user.
 * @return WP_User|null The requested WP_User or null if not found.
 */
function wp_get_current_user() {
  global $wp_test_expectations;

  if (isset($wp_test_expectations['users'][$wp_test_expectations['current_user']])) {
    return $wp_test_expectations['users'][$wp_test_expectations['current_user']];
  } else {
    return null;
  }
}

/**
 * Insert a new user.
 * @param WP_User $userdata The userdata to insert.
 */
function wp_insert_user($userdata) {
  global $wp_test_expectations;

  if (!is_object($userdata)) { $userdata = (object)$userdata; }
  if (isset($userdata->ID)) {
    $id = $userdata->ID;
  } else {
    $id = max(array_keys($wp_test_expectations['users'])) + 1;
    $userdata->ID = $id;
  }
  $wp_test_expectations['users'][$id] = $userdata;
}

/**
 * Get a user's user data.
 * @param int $id The ID to retrieve.
 * @return WP_User|false The found user or false if not found.
 */
function get_userdata($id) {
  global $wp_test_expectations;

  if (isset($wp_test_expectations['users'][$id])) {
    return $wp_test_expectations['users'][$id];
  } else {
    return false;
  }
}

/**
 * Get meta data from a user.
 * If $key is empty(), return all the metadata values in an array.
 * @param int $id The ID to retrieve.
 * @param string $key The metadata key to retrieve.
 * @return mixed|boolean False if the user doesn't exist, otherwise the retrieved data.
 */
function get_usermeta($id, $key = '') {
  global $wp_test_expectations;

  if (isset($wp_test_expectations['user_meta'][$id])) {
    if (empty($key)) {
      return array_values($wp_test_expectations['user_meta'][$id]);
    } else {
      if (isset($wp_test_expectations['user_meta'][$id][$key])) {
        $data = $wp_test_expectations['user_meta'][$id][$key];
        return $data;
      } else {
        return '';
      }
    }
  } else {
    return false;
  }
}

/**
 * Create or update a user's meta data field.
 * @param int $id The ID to manage.
 * @param string $key The key to use.
 * @param mixed $value The value to insert. If this is empty(), delete the key.
 * @return boolean True if successful.
 * @todo Check to see if a blank meta key should be allowed, both here and in WP proper.
 */
function update_usermeta($id, $key, $value) {
  global $wp_test_expectations;

  if (!is_numeric($id)) { return false; }
  $key = preg_replace('#^[^a-z0-9_]#i', '', $key);

  if (!isset($wp_test_expectations['user_meta'][$id])) {
    $wp_test_expectations['user_meta'][$id] = array();
  }

  if (empty($value)) {
    unset($wp_test_expectations['user_meta'][$id][$key]);
  } else {
    $wp_test_expectations['user_meta'][$id][$key] = $value;
  }

  return true;
}

function delete_usermeta($id, $key) {
  global $wp_test_expectations;
  if (isset($wp_test_expectations['user_meta'][$id])) {
    unset($wp_test_expectations['user_meta'][$id][$key]);
  }
}

/**
 * Set the output of get_users_of_blog().
 * Doesn't handle multi-blog support (yet).
 * @param array $users The users to set.
 */
function _set_users_of_blog($users) {
  global $wp_test_expectations;

  foreach ($users as $user) {
    $user = (object)$user;
    if (isset($user->ID)) {
      $wp_test_expectations['users'][$user->ID] = $user;
    }
  }
}

/**
 * Get the users of the blog.
 * @param string $id The blog ID to search.
 * @return array The list of users.
 */
function get_users_of_blog($id = '') {
  global $wp_test_expectations;

  return array_values($wp_test_expectations['users']);
}

/**
 * Show the link to edit the current post.
 */
function edit_post_link() {}

/** WP_Error class **/

class WP_Error {}

/** WP_user class **/

class WP_User {
  var $data, $ID, $cap_key, $first_name, $last_name;
  var $caps = array();
}

/** WP_Widget class **/

class WP_Widget {
  function WP_Widget($id, $name, $widget_options = array(), $control_options = array()) {
    global $wp_test_expectations;
    $wp_test_expectations['wp_widgets'][$id] = compact('id', 'name', 'widget_options', 'widget_controls');
    $this->id = $id;
  }
  function widget($args, $instance) {}
  function update($new_instance, $old_instance) {}
  function form($instance) {}

  function get_field_id($field_name) { return "$id-$field_name"; }
  function get_field_name($field_name) { return "$id[$field_name]"; }
}

function register_widget() {}

function is_wp_error($object) {
  return (is_a($object, "WP_Error"));
}

// For use with SimpleXML

$_xml_cache = array();

/**
 * Convert a string to XML.
 * Additional conversion of HTML entities will be performed to make the string valid XML.
 * @param string $string The string to convert.
 * @param boolean $show_exception If true, show any parsing errors.
 * @return SimpleXMLElement|boolean The SimpleXMLElement of the string, or false if not valid XML.
 */
function _to_xml($string, $show_exception = false) {
  global $_xml_cache;

  $key = md5($string);
  if (!isset($_xml_cache[$key])) {
    try {
      $_xml_cache[$key] = new SimpleXMLElement("<x>" . str_replace(
                                                         array("&mdash;", "&nbsp;"),
                                                         array("--", " "),
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

/**
 * Test a SimpleXMLElement node for the provided XPath.
 * @param SimpleXMLElement $xml The node to check.
 * @param string $xpath The XPath to search for.
 * @param mixed $value Either a string that the XPath's value should match, true if the node simply needs to exist, or false if the node shouldn't exist.
 * @return boolen True if the XPath matches.
 */
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

/**
 * Return true if the node referred to by the provided XPath.
 * @param SimpleXMLElement $xml The node to check.
 * @param string $xpath The XPath to search for.
 * @return boolean True if the node exists.
 */
function _node_exists($xml, $xpath) {
  $result = $xml->xpath($xpath);
  if (is_array($result)) {
    return count($xml->xpath($xpath)) > 0;
  } else {
    return false;
  }
}

/**
 * Get the value of a node.
 * @param SimpleXMLElement $xml The node to check.
 * @param string $xpath The XPath to search for.
 * @return string|boolean The value of the node, or false if the node does not exist.
 */
function _get_node_value($xml, $xpath) {
  $result = $xml->xpath($xpath);
  if (is_array($result)) {
    return (count($result) > 0) ? trim((string)reset($result)) : null;
  } else {
    return false;
  }
}

/**
 * Wrap an XML string in an additional node.
 * @param string $string The XML string.
 * @return SimpleXMLElement An XML node.
 */
function _wrap_xml($string) {
  return new SimpleXMLElement("<x>" . $string . "</x>");
}

?>
