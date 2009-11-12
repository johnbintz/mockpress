<?php

/** Caching functions **/

class WP_Object_Cache {
  var $cache = array();

  function _ensure_group($group) {
    return empty($group) ? 'default' : $group;
  }

  function add($id, $data, $group = 'default', $expire = '') {
    $group = $this->_ensure_group($group);

    if ($this->get($id, $group) !== false) { return false; }
    return $this->set($id, $data, $group, $expire);
  }

  function delete($id, $group = 'default', $force = false) {
    $group = $this->_ensure_group($group);

    if (($this->get($id, $group) === false) && !$force) { return false; }

    unset($this->cache[$group][$key]);
    return true;
  }

  function flush() { $this->cache = array(); return true; }

  function get($id, $group = 'default') {
    $group = $this->_ensure_group($group);

    if (isset($this->cache[$group][$id])) {
      $return = $this->cache[$group][$id];
      return is_object($return) ? wp_clone($return) : $return;
    }

    return false;
  }

  function replace($id, $data, $group = 'default', $expire = '') {
    $group = $this->_ensure_group($group);

    if ($this->get($id, $group) === false) { return false; }

    return $this->set($id, $data, $group, $expire);
  }

  function set($id, $data, $group = 'default', $expire = '') {
    $group = $this->_ensure_group($group);

    $data = is_null($data) ? '' : $data;

    if (is_object($data)) { $data = wp_clone($data); }

    if (!isset($this->cache[$group])) { $this->cache[$group] = array(); }
    $this->cache[$group][$id] = $data;

    return true;
  }
}

function wp_cache_add($id, $data, $group = '', $expire = 0) {
  global $wp_object_cache;
  return $wp_object_cache->add($id, $data, $group, $expire);
}

function wp_cache_delete($id, $group = '') {
  global $wp_object_cache;
  return $wp_object_cache->delete($id, $group);
}

function wp_cache_flush() {
  global $wp_object_cache;
  return $wp_object_cache->flush();
}

function wp_cache_get($id, $group = '') {
  global $wp_object_cache;
  return $wp_object_cache->get($id, $group);
}

function wp_cache_init() {
  $GLOBALS['wp_object_cache'] = new WP_Object_Cache();
}

function wp_cache_replace($id, $data, $group = '', $expire = 0) {
  global $wp_object_cache;
  $wp_object_cache->replace($id, $data, $group, $expire);
}

function wp_cache_set($id, $data, $group = '', $expire = 0) {
  global $wp_object_cache;
  return $wp_object_cache->set($id, $data, $group, $expire);
}

?>
