<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Bazzoo_Team {

	/**
	 * The single instance of Bazzoo_Team.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'bazzoo_team';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load frontend JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

		// Load API for generic admin functions
		if ( is_admin() ) {
			$this->admin = new Bazzoo_Team_Admin_API();
		}

		add_action( 'init', 'register_cpt_team_members' );
		// Create the Post Type
		function register_cpt_team_members() {

		    $labels = array( 
		        'name' => _x( 'Team Members', 'team_members' ),
		        'singular_name' => _x( 'Team Member', 'team_members' ),
		        'add_new' => _x( 'Add New', 'team_members' ),
		        'add_new_item' => _x( 'Add New Team Member', 'team_members' ),
		        'edit_item' => _x( 'Edit Team Member', 'team_members' ),
		        'new_item' => _x( 'New Team Member', 'team_members' ),
		        'view_item' => _x( 'View Team Member', 'team_members' ),
		        'search_items' => _x( 'Search Team Members', 'team_members' ),
		        'not_found' => _x( 'No team members found', 'team_members' ),
		        'not_found_in_trash' => _x( 'No team members found in Trash', 'team_members' ),
		        'parent_item_colon' => _x( 'Parent Team Member:', 'team_members' ),
		        'menu_name' => _x( 'Team Members', 'team_members' ),
		    );

		    $args = array( 
		        'labels' => $labels,
		        'hierarchical' => true,
		        
		        'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'trackbacks', 'custom-fields', 'revisions', 'page-attributes' ),
		        
		        'public' => true,
		        'show_ui' => true,
		        'show_in_menu' => true,
		        
		        
		        'show_in_nav_menus' => false,
		        'publicly_queryable' => true,
		        'exclude_from_search' => false,
		        'has_archive' => true,
		        'query_var' => true,
		        'can_export' => true,
		        'rewrite' => true,
		        'capability_type' => 'post'
		    );

		    register_post_type( 'team_members', $args );
		}

		add_action( 'load-post.php', 'smashing_post_meta_boxes_setup' );
        add_action( 'load-post-new.php', 'smashing_post_meta_boxes_setup' );

        /* Meta box setup function. */
        function smashing_post_meta_boxes_setup() {

          /* Add meta boxes on the 'add_meta_boxes' hook. */
          add_action( 'add_meta_boxes', 'smashing_add_post_meta_boxes' );

          /* Save post meta on the 'save_post' hook. */
          add_action( 'save_post', 'prfx_meta_save' );
        }

        /* Create one or more meta boxes to be displayed on the post editor screen. */
        function smashing_add_post_meta_boxes() {

          add_meta_box(
            'smashing-post-class',      // Unique ID
            esc_html__( 'Team Member Info', 'example' ),    // Title
            'smashing_post_class_meta_box',   // Callback function
            'team_members',         // Admin page (or post type)
            'side',         // Context
            'high'         // Priority
          );
        }

        /* Display the post meta box. */
		function smashing_post_class_meta_box( $object, $box ) { ?>

		  <?php wp_nonce_field( basename( __FILE__ ), 'smashing_post_class_nonce' ); ?>

		  <p>
		    <label for="qualifications"><?php _e( "Qualifications", 'qualificiations' ); ?></label>
		    <br />
		    <input class="widefat" type="text" name="qualifications" id="qualifications" value="<?php echo esc_attr( get_post_meta( $object->ID, 'qualifications', true ) ); ?>" size="30" />
		  </p>
		  <p>
		    <label for="email"><?php _e( "Email", 'email' ); ?></label>
		    <br />
		    <input class="widefat" type="text" name="email" id="email" value="<?php echo esc_attr( get_post_meta( $object->ID, 'email', true ) ); ?>" size="30" />
		  </p>
		<?php }

		function prfx_meta_save( $post_id ) {
 
    // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
 
    // Checks for input and sanitizes/saves if needed
    if( isset( $_POST[ 'qualifications' ] ) ) {
        update_post_meta( $post_id, 'qualifications', sanitize_text_field( $_POST[ 'qualifications' ] ) );
    }

    if( isset( $_POST[ 'email' ] ) ) {
        update_post_meta( $post_id, 'email', sanitize_text_field( $_POST[ 'email' ] ) );
    }
 
}

		function shortcode_func( $atts ){
			global $post;
			$posts = get_posts( array( 'post_type' => 'team_members', 'posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ) );
			if( $posts ):
			   foreach( $posts as $post ) :   
			    setup_postdata($post); ?>
			    	
			    	<?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );

			    	$qualifications = get_post_meta( get_the_ID(), 'qualifications', true );
			    	$email = get_post_meta( get_the_ID(), 'email', true );

			    	?>

					<div class="team-member">
						<div class="fusion-one-fourth one_fourth fusion-column">
							<div class="image">
								<img src="<?php echo $image[0]; ?>" alt="">
							</div>
						</div>
						<div class="fusion-three-fourth three_fourth fusion-column last">
							<div class="desc">
								<p class="name"><?php the_title(); ?></p>
								<p class="qual"><?php echo $qualifications; ?></p>
								<p class="email"><a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a></p>
								<p class="content"><?php the_content(); ?></p>
							</div>
						</div>
						<div class="clearfix"></div>
					</div>


			   <?php endforeach; 
			wp_reset_postdata(); 
			endif;
		}
		add_shortcode( 'bazzoo-team', 'shortcode_func' );

		// Handle localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );
	} // End __construct ()

	/**
	 * Wrapper function to register a new post type
	 * @param  string $post_type   Post type name
	 * @param  string $plural      Post type item plural name
	 * @param  string $single      Post type item single name
	 * @param  string $description Description of post type
	 * @return object              Post type class object
	 */
	public function register_post_type ( $post_type = '', $plural = '', $single = '', $description = '' ) {

		if ( ! $post_type || ! $plural || ! $single ) return;

		$post_type = new Bazzoo_Team_Post_Type( $post_type, $plural, $single, $description );

		return $post_type;
	}

	/**
	 * Wrapper function to register a new taxonomy
	 * @param  string $taxonomy   Taxonomy name
	 * @param  string $plural     Taxonomy single name
	 * @param  string $single     Taxonomy plural name
	 * @param  array  $post_types Post types to which this taxonomy applies
	 * @return object             Taxonomy class object
	 */
	public function register_taxonomy ( $taxonomy = '', $plural = '', $single = '', $post_types = array() ) {

		if ( ! $taxonomy || ! $plural || ! $single ) return;

		$taxonomy = new Bazzoo_Team_Taxonomy( $taxonomy, $plural, $single, $post_types );

		return $taxonomy;
	}

	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-frontend' );
	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-frontend' );
	} // End enqueue_scripts ()

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-admin' );
	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'bazzoo-team', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'bazzoo-team';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main Bazzoo_Team Instance
	 *
	 * Ensures only one instance of Bazzoo_Team is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Bazzoo_Team()
	 * @return Main Bazzoo_Team instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

}
