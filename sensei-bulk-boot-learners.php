<?php
/**
 * Plugin Name: Sensei Bulk Boot Learners
 * Plugin URI:  http://zao.is
 * Description: Provides tools/buttons for bulk booting users from courses
 * Version:     0.1.0
 * Author:      Justin Sternberg
 * Author URI:  http://zao.is
 * Text Domain: senseiboot
 * Domain Path: /languages
 * License:     GPL-2.0+
 */

/**
 * Copyright (c) 2017 Justin Sternberg (email : jt@zao.is)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Built using yo wp-make:plugin
 * Copyright (c) 2017 10up, LLC
 * https://github.com/10up/generator-wp-make
 */

// Useful global constants
define( 'SENSEIBOOT_VERSION', '0.1.0.1' );
define( 'SENSEIBOOT_URL',     plugin_dir_url( __FILE__ ) );
define( 'SENSEIBOOT_PATH',    dirname( __FILE__ ) . '/' );
define( 'SENSEIBOOT_INC',     SENSEIBOOT_PATH . 'includes/' );

// Include files
require_once SENSEIBOOT_INC . 'functions/core.php';

// Bootstrap
Zao\SenseiBulk_Boot_Learners\setup();
