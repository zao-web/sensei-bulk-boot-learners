<?php
namespace Zao\SenseiBulk_Boot_Learners;

class Admin {

	/**
	 * Run the init method on our sub-objects (on the init hook).
	 *
	 * @since  0.1.0
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'sensei_learners_main_column_data', array( $this, 'add_boot_button' ), 10, 2 );
		add_action( 'sensei_page_sensei_learners', array( $this, 'maybe_boot_from_course' ), 10, 2 );
		add_action( 'all_admin_notices', array( $this, 'show_boot_statuses' ), 10, 2 );
		add_action( 'wp_ajax_boot_from_course', array( $this, 'ajax_boot_from_course' ), 10, 2 );
	}

	/**
	 * Adds a boot button to the Sensei Learner Management area for courses.
	 *
	 * @since  0.1.0
	 *
	 * @param  array   $args Array of args from the row.
	 * @param  WP_Post $post The post object.
	 * @return array
	 */
	public function add_boot_button( $args, $post ) {
		if ( isset( $args['actions'], $post->ID ) ) {

			$boot_url = esc_url_raw( add_query_arg( 'boot-from', $post->ID, remove_query_arg( 'boot-from' ) ) );
			$label = apply_filters( 'senseiboot_button_label', __( 'Boot all learners', 'senseiboot' ) );
			$class = empty( $args['num_learners'] ) ? ' disabled' : '';
			$args['actions'] .= '  <a class="button senseiboot-button'. $class .'" data-bootfrom="'. absint( $post->ID ) .'" href="'. $boot_url .'">' . $label . '</a>';

			$this->enqueue();
		}

		return $args;
	}

	public function enqueue() {
		static $enqueued = false;

		if ( $enqueued ) {
			return;
		}

		$enqueued = true;
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style( 'senseiboot-admin', SENSEIBOOT_URL . "assets/css/sensei-bulk-boot-learners{$min}.css", array(), SENSEIBOOT_VERSION );
		wp_enqueue_script( 'senseiboot-admin', SENSEIBOOT_URL . "assets/js/sensei-bulk-boot-learners{$min}.js", array( 'jquery'	), SENSEIBOOT_VERSION, 1 );
		wp_localize_script( 'senseiboot-admin', 'SenseiBoot', array(
			'nonce'  => wp_create_nonce( SENSEIBOOT_URL ),
			'turtle' => defined( 'SENSEIBOOT_TURTLE' ) && SENSEIBOOT_TURTLE, // ?
			'l10n'   => array(
				'ajax_error' => __( 'Sorry, processing encountered an error and was stopped.', 'senseiboot' ),
				'boot_error' => __( 'Failed to boot all learners from the course.', 'senseiboot' ),
				'processing' => __( 'Processing %1$d of %2$d ', 'senseiboot' ),
				'success'    => __( 'Success, all learners successfully booted from the course.', 'senseiboot' ),
			),
		) );
	}

	public function maybe_boot_from_course() {
		if ( ! empty( $_REQUEST['boot-from'] ) ) {

			$course_id = absint( $_REQUEST['boot-from'] );
			$booted = Boot::learners_from_course( $course_id );

			$this->add_booted_status( $course_id, $booted );

			wp_redirect( remove_query_arg( 'boot-from' ) );
			exit;
		}
	}

	public function show_boot_statuses() {
		$statuses = $this->get_booted_statuses();

		if ( ! empty( $statuses[0] ) ) {
			echo '<div id="message" class="error"><p>' . sprintf( __( 'Failed to boot all learners from the course(s): <strong>%s</strong>', 'senseiboot' ), implode( ', ', array_map( 'get_the_title', array_keys( $statuses[0] ) ) ) ) . '</p></div>';
		}

		if ( ! empty( $statuses[1] ) ) {
			echo '<div id="message" class="updated"><p>' . sprintf( __( 'Success, all learners successfully booted from the course(s): <strong>%s</strong>', 'senseiboot' ), implode( ', ', array_map( 'get_the_title', array_keys( $statuses[1] ) ) ) ) . '</p></div>';
		}

		$this->delete_booted_statuses();
	}

	public function add_booted_status( $course_id, $success = true ) {
		$statuses = $this->get_booted_statuses();
		$statuses[ true === $success ? 1 : 0 ][ $course_id ] = $success;

		return update_option( 'senseiboot_success_message', $statuses );
	}

	public function get_booted_statuses() {
		$messages = get_option( 'senseiboot_success_message', array() );
		if ( ! is_array( $messages ) ) {
			$messages = array();
		}

		return $messages;
	}

	public function delete_booted_statuses() {
		return delete_option( 'senseiboot_success_message' );
	}

	public function ajax_boot_from_course() {
		if (
			empty( $_REQUEST['boot-from'] )
			|| empty( $_REQUEST['nonce'] )
			|| ! wp_verify_nonce( $_REQUEST['nonce'], SENSEIBOOT_URL )
		) {
			wp_send_json_error();
		}

		$course_id    = absint( $_REQUEST['boot-from'] );
		$to_process   = ! empty( $_REQUEST['to-process'] ) ? absint( $_REQUEST['to-process'] ) : 50;
		$total        = ! empty( $_REQUEST['total'] ) ? absint( $_REQUEST['total'] ) : 0;
		$processed    = ! empty( $_REQUEST['processed'] ) ? absint( $_REQUEST['processed'] ) : 0;
		$process_size = ! empty( $_REQUEST['process-size'] ) ? absint( $_REQUEST['process-size'] ) : 1;

		if ( $total ) {
			add_filter( 'sensei_learners_course_learners', array( $this, 'set_no_found_rows' ) );
		}

		$booted = Boot::learners_from_course( $course_id, $to_process );

		if ( true === $booted ) {
			if ( $total ) {
				$left = $total - $processed;
			} else {
				$found = Boot::last_query_found_comments();
				$left = $found > $to_process ? $found : false;
			}

			wp_send_json_success( $left );
		}

		wp_send_json_error( $booted );
	}

	public function set_no_found_rows( $args ) {
		$args['no_found_rows'] = true;
		return $args;
	}

}
