<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://socialsquare.dk
 * @since      1.0.0
 *
 * @package    Dac_Content_Hub
 * @subpackage Dac_Content_Hub/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Dac_Content_Hub
 * @subpackage Dac_Content_Hub/public
 * @author     Kræn Hansen <kraen@socialsquare.dk>
 */
class Dac_Content_Hub_Public {

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
	 * The helper used when communicating with the prismic CMS.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Prismic_Helper    $prismic    The prismic helper
	 */
	private $prismic;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		require_once plugin_dir_path( __FILE__ ) . '../includes/class-prismic-helper.php';
		$this->prismic = new Prismic_Helper();

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/dac-content-hub-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/dac-content-hub-public.js', array( 'jquery' ), $this->version, false );

	}

	public function add_query_vars( $vars ) {
		$vars[] = "content_type";
		$vars[] = "content_uid";
		return $vars;
	}

	public function add_rewrite_rules() {
		// @see https://codex.wordpress.org/Rewrite_API/add_rewrite_rule
		add_rewrite_rule('^content/([a-z1-9\-_]+)/([a-z1-9\-_]+)/?',
										 'index.php?content_type=$matches[1]&content_uid=$matches[2]',
										 'top');
	}

	public function posts_pre_query($return, WP_Query $query) {
		$has_content_type = array_key_exists('content_type', $query->query_vars);
		$has_content_uid = array_key_exists('content_uid', $query->query_vars);
		if($has_content_type && $has_content_uid) {
			$content_type = $query->query_vars['content_type'];
			$content_uid = $query->query_vars['content_uid'];

			$api = $this->prismic->get_api();
		  $content = $api->getByUID($content_type, $content_uid);
			$content_object = (object) $this->post_data_from_content($content);

			$query->queried_object = $content_object;
			$query->queried_object_id = $content->getUID();
			// $query->is_page = true;
			$query->is_single = true;

			return array(
				$content_object
			);
		} else {
			return $return;
		}
	}

	public function post_data_from_content($content) {
		$type = $content->getType();
		$resolver = $this->prismic->linkResolver;

		if($type === 'case') {
			$first_publication = $content->getFirstPublicationDate();
			$last_publication = $content->getLastPublicationDate();
			$short_description = $content->getStructuredText('case.short-description');
			// The ID needs to be faked to trick get_metadata into returning metadata
			return array(
				'ID' => PHP_INT_MAX,
				'post_type' => $type,
				'post_title' => $content->getStructuredText('case.title')->asText(),
				'post_name' => $content->getUID(),
				'post_date' => $first_publication ? $first_publication->format('Y-m-d H:i:s') : null,
				'post_modified' => $last_publication ? $last_publication->format('Y-m-d H:i:s') : null,
				'post_excerpt' => $short_description ? $short_description->asHtml($resolver) : '',
				'post_content' => $content->getStructuredText('case.description')->asHtml($resolver),
				'content' => $content
			);
		} else {
			throw new Error('Unexpected content-type: ' . $type);
		}
	}

	public function get_post_metadata($value, $object_id, $meta_key, $single) {
		$post = get_post();
		if($meta_key === '_thumbnail_id' && $post->post_type === 'case') {
			return true;
		} else {
			return $value;
		}
	}

	// post_thumbnail_html

	public function post_thumbnail_html($html, $post_id, $post_thumbnail_id, $size, $attr) {
		$post = get_post();
		if($post->post_type === 'case') {
			$pictures = $post->content->getGroup('case.pictures')->getArray();
			$first_picture = array_shift($pictures);
			return $first_picture->getImage('picture')->asHtml();
		} else {
			return $html;
		}
	}

	public static function clean_query_string($query_string) {
		$query_string = str_replace('&#8220;', '"', $query_string);
		$query_string = str_replace('&#8221;', '"', $query_string);
		return trim(strip_tags($query_string));
	}

	public static function overlay_html($inside_html) {
		return '<div class="dac-collage__overlay">' . $inside_html . '</div>';
	}

	public static function collage_item_from_content($content, $cols, $height) {
		$type = $content->getType();
		if($type === 'case') {
			$href = '/content/case/' . $content->getSlug();
			$pictures = $content->getGroup('case.pictures')->getArray();
			$first_picture = array_shift($pictures);
			$background_url = $first_picture->getImage('picture')->getUrl();

			$style = 'width:' . 100 / $cols . '%;';
			$style .= 'height:' . $height . ';';
			$style .= 'background-image:url(' . $background_url . ');';

			$inside_html = $content->getStructuredText('case.title')->asText();
			$overlay_html = self::overlay_html($inside_html);

			return '<a href="' . $href . '" style="' . $style . '" class="dac-collage__item">' . $overlay_html . '</a>';
		} else {
			throw new Error('Unexpected content-type: ' . $type);
		}
	}

	public function add_shortcodes() {
		add_shortcode('content-collage', array($this, 'content_collage_shortcode'));
	}

	public function content_collage_shortcode($atts = [], $content = null, $cols = 3) {
		$atts = shortcode_atts(
			array(
				'cols' => 3,
				'item-height' => '150px',
				'full-width' => false
			),
			$atts,
			'content-collage'
		);

		$api = $this->prismic->get_api();
		$query_string = self::clean_query_string($content);
		$response = $api->query($query_string);
		$result = '';
		foreach($response->getResults() as $doc) {
			$result .= self::collage_item_from_content($doc, $atts['cols'], $atts['item-height']);
		}
		$collage_classes = 'dac-collage';
		if($atts['full-width'] !== false) {
			$collage_classes .= ' dac-collage--full-width';
		}
		return '<div class="' . $collage_classes . '">' . $result . '</div>';
	}

}
