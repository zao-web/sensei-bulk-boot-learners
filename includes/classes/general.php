<?php
namespace Zao\SenseiBulk_Boot_Learners;

class General {

	/**
	 * General
	 *
	 * @var General
	 */
	protected static $single_instance = null;

	/**
	 * Admin
	 *
	 * @var Admin
	 */
	protected $admin = null;

	/**
	 * Creates or returns an instance of this class.
	 * @since  0.1.0
	 * @return General A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Initiate our sub-objects.
	 *
	 * @since 0.1.0
	 */
	protected function __construct() {
		if ( is_admin() ) {
			$this->admin = new Admin;
		}

		if ( isset( $_REQUEST['action'] ) && 'boot_from_course' === $_REQUEST['action'] ) {
			Admin::ajax_boot_from_course();
		}
	}

	/**
	 * Run the init method on our sub-objects (on the init hook).
	 *
	 * @since  0.1.0
	 *
	 * @return void
	 */
	public function init() {
		if ( is_admin() ) {
			$this->admin->init();
		}

	}

	/**
	 * Magic getter for our object.
	 * @param string $property
	 * @return mixed
	 */
	public function __get( $property ) {
		return $this->{$property};
	}
}
