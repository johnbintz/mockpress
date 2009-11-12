<?php

require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__) . '/../mockpress.php');

class MockPressTest extends PHPUnit_Framework_TestCase {
	function setUp() {
		_reset_wp();
	}

	function testGetPages() {
		wp_insert_post((object)array('ID' => 1, 'post_type' => 'post'));
		wp_insert_post((object)array('ID' => 2, 'post_type' => 'page'));

		$this->assertEquals(array((object)array('ID' => 2, 'post_type' => 'page')), get_pages());
	}
}