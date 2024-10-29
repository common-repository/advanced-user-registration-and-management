<?php

// Exit if called directly
if (!defined('ABSPATH')) {
    exit();
}

/**
 * The main class and initialization point of the mailchimp plugin admin.
 */
if (!class_exists('LR_Mailchimp_Admin')) {

    class LR_Mailchimp_Admin {
        /*
         * Constructor
         */

        public function __construct() {
            add_action('admin_enqueue_scripts', array($this, 'load_scripts'));
            add_action('admin_init', array($this, 'admin_init'));
        }

        /*
         * Enqueue Admin Scripts
         */

        public function load_scripts($hook) {
            if ($hook == 'loginradius_page_loginradius_mailchimp') {
                wp_enqueue_style('lr_mailchimp_admin_style', LR_ROOT_URL . 'lr-mailchimp/assets/css/lr-mailchimp.css', array(), '1.0', false);
                wp_enqueue_script('lr_mailchimp_admin_script', LR_ROOT_URL . 'lr-mailchimp/assets/js/lr-mailchimp.js', array('jquery'), '1.0', false);
            }
        }

        /**
         * Register LR_Mailchimp_Settings and its sanitization callback. Replicate loginradius settings on multisites.
         */
        public function admin_init() {
            register_setting('lr_mailchimp_settings', 'LR_Mailchimp_Settings');

            // Replicate mailchimp configuration to the subblogs in the multisite network
            if (is_multisite() && is_main_site()) {
                add_action('wpmu_new_blog', array($this, 'replicate_settings_to_new_blog'));
            }
        }

        // Replicate the mailchimp config to the new blog created in the multisite network
        public function replicate_settings_to_new_blog($blogId) {
            global $lr_mailchimp_settings;
            add_blog_option($blogId, 'LR_Mailchimp_Settings', $lr_mailchimp_settings);
        }

        /*
         * Callback for add_submenu_page,
         * This is the first function which is called while plugin admin page is requested
         */

        public static function options_page() {
            require_once "views/settings.php";
            LR_Mailchimp_Admin_Settings::render_options_page();
        }

    }

}

new LR_Mailchimp_Admin();
