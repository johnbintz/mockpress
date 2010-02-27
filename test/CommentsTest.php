<?php

require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__) . '/../includes/comments.php');

class CommentsTest extends PHPUnit_Framework_TestCase {
	function setUp() {
		_reset_wp();
	}

	function providerTestInsertComment() {
		return array(
			array(
				array(),
				array(
					'comment_author_IP' => '',
					'comment_date'      => true,
					'comment_date_gmt'  => true,
					'comment_parent'    => 0,
					'comment_approved'  => 1,
					'comment_karma'     => 0,
					'user_id'           => 0,
					'comment_type'      => ''
				)
			),
			array(
				array(
					'comment_author_IP' => 'ip',
					'comment_date'      => 'date',
					'comment_date_gmt'  => 'date-gmt',
					'comment_parent'    => 'parent',
					'comment_approved'  => 'approved',
					'comment_karma'     => 'karma',
					'user_id'           => 'id',
					'comment_type'      => 'type',
					'comment_post_ID'   => 'post',
					'comment_author'    => 'author',
					'comment_author_email' => 'email',
					'comment_author_url' => 'url',
					'comment_content'   => 'content',
					'comment_agent'     => 'agent',
					'bad'               => 'bad',
				),
				array(
					'comment_author_IP' => 'ip',
					'comment_date'      => 'date',
					'comment_date_gmt'  => 'date-gmt',
					'comment_parent'    => 'parent',
					'comment_approved'  => 'approved',
					'comment_karma'     => 'karma',
					'user_id'           => 'id',
					'comment_type'      => 'type',
					'comment_post_ID'   => 'post',
					'comment_author'    => 'author',
					'comment_author_email' => 'email',
					'comment_author_url' => 'url',
					'comment_content'   => 'content',
					'comment_agent'     => 'agent',
					'bad'								=> false
				)
			),
		);
	}

	/**
	 * @dataProvider providerTestInsertComment
	 */
	function testInsertComment($comment_data, $expected_comment) {
		global $wp_test_expectations;

		wp_insert_comment($comment_data);

		$this->assertEquals(1, count($wp_test_expectations['comments']));
		$comment = $wp_test_expectations['comments'][0];

		foreach ($expected_comment as $key => $value) {
			if ($value === true) {
				$this->assertTrue(!empty($comment[$key]));
			} elseif ($value === false) {
				$this->assertTrue(empty($comment[$key]));
			} else {
				$this->assertEquals($value, $comment[$key]);
			}
		}
	}
}
