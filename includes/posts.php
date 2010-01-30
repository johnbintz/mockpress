<?php


/** Posts **/

/**
 * Set the respone for get_posts()
 * @param $query The query to expect.
 * @param $result The posts to return for this query.
 */
function _set_up_get_posts_response($query, $result) {
  global $wp_test_expectations;
  if (!is_string($query)) { $query = serialize($query); }
  $wp_test_expectations['get_posts'][$query] = $result;
}

/**
 * Retrieve posts from the WordPress database.
 * @param string $query The query to use against the database.
 * @return array The posts that match the query.
 */
function get_posts($query) {
  global $wp_test_expectations;
  if (!is_string($query)) { $query = serialize($query); }

  if (isset($wp_test_expectations['get_posts'][$query])) {
    return $wp_test_expectations['get_posts'][$query];
  } else {
    return array();
  }
}

/**
 * Insert a post into the database.
 * @param array $array The post information.
 * @return int The post ID.
 */
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

/**
 * Update a post in the database.
 * @param array|object $post The post to udate.
 */
function wp_update_post($object) {
  global $wp_test_expectations;
  if (is_array($object)) { $object = (object)$object; }

  if (isset($wp_test_expectations['posts'][$object->ID])) {
    $wp_test_expectations['posts'][$object->ID] = $object;
  }
}

/**
 * Delete a post and all of its associated metadata.
 * @param int $id The post ID
 * @return object|boolean The deleted post, or false on error.
 */
function wp_delete_post($id) {
  global $wp_test_expectations;

  if (isset($wp_test_expectations['posts'][$id])) {
  	$post = $wp_test_expectations['posts'][$id];
  	unset($wp_test_expectations['posts'][$id]);

  	if (isset($wp_test_expectations['post_meta'][$id])) {
  		unset($wp_test_expectations['post_meta'][$id]);
  	}
  	return $post;
  }
  return false;
}

/**
 * Get a post from the database.
 * @param int $id The post to retrieve.
 * @param string $output
 * @return object|null The post or null if not found.
 */
function get_post($id, $output = "") {
  global $wp_test_expectations;

  if (is_object($id)) {
  	if (isset($id->ID)) { $id = $id->ID; }
  }

  if (isset($wp_test_expectations['posts'][$id])) {
    return $wp_test_expectations['posts'][$id];
  } else {
    return null;
  }
}

/**
 * Set up post data in a variety of global variables.
 * @param object $post A post to set up.
 */
function setup_postdata($p) {
	global $post;
	$post = $p;
}

/**
 * Update a post's custom field.
 * @param int $post_id The post's ID.
 * @param string $field The field to set.
 * @param string $value The value to set the field to.
 */
function update_post_meta($post_id, $field, $value) {
  global $wp_test_expectations;
  if (!isset($wp_test_expectations['post_meta'][$post_id])) {
    $wp_test_expectations['post_meta'][$post_id] = array();
  }
  $wp_test_expectations['post_meta'][$post_id][$field] = $value;
}

/**
 * Get the value of a post's custom field.
 * @param int $post_id The post's ID.
 * @param string $field The field to get.
 * @param boolean $single If true, return the first field's value.
 * @return string|array The first value, or all the values that are associated with this field.
 */
function get_post_meta($post_id, $field, $single = false) {
  global $wp_test_expectations;

  if (!isset($wp_test_expectations['post_meta'][$post_id])) { return ""; }
  if (!isset($wp_test_expectations['post_meta'][$post_id][$field])) { return ""; }

  return ($single) ? $wp_test_expectations['post_meta'][$post_id][$field] : array($wp_test_expectations['post_meta'][$post_id][$field]);
}

/**
 * Delete a post's custom field.
 * @param int $post_id The post's ID.
 * @param string $field The field to get.
 * @return string|array The first value, or all the values that are associated with this field.
 */
function delete_post_meta($post_id, $field, $value = '') {
  global $wp_test_expectations;

  if (!isset($wp_test_expectations['post_meta'][$post_id])) { return false; }
  if (!isset($wp_test_expectations['post_meta'][$post_id][$field])) { return false; }

  unset($wp_test_expectations['post_meta'][$post_id][$field]);
  return true;
}

/**
 * Check if a post exists by matching title, content, and date.
 * @param string $title The title to search for.
 * @param string $content The content to search for.
 * @param mixed $date The date to search for.
 * @return int The ID of the matching post, or 0 if none is found.
 */
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

/**
 * Get the permalink to the provided post.
 * @param object $post The post object, ID, or 0 to use the current global $post.
 * @return string The permalink to the post.
 */
function get_permalink($p = 0) {
	global $post;
	if (empty($p)) {
		$target_post = $post;
	} else {
		$target_post = get_post($p);
	}
	if (is_object($target_post)) {
		if (isset($target_post->guid)) {
	  	return $target_post->guid;
		}
	}
	return false;
}

/**
 * Set up a call to get_children()
 * @param array $options The options that should be matched.
 * @param array $children The child posts to return.
 */
function _set_get_children($options, $children) {
  global $wp_test_expectations;
  $wp_test_expectations['children'][md5(serialize($options))] = $children;
}

/**
 * Get the children of a parent post.
 * @param $options The options to match child posts against.
 * @return array The matching child posts.
 */
function get_children($options) {
  global $wp_test_expectations;
  return $wp_test_expectations['children'][md5(serialize($options))];
}
