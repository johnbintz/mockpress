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

	function providerTestGetTheTitle() {
		return array(
			array(null, 'test'),
			array(1, 'test'),
			array(2, false),
			array((object)array('ID' => 1), 'test'),
		);
	}

	/**
	 * @dataProvider providerTestGetTheTitle
	 */
	function testGetTheTitle($post_to_use = null, $expected_title) {
		global $post;
		$post = (object)array('ID' => 1, 'post_title' => 'test');
		wp_insert_post($post);
		$this->assertEquals($expected_title, get_the_title($post_to_use));
	}

	function providerTestGetPost() {
		return array(
			array(null, null),
			array(1, (object)array('ID' => 1)),
			array(2, null),
			array((object)array('ID' => 1), (object)array('ID' => 1))
		);
	}

	/**
	 * @dataProvider providerTestGetPost
	 */
	function testGetPost($input, $expected_output) {
		wp_insert_post(array('ID' => 1));
		$this->assertEquals($expected_output, get_post($input));
	}

	function testDeleteCategory() {
		update_option('default_category', 1);
		add_category(1, (object)array('slug' => 'test'));
		add_category(2, (object)array('slug' => 'test-2'));

		$this->assertFalse(wp_delete_category(1));
		$this->assertTrue(wp_delete_category(2));

		$result = get_category(1);
		$this->assertTrue(isset($result->term_id));
		$result = get_category(2);
		$this->assertFalse(isset($result->term_id));
	}

	function testInsertCategory() {
		$result = get_category(1);
		$this->assertFalse(isset($result->term_id));

		wp_insert_category(array(
			'name' => 'go away',
			'description' => 'go away',
			'slug' => 'go away',
			'parent' => 'go away',
			'cat_name' => 'name',
			'category_description' => 'description',
			'category_nicename' => 'slug',
			'category_parent' => 'parent'
		));

		$result = get_category(1);
		$this->assertTrue(isset($result->term_id));

		foreach (array(
			'name' => 'name',
			'description' => 'description',
			'slug' => 'slug',
			'parent' => 'parent',
			'cat_name' => 'name',
			'category_description' => 'description',
			'category_nicename' => 'slug',
			'category_parent' => 'parent'
		) as $field => $value) {
			$this->assertEquals($value, $result->{$field});
		}
	}

	function providerTestUserTrailingSlashIt() {
		return array(
			array('', false),
			array('test', false),
			array('test/', true),
		);
	}

	/**
	 * @dataProvider providerTestUserTrailingSlashIt
	 */
	function testUserTrailingSlashIt($permalink_structure_option, $expecting_slash) {
		update_option('permalink_structure', $permalink_structure_option);

		$this->assertEquals($expecting_slash, preg_match('#/$#', user_trailingslashit('test')) > 0);
	}

	function providerTestWPParseArgs() {
		return array(
			array('', array('test' => 'test'), array('test' => 'test')),
			array(array(), array('test' => 'test'), array('test' => 'test')),
			array(array('test' => 'test2'), array('test' => 'test'), array('test' => 'test2')),
			array('test=test2', array('test' => 'test'), array('test' => 'test2')),
		);
	}

	/**
   * @dataProvider providerTestWPParseArgs
	 */
	function testWPParseArgs($input, $defaults, $expected_output) {
		$this->assertEquals($expected_output, wp_parse_args($input, $defaults));
	}

	function testGetTheID() {
		global $post;
		$post->ID = 10;

		$this->assertEquals(10, get_the_ID());
	}

	function providerTestCurrentUserCan() {
		return array(
			array(array('one'), true),
			array(array('two'), true),
			array(array('one', 'two'), true),
			array(array('one', 'three'), false),
			array(array('three'), false),
			array(array(), true),
		);
	}

	/**
	 * @dataProvider providerTestCurrentUserCan
	 */
	function testCurrentUserCan($capabilities, $expected_result) {
		_set_user_capabilities('one', 'two');

		$this->assertEquals($expected_result, call_user_func_array('current_user_can', $capabilities));
	}

	function testIsHome() {
		_set_current_option('is_home', true);
		$this->assertTrue(is_home());
	}

	function providerTestAddCategoryErrors() {
		return array(
			array('string', (object)array()),
			array(1, array())
		);
	}

	/**
	 * @dataProvider providerTestAddCategoryErrors
	 * @expectedException PHPUnit_Framework_Error
	 */
	function testAddCategoryErrors($id, $object) {
		add_category($id, $object);
	}

	function testGetCategoryBySlug() {
		add_category(1, (object)array('cat_name' => 'Test 123', 'category_nicename' => 'test-123'));
		add_category(2, (object)array('cat_name' => 'Test 234', 'category_nicename' => 'test-234'));

		$this->assertFalse(get_category_by_slug('test-345'));

		$category = get_category_by_slug('test-123');
		$this->assertEquals('Test 123', $category->name);
	}

	function testSetupPostData() {
		global $post;

		$post = "1";
		setup_postdata("2");

		$this->assertEquals("2", $post);
	}

	function testGetPermalink() {
		global $post;

		wp_insert_post((object)array(
			'ID' => 1,
			'guid' => 'post-1'
		));

		$this->assertEquals('post-1', get_permalink(1));
		$this->assertFalse(get_permalink(2));
		$this->assertEquals('post-1', get_permalink((object)array('ID' => 1)));
		$this->assertFalse(get_permalink((object)array('ID' => 2)));

		$post = get_post(1);

		$this->assertEquals('post-1', get_permalink());
	}

	function providerTestGetPostCategories() {
		return array(
			array(null, array(2)),
			array(1, array(2)),
			array(2, array(3)),
		);
	}

	/**
	 * @dataProvider providerTestGetPostCategories
	 */
	function testGetPostCategories($post_id, $expected_categories) {
		global $post;

		$post = (object)array('ID' => 1);

		wp_insert_post($post);
		wp_insert_post((object)array('ID' => 2));

		wp_set_post_categories(1, array(2));
		wp_set_post_categories(2, array(3));

		$this->assertEquals($expected_categories, wp_get_post_categories($post_id));
	}

    function testEnqueueScript() {
		wp_enqueue_script('test');
		$this->assertTrue(_did_wp_enqueue_script('test'));
    }

	function testEnqueueStyle() {
		global $wp_test_expectations;

		wp_enqueue_style('test');
		$this->assertTrue($wp_test_expectations['enqueued_styles']['test']);
	}

	function testWpmuValidateUserSignup() {
		$this->assertNull(wpmu_validate_user_signup("test", "user@example.com"));
	}

	function testWpmuCreateUser() {
		$this->assertTrue(is_int(wpmu_create_user(
			"foo",
			"bar",
			"user@example.com"
		)));
	}

	function testWpmuCreateBlog() {
		$this->assertTrue(is_int(wpmu_create_blog(
			"example.com",
			"path",
			"The Title",
			1
		)));
	}

	function testWpGeneratePassword() {
		$this->assertTrue(strlen(wp_generate_password()) > 0);
	}

	function testGetUserIdFromString() {
		_set_users_of_blog(array(
			array(
				'ID' => 1, 
				'user_login' => "admin",
				'user_email' => "user@example.com"
			),
		));

		$this->assertNull(get_user_id_from_string('non-user@example.com'));
		$this->assertEquals(get_user_id_from_string('user@example.com'), 1);
	}

	function testIsSubdomainInstall() {
		$this->assertFalse(is_subdomain_install());

		_set_multisite_configuration_type('subdomain');
		$this->assertTrue(is_subdomain_install());
	}
}
