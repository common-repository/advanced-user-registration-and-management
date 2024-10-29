<?php
// Exit if called directly
if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

/**
 * The main class and initialization point of the mailchimp plugin admin.
 */
if ( ! class_exists( 'LR_Social_Share_Admin' ) ) {

    class LR_Social_Share_Admin {

        /**
         * LR_Social_Share_Admin class instance
         *
         * @var string
         */
        private static $instance;

        /**
         * Get singleton object for class LR_Social_Share_Admin
         *
         * @return object LR_Social_Share_Admin
         */
        public static function get_instance() {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof LR_Social_Share_Admin ) ) {
                self::$instance = new LR_Social_Share_Admin();
            }
            return self::$instance;
        }

        /*
         * Constructor for class LR_Social_Share_Admin
         */

        public function __construct() {
            // Registering hooks callback for admin section.
            $this->register_hook_callbacks();
        }

        /*
         * Register admin hook callbacks
         */
        public function register_hook_callbacks() {

            // Add a meta box on all posts and pages to disable sharing.
            add_action( 'add_meta_boxes', array( $this, 'meta_box_setup' ) );

            // Add a callback public function to save any data a user enters in
            add_action( 'save_post', array( $this, 'save_meta' ) );

            add_action( 'admin_init', array( $this, 'admin_init') );
        }

        /**
         * Callback for admin_menu hook,
         * Register LoginRadius_settings and its sanitization callback. Add Login Radius meta box to pages and posts.
         */
        public function admin_init() {

            register_setting('loginradius_share_settings', 'LoginRadius_share_settings', array( $this, 'validate_options') );

            // Replicate Social Login configuration to the subblogs in the multisite network
            if ( is_multisite() && is_main_site() ) {
                add_action( 'wpmu_new_blog', array( $this, 'replicate_settings_to_new_blog' ) );
            }
        }
        
        public static function validate_options( $settings ) {
            $providerDefault = array('Facebook'=>'Facebook', 'Google'=>'Google', 'LinkedIn'=>'LinkedIn','Pinterest'=>'Pinterest', 'Twitter'=>'Twitter');
            $counterProviderDefault = array('Facebook Like'=>'Facebook Like', 'Google+ Share'=>'Google+ Share', 'LinkedIn Share'=>'LinkedIn Share','Pinterest Pin it'=>'Pinterest Pin it', 'Twitter Tweet'=>'Twitter Tweet');
            
            /*Horizontal Sharing Provider*/
            $settings['horizontal_rearrange_providers'] = ( ( isset( $settings['horizontal_rearrange_providers'] ) && count($settings['horizontal_rearrange_providers'])>0) ? $settings['horizontal_rearrange_providers'] : $providerDefault );
            $settings['horizontal_sharing_providers']['Default'] = ( ( isset( $settings['horizontal_sharing_providers']['Default'] ) && count($settings['horizontal_sharing_providers']['Default'])>0) ? $settings['horizontal_sharing_providers']['Default'] : $providerDefault );
            
            /*Vertical Sharing Provider*/
            $settings['vertical_rearrange_providers'] = ( ( isset( $settings['vertical_rearrange_providers'] ) && count($settings['vertical_rearrange_providers'])>0) ? $settings['vertical_rearrange_providers'] : $providerDefault );
            $settings['vertical_sharing_providers']['Default'] = ( ( isset( $settings['vertical_sharing_providers']['Default'] ) && count($settings['vertical_sharing_providers']['Default'])>0) ? $settings['vertical_sharing_providers']['Default'] : $providerDefault );
            
            /*Horizontal Counter Provider*/
            $settings['horizontal_sharing_providers']['Hybrid'] = ( ( isset( $settings['horizontal_sharing_providers']['Hybrid'] ) && count($settings['horizontal_sharing_providers']['Hybrid'])>0) ? $settings['horizontal_sharing_providers']['Hybrid'] : $counterProviderDefault );
            
            /*Vertical Counter Provider*/
            $settings['vertical_sharing_providers']['Hybrid'] = ( ( isset( $settings['vertical_sharing_providers']['Hybrid'] ) && count($settings['vertical_sharing_providers']['Hybrid'])>0) ? $settings['vertical_sharing_providers']['Hybrid'] : $counterProviderDefault );
            
            return $settings;
        }

        // Replicate the social login config to the new blog created in the multisite network
        public function replicate_settings_to_new_blog( $blogId ) {
            global $loginradius_share_settings;
            add_blog_option( $blogId, 'LoginRadius_share_settings', $loginradius_share_settings );
        }
        /*
         * adding LoginRadius meta box on each page and post
         */
        public function meta_box_setup() {
            add_meta_box('login_radius_meta', 'LoginRadius Sharing', array($this, 'meta_setup'));
        }

        /**
         * Display  metabox information on page and post
         */
        public function meta_setup() {
            global $post;
            $postType = $post->post_type;
            $lrMeta = get_post_meta($post->ID, '_login_radius_meta', true);
            if ( is_array( $lrMeta ) ) {
                $meta['sharing'] = isset($lrMeta['sharing']) ? $lrMeta['sharing'] : '';
            } else {
                $meta['sharing'] = isset($lrMeta) && $lrMeta == '1' || $lrMeta == '0' ? $lrMeta : '';
            }
            ?>
            <p>
                <label for="login_radius_sharing">
                    <input type="checkbox" name="_login_radius_meta[sharing]" id="login_radius_sharing" value='1' <?php checked('1', $meta['sharing']); ?> />
                    <?php _e( 'Disable Social Sharing on this ' . $postType, 'lr-plugin-slug' ) ?>
                </label>
            </p>
            <?php
            // Custom nonce for verification later.
            echo '<input type="hidden" name="login_radius_meta_nonce" value="' . wp_create_nonce(__FILE__) . '" />';
        }

        /**
         * Save sharing enable/diable meta fields.
         */
        public function save_meta( $postId ) {
            // make sure data came from our meta box
            if ( ! isset( $_POST['login_radius_meta_nonce'] ) || ! wp_verify_nonce( $_POST['login_radius_meta_nonce'], __FILE__)) {
                return $postId;
            }
            // check user permissions
            if ( $_POST['post_type'] == 'page' ) {
                if ( ! current_user_can('edit_page', $postId)) {
                    return $postId;
                }
            } else {
                if ( ! current_user_can('edit_post', $postId)) {
                    return $postId;
                }
            }
            if ( isset( $_POST['_login_radius_meta'] ) ) {
                $newData = $_POST['_login_radius_meta'];
            } else {
                $newData = 0;
            }
            update_post_meta( $postId, '_login_radius_meta', $newData );
            return $postId;
        }

        /*
         * Callback for add_menu_page,
         * This is the first function which is called while plugin admin page is requested
         */
        public static function options_page() {

            require_once LR_ROOT_DIR."lr-social-sharing/admin/views/settings.php";
            LR_Social_Share_Settings::render_options_page();
        }

    }

    new LR_Social_Share_Admin();
}

