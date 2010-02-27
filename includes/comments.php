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

	$wp_test_expectations['comments'][] = compact(
		'comment_post_ID', 'comment_author', 'comment_author_email',
		'comment_author_url', 'comment_author_IP', 'comment_date',
		'comment_date_gmt', 'comment_content', 'comment_karma',
		'comment_approved', 'comment_agent', 'comment_type',
		'comment_parent', 'user_id'
	);
}
