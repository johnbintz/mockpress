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

	function testGetAllOptions() {
		update_option('test', 'value');
		$this->assertEquals(array('test' => 'value'), get_alloptions());
	}

	function providerTestGetOption() {
		return array(
			array(false, false),
			array(array('test'), false),
			array('test', 'test2')
		);
	}

	/**
	 * @dataProvider providerTestGetOption
	 */
	function testGetOption($key, $expected_value) {
		global $wp_test_expectations;

		$wp_test_expectations['options'] = array('test' => 'test2');

		$this->assertEquals($expected_value, get_option($key));
	}
}
