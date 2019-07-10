<?php
/*
Plugin Name:  Contact Form Plugin
Plugin URI: 
Description:  WordPress Contact Form
Version: 1.0
Author: Maxim
Author URI: 
*/


    function html_form_code() {
    echo '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post">';
    echo '<p>';
    echo 'First Name  <br />';
    echo '<input type="text" name="cf-name" pattern="[a-zA-Z0-9 ]+" value="' . ( isset( $_POST["cf-name"] ) ? esc_attr( $_POST["cf-name"] ) : '' ) . '" size="40" />';
    echo '</p>';
    echo '<p>';
    echo 'Last Name  <br />';
    echo '<input type="text" name="cf-lname" pattern="[a-zA-Z0-9 ]+" value="' . ( isset( $_POST["cf-lname"] ) ? esc_attr( $_POST["cf-lname"] ) : '' ) . '" size="40" />';
    echo '</p>';
    echo '<p>';
    echo 'Subject  <br />';
    echo '<input type="text" name="cf-subject" pattern="[a-zA-Z ]+" value="' . ( isset( $_POST["cf-subject"] ) ? esc_attr( $_POST["cf-subject"] ) : '' ) . '" size="40" />';
    echo '</p>';
    echo '<p>';
    echo 'Your Message <br />';
    echo '<textarea rows="10" cols="35" name="cf-message">' . ( isset( $_POST["cf-message"] ) ? esc_attr( $_POST["cf-message"] ) : '' ) . '</textarea>';
    echo '</p>';
    echo '<p>';
    echo 'Your Email (required) <br />';
    echo '<input type="email" name="cf-email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" value="' . ( isset( $_POST["cf-email"] ) ? esc_attr( $_POST["cf-email"] ) : '' ) . '" size="40" />';
    echo '</p>';
    echo '<p><input type="submit" name="cf-submitted" value="Submit"/></p>';
    echo '</form>';
}


function deliver_mail() {

    // if the submit button is clicked, send the email
    if ( isset( $_POST['cf-submitted'] ) ) {

        // sanitize form values
        $name    = sanitize_text_field( $_POST["cf-name"] );
        $lname    = sanitize_text_field( $_POST["cf-lname"] );
        $email   = sanitize_email( $_POST["cf-email"] );
        $subject = sanitize_text_field( $_POST["cf-subject"] );
        $message = esc_textarea( $_POST["cf-message"] );
        //  email address
        //$to = "urmail@example.domen";
        $to = get_option( 'admin_email' );

        $headers = "From: $name <$email>" . "\r\n";
 

        // If email has been process for sending, display a success message
        if ( $result =wp_mail( $to, $subject, $message, $headers ) ) {
            echo '<div>';
            echo '<p>Message sent</p>';
            echo '</div>';
        } else {
            echo 'Message not sent';
        }
    }
        // making log file 
        if ($result) {
            file_put_contents ('sent.log', print_r($headers, true), FILE_APPEND);
            }

        $arr = array(
            'properties' => array(
                array(
                    'property' => 'email',
                    'value' => $email
                ),
                array(
                    'property' => 'firstname',
                    'value' => $name
                ),
                array(
                    'property' => 'lastname',
                    'value' => $lname
                )
            )
        );
         $post_json = json_encode($arr);
        $hapikey = "46efe4f0-ed5d-4c99-b174-c3cb0dddf06b";
        $endpoint = 'https://api.hubapi.com/contacts/v1/contact?hapikey=' . $hapikey;
        $ch = @curl_init();
        @curl_setopt($ch, CURLOPT_POST, true);
        @curl_setopt($ch, CURLOPT_POSTFIELDS, $post_json);
        @curl_setopt($ch, CURLOPT_URL, $endpoint);
        @curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = @curl_exec($ch);
        $status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_errors = curl_error($ch);
        @curl_close($ch);
        echo "curl Errors: " . $curl_errors;
        echo "\nStatus code: " . $status_code;
        echo "\nResponse: " . $response;
       
}




function cf_shortcode() {
	ob_start();
	deliver_mail();
	html_form_code();
  //  create_contact();

	return ob_get_clean();
}

add_shortcode( 'contact_form', 'cf_shortcode' );

?>

