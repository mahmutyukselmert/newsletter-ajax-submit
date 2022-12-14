<?php
/*
 * Plugin Name:       Newsletter Ajax Submit
 * Plugin URI:        https:///kargobul.com.tr
 * Description:       Newsletter için Mahmut Yüksel Mert tarafından geliştirilmiş ajax ile abonelik gerçekleştirebilen eklenti.
 * Version:           1.0
 * Requires PHP:      7.4
 * Author:            Mahmut Yüksel MERT
 * Author URI:        https:///kargobul.com.tr
*/

/**
 * Custom script
 */
function ajax_subscribe_scripts() {
    wp_enqueue_script( "ajax_subscribe", plugin_dir_url( __FILE__ ) . 'main.min.js', array( 'jquery' ) );

    wp_localize_script( 'ajax_subscribe' , 'ajax', array(
        'url' =>            admin_url( 'admin-ajax.php' ),
        'nonce' =>     wp_create_nonce( 'noncy_nonce' ),
        'assets_url' =>     get_stylesheet_directory_uri(),
    ) );  
}
add_action( 'wp_enqueue_scripts', 'ajax_subscribe_scripts' );

/// KARGOBUL NEWSLETTER AJAX ///
function newsletter_ajax_subscribe() {
    check_ajax_referer( 'noncy_nonce', 'nonce' );
    $data = urldecode( $_POST['data'] );

    if ( !empty( $data ) ) :
        $data_array = explode( "&", $data );
        $fields = [];
        foreach ( $data_array as $array ) :
            $array = explode( "=", $array );
            $fields[ $array[0] ] = $array[1];
        endforeach;
    endif;

    if ( !empty( $fields ) ) :
        global $wpdb;
        
        // check if already exists
        
        /** @var int $count **/
        $count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}newsletter WHERE email = %s", $fields['ne'] ) );
        
        if( $count > 0 ) {
            $output = array(
                'status'    => 'error',
                'msg'       => __( 'Aktif olarak aboneliğiniz bulunmaktadır.', THEME_NAME )
            );
        } elseif( !defined( 'NEWSLETTER_VERSION' ) ) {
            $output = array(
                'status'    => 'error',
                'msg'       => __( 'Lütfen önce Newsletter eklentisini kurun ve aktif edin.', THEME_NAME )
            );           
        } else {
            /**
             * Generate token
            */
            
            /** @var string $token */
            $token =  wp_generate_password( rand( 10, 50 ), false );


            $wpdb->insert( $wpdb->prefix . 'newsletter', array(
                'email'         => $fields['ne'],
                'status'        => $fields['na'],
                'http_referer'  => $fields['nhr'],
                'token'         => $token,
            )
        );

            $opts = get_option('newsletter');

            $opt_in = (int) $opts['noconfirmation'];

            // This means that double opt in is enabled
            // so we need to send activation e-mail
            if ($opt_in == 0) {
                $newsletter = Newsletter::instance();
                $user = NewsletterUsers::instance()->get_user( $wpdb->insert_id );

                NewsletterSubscription::instance()->mail($user->email, $newsletter->replace($opts['confirmation_subject'], $user), $newsletter->replace($opts['confirmation_message'], $user));
            }

            $output = array(
                'status'    => 'success',
                'msg'       => __( 'Aboneliğiniz başarılı bir şekilde gerçekleşti. Abone olduğunuz için teşekkürler!', THEME_NAME )
            );  
        }
        
    else :
        $output = array(
            'status'    => 'error',
            'msg'       => __( 'Bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.', THEME_NAME  )
        );
    endif;
    
    wp_send_json( $output );
    die();
}

add_action( 'wp_ajax_newsletter_ajax_subscribe', 'newsletter_ajax_subscribe' );
add_action( 'wp_ajax_nopriv_newsletter_ajax_subscribe', 'newsletter_ajax_subscribe' );
/// KARGOBUL NEWSLETTER AJAX ///