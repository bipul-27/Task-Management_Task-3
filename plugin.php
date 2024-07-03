<?php
/*
Plugin Name: Project Management
Plugin URI: 
Description: A plugin to manage project
Version: 1.0.0
Author: Bipul
AUTHOR URI: https://www.bipul.com
Requires at least: 5.0  
Tested up to: 5.7  
Stable tag: 1.0.0  
License: GPLv2  
License URI: https://www.gnu.org/licenses/gpl-2.0.html  

*/


if ( !defined( 'ABSPATH' ) ) exit;


class Fluent_Features_board {

   
    public $version = '1.0.0';

    private $container = array();
    public $columns = array();

    public $fluent_features_board = 'fluent_features_board';
    public $ff_requests_list = 'ff_requests_list';
    public $ffr_tags = 'ffr_tags';
    public $ffr_comments = 'ffr_comments';
    public $ffr_votes = 'ffr_votes';

   
    public function __construct() {

        $this->define_constants();

        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        add_action( 'wp_enqueue_scripts', [$this, 'ffb_frontend_scripts'] );
        add_action( 'admin_enqueue_scripts', [$this, 'ffb_admin_scripts'] );
        add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
		add_action( 'set_logged_in_cookie', [$this, 'ffb_loggedin_cookie'] );
        add_action( 'wp_ajax_fluent_features_board_ajaxlogin', [$this, 'fluent_features_board_ajaxlogin'] );
        add_action( 'wp_ajax_nopriv_fluent_features_board_ajaxlogin', [$this, 'fluent_features_board_ajaxlogin'] );

        add_action( 'wp_ajax_fluent_features_board_ajaxregister', [$this, 'fluent_features_board_ajaxregister'] );
        add_action( 'wp_ajax_nopriv_fluent_features_board_ajaxregister', [$this, 'fluent_features_board_ajaxregister'] );
    }

   
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new Fluent_Features_board();
        }

        return $instance;
    }

    
    public function __get( $prop ) {
        if ( array_key_exists( $prop, $this->container ) ) {
            return $this->container[ $prop ];
        }

        return $this->{$prop};
    }

    
    public function __isset( $prop ) {
        return isset( $this->{$prop} ) || isset( $this->container[ $prop ] );
    }

    
    public function define_constants() {
        define( 'FFB_VERSION', $this->version );
        define( 'FFB_FILE', __FILE__ );
        define( 'FFB_PATH', dirname( FFB_FILE ) );
        define( 'FFB_INCLUDES', FFB_PATH . '/includes' );
        define( 'FFB_URL', plugins_url( '', FFB_FILE ) );
        define( 'FFB_ASSETS', FFB_URL . '/assets' );
    }

    
    public function init_plugin() {
        $this->includes();
        $this->init_hooks();
        $this->ffb_wpdb_tables();
    }
    public function fluent_features_board_ajaxlogin() {
       
        check_ajax_referer( 'ajax-login-nonce', 'security' );

        
        $this->ffb_auth_user_login($_POST['username'], $_POST['password'], 'Login');

        die();
    }

    public function ffb_frontend_scripts() {
        wp_enqueue_style( 'fluent-features-board-global-frontend', FFB_ASSETS .'/css/fluent-features-board.frontend.css' );
        wp_enqueue_script( 'ff-request-frontend', FFB_ASSETS .'/js/ff-request-frontend.js', ['jquery'], true );
        wp_localize_script( 'ff-request-frontend', 'ajax_url', array(
            'ajaxurl'         => admin_url('admin-ajax.php'),
            'redirecturl'     => home_url(),
            'loadingmessage'  => esc_html__('Sending user info, please wait...','fluent-features-board'),
            'nonce'           => wp_create_nonce('ajax-nonce')
        ));
    }

    public function ffb_auth_user_login($user_login, $password, $login) {
		$info                  = array();
		$info['user_login']    = $user_login;
		$info['user_password'] = $password;


		$user_signon = wp_signon( $info, is_ssl() ? true : false);
		if ( is_wp_error($user_signon) ){
			echo json_encode(
                array(
                    'loggedin' => false,
                    'message'  => esc_html__('Wrong username or password.','fluent-features-board')
                )
            );
		} else {
			wp_set_current_user($user_signon->ID);
			if($login=="Login"){
				echo json_encode(
                    array(
                        'loggedin' => true,
                        'message'  => esc_html__('Login successful, redirecting...','fluent-features-board')
                    )
                );
			}
			else{
				echo json_encode(
                    array(
                        'loggedin' => true,
                        'message'  => esc_html__('Registration successful, redirecting...','fluent-features-board')
                    )
                );
			}

		}

		die();
    }


    public function fluent_features_board_ajaxregister() {
        global $options; $options = get_option('ffb_register_login');

		
		check_ajax_referer( 'ajax-register-nonce', 'security' );

		
		$info = array();
		$info['user_nicename'] = $info['nickname'] = $info['display_name'] = $info['first_name'] = $info['user_login'] = sanitize_user($_POST['username']) ;
		$info['user_pass']     = sanitize_text_field($_POST['password']);
		$info['user_email']    = sanitize_email( $_POST['email']);

		

		if(!is_email($info['user_email']) ){
			echo json_encode(
                array(
                    'loggedin' => false,
                    'message'  => esc_html__("Please enter a valid email address","fluent-features-board")
                )
            );
			die();
		}
		if(sanitize_text_field($_POST['password2'])!=$info['user_pass']){
			echo json_encode(
                array(
                    'loggedin' => false,
                    'message'  => esc_html__("Please enter same password in both fields","fluent-features-board")
                )
            );
			die();
		}
		if(!isset($info['user_pass'])|| !(strlen($info['user_pass']) >0 ) ){
			echo json_encode(
                array(
                    'loggedin' => false,
                    'message'  => esc_html__("Password fields cannot be blank","fluent-features-board")
                )
            );
			die();
		}

		$user_register = wp_insert_user( $info );
		if ( is_wp_error($user_register) ){
			$error  = $user_register->get_error_codes() ;

			if(in_array('empty_user_login', $error))
				echo json_encode(
                    array(
                        'loggedin' => false, 
                        'message'  => $user_register->get_error_message('empty_user_login')
                    )
                );
			elseif(in_array('existing_user_login',$error))
				echo json_encode(
                    array(
                        'loggedin' => false, 
                        'message'  => esc_html__('This username is already registered.','fluent-features-board')
                    )
                );
			elseif(in_array('existing_user_email',$error))
				echo json_encode(
                    array(
                        'loggedin' => false, 
                        'message'  => esc_html__('This email address is already registered.','fluent-features-board')
                    )
                );
		} else {
			$this->ffb_auth_user_login($info['nickname'], $info['user_pass'], 'Registration');
		}

		die();
    }

    

    public function ffb_loggedin_cookie( $logged_in_cookie ){
        $_COOKIE[LOGGED_IN_COOKIE] = $logged_in_cookie;
    }

    

    public function ffb_admin_scripts() {
        wp_enqueue_style( 'fluent-features-board-admin', FFB_ASSETS .'/css/fluent-features-board.admin.css' );
    }

    
    public function activate() {

        $installed = get_option( 'fluent_features_board_installed' );

        if ( ! $installed ) {
            update_option( 'fluent_features_board_installed', time() );
        }

        update_option( 'FFB_version', FFB_VERSION );
    }

    
    public function includes() {

        require_once FFB_INCLUDES . '/database/model-table.php';
        require_once FFB_INCLUDES . '/Assets.php';
        require_once FFB_INCLUDES . '/Shortcodes.php';

        
            require_once FFB_INCLUDES . '/Admin.php';
        

        if ( $this->is_request( 'ajax' ) ) {}
    }

   
    public function init_hooks() {

        add_action( 'init', array( $this, 'init_classes' ) );

        
        add_action( 'init', array( $this, 'localization_setup' ) );
    }

    public function ffb_wpdb_tables() {
        global $wpdb;

        // Tables
        $table_name = $wpdb->prefix . $this->fluent_features_board;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title text NOT NULL,
        logo text NOT NULL,
        sort_by text NOT NULL,
        show_upvotes text NOT NULL,
        visibility text NOT NULL,
        PRIMARY KEY  (id)
        ) $charset_collate;";


        
        $ffr_table_name = $wpdb->prefix . $this->ff_requests_list;

        $sql2 = "CREATE TABLE $ffr_table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        post_author int NOT NULL,
        title text NOT NULL,
        description text NOT NULL,
        status text NOT NULL,
        parent_id int NOT NULL,
        is_public text NOT NULL,
        comments_count int NOT NULL,
        votes_count int NOT NULL,
        PRIMARY KEY  (id)
        ) $charset_collate;";


        
        $ffr_tags_table = $wpdb->prefix . $this->ffr_tags;

        $sql3 = "CREATE TABLE $ffr_tags_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name text NOT NULL,
        slug text NOT NULL,
        board_id int NOT NULL,
        PRIMARY KEY  (id)
        ) $charset_collate;";


        
        $ffr_comments_table = $wpdb->prefix . $this->ffr_comments;

        $sql4 = "CREATE TABLE $ffr_comments_table (
        id bigint NOT NULL AUTO_INCREMENT,
        comment_post_ID bigint NOT NULL,
        comment_author tinytext NOT NULL,
        comment_author_email varchar(100) NULL,
        comment_author_url varchar(200) NULL,
        comment_author_IP varchar(100) NULL,
        comment_date text NOT NULL,
        comment_content text NOT NULL,
        comment_user_id bigint NOT NULL,
        PRIMARY KEY  (id)
        ) $charset_collate;";


        
        $ffr_votes_table = $wpdb->prefix . $this->ffr_votes;

        $sql5 = "CREATE TABLE $ffr_votes_table (
        id bigint NOT NULL AUTO_INCREMENT,
        post_id bigint NOT NULL,
        vote_user_id bigint NOT NULL,
        votes_count bigint NOT NULL,
        PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        dbDelta( $sql2 );
        dbDelta( $sql3 );
        dbDelta( $sql4 );
        dbDelta( $sql5 );
    }
    public function deactivate() {
        global $wpdb;
        $prefix=$wpdb->prefix;

        $sql="DROP TABLE IF EXISTS ".$prefix.$this->fluent_features_board;
        $sql1="DROP TABLE IF EXISTS ".$prefix.$this->ff_requests_list;
        $sql2="DROP TABLE IF EXISTS ".$prefix.$this->ffr_tags;
        $sql3="DROP TABLE IF EXISTS ".$prefix.$this->ffr_comments;
        $sql4="DROP TABLE IF EXISTS ".$prefix. $this->ffr_votes;

        $wpdb->query($sql);
        $wpdb->query($sql1);
        $wpdb->query($sql2);
        $wpdb->query($sql3);
        $wpdb->query($sql4);

    }

    
    public function init_classes() {

        
        $this->container['model_table'] = new FFB\FFB_Model_Table();

        
        if ( $this->is_request( 'admin' ) ) {
            $this->container['admin'] = new FFB\FFB_Admin();
        }

        
        if ( $this->is_request( 'ajax' ) ) {}

        
        $this->container['assets'] = new FFB\FFB_Assets();

        
        $this->container['hooks'] = new FFB\Shortcodes();
    }

    
    public function localization_setup() {
        load_plugin_textdomain( 'fluent-features-board', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    private function is_request( $type ) {
        switch ( $type ) {
            case 'admin' :
                return is_admin();

            case 'ajax' :
                return defined( 'DOING_AJAX' );

            case 'rest' :
                return defined( 'REST_REQUEST' );

            case 'cron' :
                return defined( 'DOING_CRON' );
        }
    }

} 

$fluent_features_board = Fluent_Features_board::init();
