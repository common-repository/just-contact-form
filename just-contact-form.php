<?php
/*
Plugin Name: Just Contact Form
Plugin URI: http://wp-plugins.in/just-contact-form
Description: Just ajax contact form with captcha, one shortcode and easy to use, without options and without complexity.
Version: 1.0.2
Author: Alobaidi
Author URI: http://wp-plugins.in
License: GPLv2 or later
*/

/*  Copyright 2015 Alobaidi (email: wp-plugins@outlook.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


function alobaidi_just_contact_form_plugin_row_meta( $links, $file ) {

	if ( strpos( $file, 'just-contact-form.php' ) !== false ) {
		
		$new_links = array(
						'<a href="http://wp-plugins.in/just-contact-form" target="_blank">Explanation of Use</a>',
						'<a href="https://profiles.wordpress.org/alobaidi#content-plugins" target="_blank">More Plugins</a>',
						'<a href="http://j.mp/ET_WPTime_ref_pl" target="_blank">Elegant Themes</a>'
					);
		
		$links = array_merge( $links, $new_links );
		
	}
	
	return $links;
	
}
add_filter( 'plugin_row_meta', 'alobaidi_just_contact_form_plugin_row_meta', 10, 2 );


function just_contact_form_session(){
	if( !session_id() ){
		session_start();
	}
}
add_action('init', 'just_contact_form_session', 1);


function add_option_for_just_contact_form_ajax_image(){
	add_settings_field( 'just_contact_form_ajax_image', 'Ajax Image', 'just_contact_form_ajax_image', 'general', 'default', array('label_for' => 'just_contact_form_ajax_image') );
	register_setting( 'general', 'just_contact_form_ajax_image' );
}
add_action( 'admin_init', 'add_option_for_just_contact_form_ajax_image' );

function just_contact_form_ajax_image(){
	?>
		<input id="just_contact_form_ajax_image" class="regular-text" type="text" name="just_contact_form_ajax_image" value="<?php echo esc_attr( get_option('just_contact_form_ajax_image') ); ?>">
		<p class="description">Ajax image for Just Contact Form, enter your ajax image link, image size must to be 64x64 to be retina ready! <a href="http://preloaders.net/" target="_blank">Get free ajax images</a>.</p>
	<?php
}


function just_contact_form_ajax_script(){	
	wp_enqueue_script( 'just-contact-form-ajax-script', plugins_url( '/js/just-contact-form-ajax-script.js', __FILE__ ), array('jquery-form'), false, false);
}
add_action('wp_enqueue_scripts', 'just_contact_form_ajax_script');


function just_contact_form_html($email, $captcha){
	
	if( !empty($email) ){
		$_SESSION['just_contact_form_to_email'] = $email;
	} 
	
	$_SESSION['just_contact_form_captcha'] = rand(99,999);
	$captcha_number = $_SESSION['just_contact_form_captcha'];

	if( !empty($captcha) and $captcha == 'false' ){
		$display_captcha = 0;
	}else{
		$display_captcha = 1;
	}

	if( get_option('just_contact_form_ajax_image') ){
		$ajax_image = get_option('just_contact_form_ajax_image');
	}else{
		$ajax_image = plugins_url('/images/ajax-load.GIF', __FILE__);
	}

	?>
		<div id="just-contact-form-wrap" class="just-contact-form-wrap">
			<form id="just-contact-form" class="just-contact-form" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post">

				<?php
					if( is_singular() ){
						$_SESSION['just_contact_form_id'] = get_the_ID();
					}else{
						$_SESSION['just_contact_form_id'] = 'home';
					}
				?>

				<p>Name (required)<br><input type="text" value="" name="just_contact_form_name" class="just_contact_form_name"></p>

				<p>Email (required)<br><input type="text" value="" name="just_contact_form_email" class="just_contact_form_email"></p>

				<p>Website<br><input type="text" value="" name="just_contact_form_url" class="just_contact_form_url"></p>

				<p>Subject (required)<br><input type="text" value="" name="just_contact_form_subject" class="just_contact_form_subject"></p>

				<p>Message (required)<br><textarea name="just_contact_form_message" class="just_contact_form_message"></textarea></p>

				<?php
					if( $display_captcha == 1 ){
						?>
							<p>Enter number "<?php echo $captcha_number; ?>" (required)<br><input type="text" value="" name="just_contact_form_captcha" class="just_contact_form_captcha"></p>
						<?php
					}else{
						?>
							<input type="hidden" value="<?php echo $captcha_number; ?>" name="just_contact_form_captcha" class="just_contact_form_captcha">
						<?php
					}
				?>
			
				<p><input type="submit" name="just_contact_form_submit" value="Send" id="just-contact-form-submit" class="just-contact-form-submit"></p>

				<p id="just-contact-form-ajax-load" class="just-contact-form-ajax-load" style="display:none;"><img style="display:block !important; margin:0 auto !important; padding:0 !important;" width="32" height="32" src="<?php echo $ajax_image; ?>"></p>
			</form>

			<div id="just-contact-form-result" class="just-contact-form-result" style="display:none;"></div>
		</div>
	<?php

}


function just_contact_form_shortcode($atts){

	ob_start();

	if( !empty($atts['email']) ){
		$email = $atts['email'];
	}else{
		$email = '';
	}

	if( !empty($atts['captcha']) ){
		$captcha = $atts['captcha'];
	}else{
		$captcha = '';
	}

	just_contact_form_html($email, $captcha);

	return ob_get_clean();

}
add_shortcode('just_contact_form', 'just_contact_form_shortcode');


function just_contact_form_sending_message(){

	if( isset($_SESSION['just_contact_form_to_email']) ){
		$to_email = $_SESSION['just_contact_form_to_email'];
	}else{
		$to_email = get_option('admin_email');
	}

	if( isset($_POST['just_contact_form_submit']) and $_SERVER['REQUEST_METHOD'] == "POST" ){

		if( isset($_SESSION['just_contact_form_captcha']) and !empty($_POST['just_contact_form_captcha']) and $_POST['just_contact_form_captcha'] == $_SESSION['just_contact_form_captcha'] ){

			if( empty($_POST['just_contact_form_name']) ){
				echo '<p>Name field is empty! Please enter name.</p>';
				exit;
			}

			elseif( strlen(utf8_decode($_POST['just_contact_form_name'])) > 200 ){
				echo '<p>Name is too long! Please enter a short name.</p>';
				exit;
			}

			elseif( empty($_POST['just_contact_form_email']) ){
				echo '<p>Email field is empty! Please enter email.</p>';
				exit;
			}

			elseif( !preg_match('/^([\w-\.]+@([\w-]+\.)+[\w-]{2,6})?$/', $_POST['just_contact_form_email']) ){
				echo '<p>Invalid email! Please enter a valid email.</p>';
				exit;
			}

			elseif( strlen(utf8_decode($_POST['just_contact_form_email'])) > 200 ){
				echo '<p>Email is too long! Please enter a short email.</p>';
				exit;
			}

			elseif( !empty($_POST['just_contact_form_url']) and !filter_var($_POST['just_contact_form_url'], FILTER_VALIDATE_URL) ){
				echo "<p>Invalid website link! Please enter a valid website link.</p>";
				exit;
			}

			elseif( !empty($_POST['just_contact_form_url']) and strlen(utf8_decode($_POST['just_contact_form_url'])) > 1000 ){
				echo "<p>Website link is too long! Please enter a short link.</p>";
				exit;
			}

			elseif( empty($_POST['just_contact_form_subject']) ){
				echo '<p>Subject field is empty! Please enter subject.</p>';
				exit;
			}

			elseif( strlen(utf8_decode($_POST['just_contact_form_subject'])) > 200 ){
				echo '<p>Subject is too long! Please enter a short subject.</p>';
				exit;
			}

			elseif( empty($_POST['just_contact_form_message']) ){
				echo '<p>Message field is empty! Please enter message.</p>';
				exit;
			}

			elseif( strlen(utf8_decode($_POST['just_contact_form_message'])) > 2000 ){
				echo '<p>Your message contains more than 2000 characters! Please enter less than 2000 characters.</p>';
				exit;
			}

			else{
				$name    	  = sanitize_text_field( $_POST["just_contact_form_name"] );
				$from_email   = sanitize_email( $_POST["just_contact_form_email"] );

				if( !empty($_POST['just_contact_form_url']) ){
					$url = "<p><b>Website:</b> ".esc_url($_POST['just_contact_form_url'])."</p>";
				}else{
					$url = null;
				}

				$get_subject   = 	sanitize_text_field( $_POST["just_contact_form_subject"] );
				$website_name  =	get_option('blogname');
				$subject 	   = 	"$get_subject - $website_name";
				$message 	   = 	nl2br( esc_textarea($_POST["just_contact_form_message"]) );

				if( preg_match('/^[0-9]+$/', $_SESSION['just_contact_form_id']) ){
					$get_via = get_permalink($_SESSION['just_contact_form_id']);
					if($get_via){
						$via = $get_via;
					}else{
						$via = get_option('siteurl');
					}
				}else{
					$via = get_option('siteurl');
				}
				
				$body		   = 	"<p><b>Message Via:</b> $via</p>";
				$body		  .= 	"<p><b>Name:</b> $name</p>";
				$body		  .= 	"<p><b>Email:</b> $from_email</p>";
				$body		  .= 	$url;
				$body		  .= 	"<p><b>Subject:</b> $get_subject</p>";
				$body		  .= 	"<p><b>Message:</b><br>$message</p>";

				$headers  	   = 	'MIME-Version: 1.0' . "\r\n";
				$headers      .= 	'Content-type: text/html; charset=utf-8' . "\r\n";
				$headers      .= 	"From: $name <$from_email>" . "\r\n";

				if( wp_mail($to_email, $subject, $body, $headers) ){
					echo '<script type="text/javascript">';
					echo 'jQuery(document).ready(function() {';
					echo 'jQuery("#just-contact-form").slideUp("fast");';
					echo 'jQuery("#just-contact-form")[0].reset();';
					echo '});';
					echo '</script>';
					echo '<p>Your message has been sent! Thank you.</p>';

					unset($_SESSION['just_contact_form_captcha']);

					if( isset($_SESSION['just_contact_form_to_email']) ){
						unset($_SESSION['just_contact_form_to_email']);
					}

					unset($_SESSION['just_contact_form_id']);
					exit;
				}else{
					echo '<p>There is an unknown error! Please refresh the page and try again.</p>';
					exit;
				}
				
			}

		}

		else{
			if( isset($_SESSION['just_contact_form_captcha']) ){
				$captcha = $_SESSION['just_contact_form_captcha'];
				$error_message = "<p>Please enter number \"$captcha\".</p>";
			}else{
				$error_message = '<p>Please refresh page to send another message.</p>';
			}

			echo $error_message;
			exit;
		}

	}

}
add_action('init', 'just_contact_form_sending_message');

?>