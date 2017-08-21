<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://socialsquare.dk
 * @since      1.0.0
 *
 * @package    Dac_Content_Hub
 * @subpackage Dac_Content_Hub/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Dac_Content_Hub
 * @subpackage Dac_Content_Hub/admin
 * @author     Kræn Hansen <kraen@socialsquare.dk>
 */
class Dac_Content_Hub_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Dac_Content_Hub_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Dac_Content_Hub_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/dac-content-hub-admin.css', [], $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Dac_Content_Hub_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Dac_Content_Hub_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/dac-content-hub-admin.js', [ 'jquery' ], $this->version, false );
		wp_localize_script( $this->plugin_name, 'plugin_dir', plugin_dir_url( __FILE__ ) );
	}

	public function dac_register_shortcode_button( $buttons ) {
		array_push( $buttons, '|', 'dac_content_hub' );
		return $buttons;
	}

	public function dac_add_shortcode_button( $plugin_array ) {
		$plugin_array['dac_shortcode'] = plugins_url( '/js/dac.prismic-shortcode.js', __FILE__ );
		return $plugin_array;
	 }

}
