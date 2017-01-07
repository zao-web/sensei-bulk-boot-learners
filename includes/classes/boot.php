<?php
namespace Zao\SenseiBulk_Boot_Learners;
use Sensei_Utils;

class Boot {
	protected static $args = array();
	protected static $comment_query = null;

	/**
	 * Boot all users from a course, deleting all their activities across all lessons.
	 *
	 * @since  0.1.0
	 *
	 * @param  int|WP_Post $course_id Course ID or Post object
	 * @param  int         $number    The number of learners to fetch/remove. Default, all.
	 *
	 * @return bool|array bool true if successful, false if bad $course_id, or array of learner ids if failed.
	 */
	public static function learners_from_course( $course_id, $number = -1 ) {
		$post = isset( $course_id->ID ) ? $course_id : get_post( $course_id );

		if ( ! $post || ! isset( $post->post_type ) || 'course' !== $post->post_type ) {
			return false;
		}

		if ( 0 === $number ) {
			return false;
		}

		self::$args = array(
			'post_id' => $post->ID,
			'type'    => 'sensei_course_status',
			'status'  => 'any',
		);

		if ( -1 !== $number && absint( $number ) ) {
			self::$args['number'] = absint( $number );
			self::$args['no_found_rows'] = false;
		}

		add_filter( 'pre_get_comments', array( __CLASS__, 'store_query' ) );
		$learners = Sensei_Utils::sensei_check_for_activity( apply_filters( 'sensei_learners_course_learners', self::$args ), true );
		remove_filter( 'pre_get_comments', array( __CLASS__, 'store_query' ) );

		if ( isset( $learners->user_id ) ) {
			$learners = array( $learners );
		}

		return self::boot_users_from_course( wp_list_pluck( $learners, 'user_id' ), $post->ID );
	}

	public static function store_query( $comment_query) {
		self::$comment_query = $comment_query;
	}

	protected static function boot_users_from_course( $learners, $course_id ) {
		$not_booted = array();

		foreach ( $learners as $user_id ) {
			// Remove all course user meta
			if ( ! Sensei_Utils::sensei_remove_user_from_course( $course_id, $user_id ) ) {
				$not_booted[] = $user_id;
			}
		}

		return empty( $not_booted ) ? true : $not_booted;
	}

	public static function last_query_found_comments() {
		return self::$comment_query->found_comments;
	}

	public static function last_query() {
		return self::$comment_query;
	}

	public static function last_query_args() {
		return self::$args;
	}

}
