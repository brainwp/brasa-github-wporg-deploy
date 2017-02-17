<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
/**
 * Use the PHP for https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate
 *
 * @package   Plugin_Name
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2014 Your Name or Company Name
 *
 * @wordpress-plugin
 * Plugin Name:       Brasa GitHub WP.org Deploy
 * Plugin URI:        @TODO
 * Description:       @TODO
 * Version:           0.1
 * Author:            @TODO
 * Author URI:        @TODO
 * Text Domain:       brasa-wporg-deploy
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/<owner>/<repo>
 */
// Include custom post type
require 'inc/register-cpt.php';
class Brasa_GitHub_Release_To_WPORG {
	/**
	* Instance of this class.
	*
	* @var object
	*/
	protected static $instance = null;

	/**
	 * Options page array
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * @var boolean|in
	 */
	public $post_id = false;

	/**
	 * @var boolean|string
	 */
	protected $folder = false;

	/**
	 * @var boolean|string
	 */
	protected $svn_folder = false;

	/**
	 * @var array
	 */
	protected $data = array();

	protected function create_folder() {
		$this->folder = WP_CONTENT_DIR . '/wporg-deploy-' . $this->post_id;
		if ( ! file_exists( $this->folder ) ) {
			mkdir( $this->folder, 0755 );
		}
		$this->svn_folder = $this->folder . '/svn';
		if ( ! file_exists( $this->svn_folder ) ) {
			mkdir( $this->svn_folder, 0755 );
		}
	}
	protected function svn_clone() {
		$svn_url = 'http://plugins.svn.wordpress.org/' . $this->options[ 'wporg_slug'];
		var_dump( svn_checkout( $svn_url, $this->svn_folder ) );
	}
	/**
	 * Download, extract and upload via svn to wordpress.org
	 * @return boolean
	 */
    protected function update_plugin() {
    	$this->create_folder();
    	$this->svn_clone();
    	var_dump( $this->data );
		exec( "cd $this->folder && wget $this->data['tarball_url']" );

		$file_format = sprintf( '%s.tar.gz', $this->data[ 'after' ] );
		$file_check = $folder . '/' . $file_format;
		if ( ! file_exists( $file_check ) ) {
			return false;
		}
		$template_folder = get_template_directory();
		exec( "rm -rf $template_folder" );
		exec( "cd $folder && tar -zxvf $file_format" );
		$folder_format = sprintf( '%s-%s', $this->data['repository']['name'], $this->data[ 'after' ] );
		rename( $folder . '/' . $folder_format, $template_folder );
		unlink( $folder . '/' . $file_format );
		return true;
	}
	/**
	 * Construct class
	 * @return boolean
	 */
	public function __construct () {
		add_action( 'wp_ajax_nopriv_brasa_deploy', array( $this, 'deploy' ) );
		add_action( 'wp_ajax_brasa_deploy', array( $this, 'deploy' ) );
	}
	/**
	 * Validate secret key from GitHub
	 * @return boolean
	 */
	private function validate_secret() {
		list ( $algo, $signature ) = explode( '=', $_SERVER['HTTP_X_HUB_SIGNATURE'] );
		//var_dump( explode( '=', $_SERVER['HTTP_X_HUB_SIGNATURE'] ) );
        if ( $algo !== 'sha1' ) {
            // see https://developer.github.com/webhooks/securing/
            return false;
        }
        if ( false === $this->options[ 'github_secret' ] ) {
        	return false;
        }
        //var_dump( $HTTP_RAW_POST_DATA );
        $payloadhash = hash_hmac( $algo, file_get_contents('php://input'), $this->options[ 'github_secret' ] );
        if ( $payloadhash == $signature ) {
        	return true;
        }
        return false;
	}
	/**
	 * init var $this->options
	 */
	protected function init_options() {
		$options = array( 'wporg_user', 'wporg_password', 'wporg_slug', 'github_secret' );
		foreach( $options as $option ) {
			$value = get_post_meta( $this->post_id, $option, true );
			if ( $value ) {
				$this->options[ $option ] = $value;
			} else {
				$this->options[ $option ] = false;
			}
		}
	}
	/**
	 * Deploy last commit from GitHub repo
	 * @return boolean
	 */
	public function deploy() {
		//var_dump( $_REQUEST );
		if ( ! isset( $_REQUEST[ 'post_id' ] ) || ! is_numeric( $_REQUEST[ 'post_id' ] ) ) {
			wp_die( 'false 1' );
		}
		$this->post_id = (int) $_REQUEST[ 'post_id' ];
		$this->init_options();
		var_dump( $this );
		$this->data = json_decode( file_get_contents( 'php://input' ), true );
		if ( false === $this->validate_secret() ) {
			//wp_die( 'false 2' );
		}
		if ( $this->update_plugin() ) {
			wp_die( 'true' );
		}
		wp_die( 'false 3' );
	}
	/**
	 * Get class instance
	 * @return object
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}
}
/**
 * Instance plugin classes
 */
function brasa_wporg_deploy_load_classes() {
	new Brasa_GitHub_Release_To_WPORG();
}
add_action( 'plugins_loaded', 'brasa_wporg_deploy_load_classes' );
