<?php

/*	
hCaptcha addon for Ultimate Member plugin 

Version 1.0

Install by adding this code to your child theme functions file
Create your child theme with the "Child Theme Configurator" plugin

Free version ( Publisher account ) of the hCaptcha plugin remote functions being used for testing.
Install and activate the hCaptcha plugin for setting the configuration because these parameters are being used:

    hCaptcha Site Key
    hCaptcha Secret Key 
    hCaptcha Theme
    hCaptcha Size

Ultimate Member forms supported are: Registration, Login and Password reset.

Deactivate the hCaptcha plugin during usage of this code but keep the plugin for future changes of the configuration parameters and if a local language being used
Any local language file is being used from the hCaptcha plugin if required regardless of the activatation status of the hCaptcha plugin
*/


add_action( 'um_submit_form_errors_hook__registration', 'custom_verify_hcaptcha_um', 10, 1 );
add_action( 'um_submit_form_errors_hook_logincheck',    'custom_verify_hcaptcha_um', 10, 1 );
add_action( 'um_reset_password_errors_hook',            'custom_verify_hcaptcha_um', 10, 1 );

        function custom_verify_hcaptcha_um( $args ) {
			
            $hcaptcha_secret_key = get_option( 'hcaptcha_secret_key' );
            $hcaptcha_api_key    = get_option( 'hcaptcha_api_key' );

            if( !empty( $hcaptcha_secret_key ) && !empty( $hcaptcha_api_key )) {

                if( isset( $_POST['h-captcha-response'] ) && !empty( $_POST['h-captcha-response'] )) {

                    $data = array(  'secret'   => $hcaptcha_secret_key, 
                                    'response' => $_POST['h-captcha-response'],
                                    'sitekey'  => $hcaptcha_api_key );

                    $verify = curl_init();

                    curl_setopt( $verify, CURLOPT_URL, "https://hcaptcha.com/siteverify" );
                    curl_setopt( $verify, CURLOPT_POST, true);
                    curl_setopt( $verify, CURLOPT_POSTFIELDS, http_build_query( $data ));
                    curl_setopt( $verify, CURLOPT_RETURNTRANSFER, true );

                    $verifyResponse = curl_exec( $verify );            
                    $responseData = json_decode( $verifyResponse ); 

                    if( $responseData->success ) {
                        // Not a bot so accept this user
                        return;

                    } else $errMsg = 'Captcha Failed';
                } else $errMsg = '<strong>Error</strong>: Please complete the captcha.';

            } else {
                $errMsg = 'No hCaptcha secret key found. Add your secret key in the hCaptcha plugin admin page.';
            }

            hCaptcha_textdomain();
            $errMsg = __( $errMsg, 'hcaptcha-for-forms-and-more' );
            if( isset( $args['_um_password_reset'] ) && $args['_um_password_reset'] ) $errMsg = str_replace( array( '<strong>', '</strong>' ), '', $errMsg );
            UM()->form()->add_error( 'username_b', $errMsg );
        }

add_action( 'um_after_form', 'custom_setup_hcaptcha_um', 10, 1 ); 
add_action( 'um_reset_password_form', 'custom_setup_hcaptcha_um', 10, 1 );            

        function custom_setup_hcaptcha_um( $args ) {

            if( !in_array( $args['template'], array( 'login', 'register', 'password-reset' ))) return $args;

            hCaptcha_textdomain();

            $hcaptcha_api_key = get_option( 'hcaptcha_api_key' );
            $hcaptcha_size    = get_option( 'hcaptcha_size' );
            $hcaptcha_theme   = get_option( 'hcaptcha_theme' );

            if( !empty( $hcaptcha_api_key )) {
                echo    '<!-- start hCaptcha setup -->
                        <div style="text-align: center;">
                        <div class="h-captcha" 
                        data-sitekey="' . esc_html( $hcaptcha_api_key ) . '"
                        data-theme="' . esc_html( $hcaptcha_theme ) . '"
                        data-size="' . esc_html( $hcaptcha_size ) . '">
                        </div>
                        <p>This site is protected by hCaptcha and its</p>
                        <p><a href="https://hcaptcha.com/privacy">Privacy Policy</a> and
                        <a href="https://hcaptcha.com/terms">Terms of Service</a> apply.</p>
                        </div>
                        <!-- end hCaptcha setup -->';                 

            } else {
                echo __( 'No hCaptcha API key found. Add your API key in the hCaptcha plugin admin page.', 'hcaptcha-for-forms-and-more' );
                wp_die();
            }

            return $args;
        }

        function hCaptcha_textdomain() {

            if( !defined( 'HCAPTCHA_VERSION' )) {
                load_plugin_textdomain( 'hcaptcha-for-forms-and-more', false, WP_PLUGIN_DIR . '/hcaptcha-for-forms-and-more/languages/' );
            }
        }

add_action( 'wp_enqueue_scripts', 'enqueue_hcaptcha_um_scripts' );

        function enqueue_hcaptcha_um_scripts() {

            wp_enqueue_script(
                'hcaptcha-um-script', 
                'https://www.hCaptcha.com/1/api.js?render=onload',
                array( 'jquery' )
            );
        }