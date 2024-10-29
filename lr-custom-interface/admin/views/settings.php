<?php
// Exit if called directly
if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

/**
 * The custom_interface admin settings page.
 */
if ( ! class_exists( 'LR_Custom_Interface_Admin_Settings' ) ) {

    class LR_Custom_Interface_Admin_Settings {

        public static function custom_interface_javascript() {
            $siteId = '';
            
            if( is_multisite() ){
                $siteId = get_current_user_id();
            }

            ?>
            <script type="text/javascript" >
                jQuery( document ).ready( function ($) {
                    if ( $( '#lr-custom-interface-enable' ).is( ':checked' ) ) {
                        $( ".lr-option-disabled-hr" ).hide();
                    } else {
                        $( ".lr-option-disabled-hr" ).show();
                    }

                    $( '#lr-custom-interface-enable' ).change( function () {
                        if ( $(this).is( ':checked' ) ) {
                            $( ".lr-option-disabled-hr" ).hide();
                        } else {
                            $( ".lr-option-disabled-hr" ).show();
                        }
                    });
                    $( '#lr-ci-upload-btn' ).click( function (event) { 
                        event.preventDefault();
                        ci_upload(event);
                    });

                    var loadPreview = function() {
                        $LRIC.util.ready(function () {
                            var options = {};
                            options.apikey = phpvar.apiKey;
                            options.appname = phpvar.siteName;
                            options.providers = phpvar.providers;
                            options.templatename = "loginradiuscustom_tmpl";
                            $LRIC.renderInterface( "interface_container", options );
                        });
                    }

                    var reloadImages = function() {
                        var images = document.getElementsByClassName( 'custom_preview_img' );
                        var milliseconds = new Date().getTime();
                        for ( var i = 0; images.length > i; i++ ) {
                            images[i].src = images[i].src.split('?ts=')[0] + "?ts=" + milliseconds;
                        }
                    }

                    function ci_upload(event) {
                        event.preventDefault();

                        var upload_form = document.getElementById('lr-ci-upload-form');
                        var fileSelect = document.getElementById('lr-ci-upload-files');
                        var fileName = document.getElementById('lr-ci-upload-file-name').value;
                        // Get the selected files.
                        var files = fileSelect.files;
                        // Create a new FormData object.
                        var formData = new FormData();
                        // Loop through each of the selected files.
                        for (var i = 0; i < files.length; i++) {
                            var file = files[i];
                            // Check the file type.
                            if ( ! file.type.match( 'image.*' ) ) {
                                jQuery( '#lr-ci-upload-btn' ).val( 'Upload Image' );
                                jQuery( '#ajax-result' ).show();
                                jQuery( '#ajax-result' ).html('<div class="lr-waring-box">file type not correct, should be png files</div>');
                                throw new Error("Wrong type of image file, should be in png");
                            }
                            // Append the file to the request
                            formData.append( 'images[]', file, file.name );
                        }
                         formData.append('socialProvider', fileName);
                         formData.append('action', 'upload_custom_interface_image');
                        if ( files.length > 0 ) {
                            jQuery('#ajax-result').show();
                            jQuery('#ajax-result').html('<div class="lr-alert-box">'+fileName+' Image is loading...</div>');
                            jQuery('#lr-ci-upload-btn').val('Uploading ...');
                            jQuery.ajax({
                                type: 'POST',
                                url: '<?php echo admin_url( 'admin-ajax.php' );?>',
                                data: formData,
                                xhr: function () {
                                    var myXhr = jQuery.ajaxSettings.xhr();
                                    return myXhr;
                                },
                                processData: false,
                                contentType: false,
                                success: function (response) {
                                    jQuery('#ajax-result').show();
                                    jQuery('#ajax-result').html('<div class="lr-alert-box">'+fileName+' '+response+'</div>');
                                    jQuery('#lr-ci-upload-files').val('');
                                    
                                    reloadImages();
                                },
                                error: function ( xhr, ajaxOptions, thrownError ) {
                                    jQuery('#ajax-result').show();
                                    jQuery('#ajax-result').html('fail');
                                    jQuery('#ajax-result').append(xhr + '<br>');
                                    jQuery('#ajax-result').append(ajaxOptions + '<br>');
                                    jQuery('#ajax-result').append(thrownError);
                                }
                            });
                        } else {
                            alert( 'Please select uploading images first' );
                        }
                        jQuery( '#lr-ci-upload-btn' ).val( 'Upload Image' );
                    }

                    loadPreview();
                });

                
            </script>
            <?php
        }

        public static function render_options_page() {
            LR_Custom_Interface::reset_options();
            $lr_custom_interface_settings = get_option( 'LR_Custom_Interface_Settings' );
            
            /**
             *  Call Ajax at the footer
             */
            add_action( 'admin_footer', array( get_class(), 'custom_interface_javascript' ) );
            $enableStyle = '';

            ?>
            <div class="wrap lr-wrap cf">
                <header>
                    <h2 class="logo"><a href="//loginradius.com" target="_blank">LoginRadius</a><em>Custom Interface</em></h2>
                </header>

                <div class="lr-tab-frame lr-active">
                    <form method="POST" enctype="multipart/form-data" action="options.php" name="lr-ci-form" id="lr-ci-option-form" method="post">
                        <div class="lr_options_container" <?php echo $enableStyle; ?> >
                            <?php
                            settings_fields('lr_custom_interface_settings');
                            settings_errors();
                            $max_upload_size = wp_max_upload_size();
                            if ( ! $max_upload_size ) {
                                    $max_upload_size = 0;
                            }
                            ?>
                            <div class="lr-row">
                                <h3>
                                    <?php _e( 'Custom Interface Settings', 'lr-plugin-slug' ); ?>
                                </h3>
                                <div>
                                    <input type="hidden" id="checkbox_value" value="<?php echo ! empty( $lr_custom_interface_settings['custom_interface'] ) ? $lr_custom_interface_settings['custom_interface'] : ''; ?>" />
                                    <input type="checkbox" class="lr-toggle" id="lr-custom-interface-enable" name="LR_Custom_Interface_Settings[custom_interface]" value="1" <?php echo isset( $lr_custom_interface_settings['custom_interface'] ) && $lr_custom_interface_settings['custom_interface'] == '1' ? 'checked' : ''; ?> />
                                    <label class="lr-show-toggle" for="lr-custom-interface-enable">
                                        <?php _e('Enable Custom Interface settings', 'lr-plugin-slug' ); ?>
                                        <span class="lr-tooltip" data-title="<?php _e( 'Enable, to use custom interface instead of LoginRadius themes', 'lr-plugin-slug' ); ?>">
                                            <span class="dashicons dashicons-editor-help"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div style="position: relative;">
                            <div class="lr-option-disabled-hr lr_custom_interface" style="display: none;"></div>
                            <div class="lr_options_container">
                                <div class="lr-row lr-cf">
                                    <h3><?php _e( 'Upload image for the Custom Interface','lr-plugin-slug' );?></h3>
                                    <p style="display:none;" id="ajax-result">hidden</p>
                                    <label>
                                        <span class="lr_property_title"><?php _e( 'Select Social Provider','lr-plugin-slug' );?>
                                            <span class="lr-tooltip" data-title="<?php _e( 'You can select the social provider to use a custom image','lr-plugin-slug' );?>">
                                                <span class="dashicons dashicons-editor-help"></span>
                                            </span>
                                        </span>
                                        <div id="select-provider"></div>
                                    </label>
                                    <label>
                                        <span class="lr_property_title"><?php _e( 'Upload Images', 'lr-plugin-slug' ); ?>
                                            <span class="lr-tooltip" data-title="<?php _e( 'Upload a social provider image','lr-plugin-slug' );?>">
                                                <span class="dashicons dashicons-editor-help"></span>
                                            </span>
                                        </span>
                                        <input name="images[]" type="file" id="lr-ci-upload-files" multiple class="lr-row-field" style="margin-top: 10px;"/>
                                    </label>
                                    <div class="lr-row-field"><?php printf( __( 'Maximum upload file size: %s.' ), esc_html( size_format( $max_upload_size ) ) ); ?></div>
                                    <input type="submit" name="submit" value="Upload Images" id="lr-ci-upload-btn" style="width:150px" />
                                </div>
                            </div>
                            <div class="lr_options_container">
                                <div class="lr-row lr-cf">
                                    <div class="lr-ci-preview">
                                        <h3><?php _e( 'Custom Interface Images', 'lr-plugin-slug' ); ?></h3>
                                        <ul class="interface_container lr-cf"></ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="submit" style="">
                            <input type="submit" id="btnSubmit" class="button button-primary" value="Save Options">
                        </p>
                    </form>
                    <?php do_action( 'lr_reset_admin_ui', 'Custom Interface' );?>
                </div>
            </div>
            <?php
        }

    }

}