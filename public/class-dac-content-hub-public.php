<?php
/**
 * The public-facing functionality of the plugin.
 *
 *  @link       http://socialsquare.dk
 *  @since      1.0.0
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
	 * Base path.
	 *
	 * @var string
	 */
	private $base_path = 'content';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
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
	 * @since 1.0.0
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
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/dac-content-hub-public.css', [], $this->version, 'all' );
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/dac-content-hub-public.js', [ 'jquery' ], $this->version, false );

	}

	/**
	 * URL query variables.
	 *
	 * @param array $vars Query variables.
	 *
	 * @return array $vars
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'content_type';
		$vars[] = 'content_uid';
		return $vars;
	}

	/**
	 * Add rewrite rules to WordPress.
	 *
	 * @return void
	 */
	public function add_rewrite_rules() {
		// @see https://codex.wordpress.org/Rewrite_API/add_rewrite_rule
		$regex = "^$this->base_path/([a-z1-9\-_]+)/([a-z1-9\-_]+)/?";
		$redirect = 'index.php?content_type=$matches[0]&content_uid=$matches[1]';
		$after = 'top';
		add_rewrite_rule( $regex, $redirect, $after );
	}

	/**
	 * Hijack wordpress queries.
	 *
	 */
	public function posts_pre_query( $return, WP_Query $query ) {
		$has_content_type = array_key_exists( 'content_type', $query->query_vars );
		$has_content_uid = array_key_exists( 'content_uid', $query->query_vars );

		if ( $has_content_type && $has_content_uid ) {
			$content_type = $query->query_vars['content_type'];
			$content_uid = $query->query_vars['content_uid'];

			$api = $this->prismic->get_api();
			$content = $api->getByUID( $content_type, $content_uid );
			$content_object = (object) $this->post_data_from_content( $content );

			$query->queried_object = $content_object;
			$query->queried_object_id = $content->getUID();
			$query->is_page = true;
			$query->is_single = true;

			return [
				$content_object,
			];
		}
	}

	/**
	 * Thumbnail output.
	 */
	public function post_thumbnail_html($html, $post_id, $post_thumbnail_id, $size, $attr) {
		$post = get_post();
		if($post->post_type === 'case') {
			$pictures = $post->content->getGroup('case.pictures')->getArray();
		}
	}

	/**
	 * Hijack a WordPress page.
	 */
	private function post_data_from_content( $content ) {
		$type = $content->getType();
		$resolver = $this->prismic->link_resolver;

		if ( 'case' === $type ) {
			$first_publication = $content->getFirstPublicationDate();
			$last_publication = $content->getLastPublicationDate();
			$short_description = $content->getStructuredText( 'case.short-description' );
			// The ID needs to be faked to trick get_metadata into returning metadata.
			return [
				'ID' => PHP_INT_MAX,
				'post_type' => $type,
				'post_title' => $content->getText( 'case.title' ),
				'post_name' => $content->getUID(),
				'post_date' => $first_publication ? $first_publication->format( 'Y-m-d H:i:s' ) : null,
				'post_modified' => $last_publication ? $last_publication->format( 'Y-m-d H:i:s' ) : null,
				'post_excerpt' => $short_description ? $short_description->asHtml( $resolver ) : '',
				'post_content' => $content->getStructuredText( 'case.description' )->asHtml( $resolver ),
				'content' => $content,
			];
		} else {
			throw new Error( "Unexpected content-type: $type" );
		}
	}

	public function get_post_metadata( $value, $object_id, $meta_key, $single ) {
		$post = get_post();
		if ( '_thumbnail_id' === $meta_key && 'case' === $post->post_type ) {
			return true;
		} else {
			return $value;
		}
	}

	/**
	 * Build a content link.
	 *
	 * @param string $type Content type.
	 * @param string $urn  Content url.
	 */
	public function generate_content_link( $type, $urn ) {
		return home_url( "/$this->base_path/$type/$urn" );
	}

	/**
	 * Build post link.
	 */
	public function post_link( $permalink, $post, $leavename ) {
		if ( property_exists( $post, 'content' ) ) {
			// We assume it's from the content hub.
			return $this->generate_content_link( $post->post_type, $post->post_name );
		}
		return $permalink;
	}

	/**
	 * Twig powered data formatter.
	 *
	 * @param object $doc Prismic document.
	 */
	public function dac_format_data( $doc ) {
		$type = $doc->getType();
		// Use twig for templating.
		$loader = new Twig_Loader_Filesystem( plugin_dir_path( __FILE__ ) . 'templates' );
		$twig = new Twig_Environment( $loader );
		// Prismic link resolver.
		$resolver = $this->prismic->link_resolver;
		// Generate url.
		$href = $this->generate_content_link( $type, $doc->getSlug() );
		// Decide template per content type.
		$template = '';
		$context = [];
		switch ( $type ) {
			case 'case':
				// Teaser template file.
				$template = 'case--teaser.html.twig';
				// Content.
				$images = $doc->getGroup( 'case.pictures' )->getArray();
				$image_attributes = array_map(function ( $image ) {
					return [
						'src' => $image->getImage( 'picture' )->getUrl(),
						'alt' => $image->getImage( 'picture' )->getAlt(),
						'width' => $image->getImage( 'picture' )->getWidth(),
						'height' => $image->getImage( 'picture' )->getHeight(),
					];
				}, $images);
				$image_attributes_first = array_shift( $image_attributes );
				$context = [
					'title' => $doc->getText( 'case.title' ),
					'image' => $image_attributes_first,
					'teaser_text' => $doc->getStructuredText( 'case.short-description' )->asHtml( $resolver ),
					'href' => $href,
				];
				break;
		}
		if ( ! empty( $template ) && ! empty( $context ) ) {
			return $twig->render( $template, $context );
		}
	}

	/**
	 * Initialize shortcode.
	 */
	public function add_shortcodes() {
		add_shortcode( 'content-hub', [ $this, 'dac_shortcode' ] );
	}

	/**
	 * Shortcode callback.
	 *
	 * @param array $attributes Shortcode attributes.
	 */
	public function dac_shortcode( $attributes = [] ) {
		$attributes = shortcode_atts(
			[
				// Default to case only.
				'type' => 'case',
				'limit' => null,
				'view_mode' => null,
				'organisation' => null,
				'tags' => [],
				'case_category' => null,
				'case_area' => null,
				'id' => null,
				'uid' => null,
				'build_year' => null,
			],
			$attributes,
			'content-hub'
		);

		$response = $this->prismic->query( $attributes );
		$result = '';
		foreach ( $response->getResults() as $doc ) {
			$result .= $this->dac_format_data( $doc );
		}
		return $result;
	}

}
