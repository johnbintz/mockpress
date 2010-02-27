<?php

function wp_insert_comment($comment_data) {
	global $wp_test_expectations;

	extract($comment_data);

	// taken straight from WPMU 2.9.1.1 source...
	if ( ! isset($comment_author_IP) )
		$comment_author_IP = '';
	if ( ! isset($comment_date) )
		$comment_date = time();
	if ( ! isset($comment_date_gmt) )
		$comment_date_gmt = time();
	if ( ! isset($comment_parent) )
		$comment_parent = 0;
	if ( ! isset($comment_approved) )
		$comment_approved = 1;
	if ( ! isset($comment_karma) )
		$comment_karma = 0;
	if ( ! isset($user_id) )
		$user_id = 0;
	if ( ! isset($comment_type) )
		$comment_type = '';

	$id = count($wp_test_expectations['comments']) + 1;

	$wp_test_expectations['comments'][$id] = compact(
		'comment_post_ID', 'comment_author', 'comment_author_email',
		'comment_author_url', 'comment_author_IP', 'comment_date',
		'comment_date_gmt', 'comment_content', 'comment_karma',
		'comment_approved', 'comment_agent', 'comment_type',
		'comment_parent', 'user_id'
	);

	return $id;
}

function wp_new_comment($commentdata) {
	// taken straight from WPMU 2.9.11 source...
	$commentdata['comment_post_ID'] = (int) $commentdata['comment_post_ID'];
	if ( isset($commentdata['user_ID']) )
		$commentdata['user_id'] = $commentdata['user_ID'] = (int) $commentdata['user_ID'];
	elseif ( isset($commentdata['user_id']) )
		$commentdata['user_id'] = (int) $commentdata['user_id'];

	if (isset($_SERVER['REMOTE_ADDR'])) {
		$commentdata['comment_author_IP'] = preg_replace('/[^0-9a-fA-F:., ]/', '', $_SERVER['REMOTE_ADDR']);
	}

	if (isset($_SERVER['HTTP_USER_AGENT'])) {
		$commentdata['comment_agent'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 254);
	}

	return wp_insert_comment($commentdata);
}

function get_comment($comment) {
	global $wp_test_expectations;

	if (is_numeric($comment)) {
		if (isset($wp_test_expectations['comments'][$comment])) {
			return $wp_test_expectations['comments'][$comment];
		} else {
			return false;
		}
	}
}
