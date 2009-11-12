<?php

require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__) . '/../mockpress.php');

class MockPressTest extends PHPUnit_Framework_TestCase {
	function setUp() {
		global $post;
		_reset_wp();
		unset($post);
	}

	function testGetPages() {
		wp_insert_post((object)array('ID' => 1, 'post_type' => 'post'));
		wp_insert_post((object)array('ID' => 2, 'post_type' => 'page'));

		$this->assertEquals(array((object)array('ID' => 2, 'post_type' => 'page')), get_pages());
	}

	function testGetAllOptions() {
		update_option('test', 'value');
		$this->assertEquals(array('test' => 'value'), get_alloptions());
	}

	function providerTestIsPage() {
		return array(
			array(false, false),
			array((object)array('post_type' => 'post'), false),
			array((object)array('post_type' => 'page'), true),
		);
	}

	/**
	 * @dataProvider providerTestIsPage
	 */
	function testIsPage($p, $expected_result) {
		global $post;

		$post = $p;
		$this->assertEquals($expected_result, is_page());
	}
}
