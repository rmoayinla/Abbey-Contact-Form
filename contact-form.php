<?php
/*
* Plugin Name: Abbey Contact Form
* Description: Display a simple contact form 
* Author: Rabiu Mustapha
* Version: 0.1
* Text Domain: abbey-contact-form

*/

class Abbey_Contact_Form extends WP_Widget{
	
	private $form_elements = array();
	private $alert_class = "";
	private $status_message = "";
	private $mail = null;
	
	public function __construct(){
		//parent::__construct( $this->id, $this->name, array ( $this->description ) );
		parent::__construct( "abbey-contact-form", __( "Abbey Contact Form", "abbey-contact-form"), 
				array( "description" => __( "This widget display my simple contact form", "abbey-contact-form" )
				) 
		);

		add_action ( "phpmailer_init", array( $this, "configureSMTP" ) );

		add_action ( "wp_mail_failed", array( $this, "displayError" ) );

		add_action ( "wp_enqueue_scripts", array ( $this, "enqueJS" ) );

		add_action ( "wp_ajax_nopriv_abbey_process_form", array ( $this, "processForm" ) );

		add_action ( "wp_ajax_abbey_process_form", array ( $this, "processForm" ) );

	}

	public function widget ( $args, $instance ) {
		$before_widget = ( isset( $args["before_widget"] ) ) ? $args["before_widget"] : "";
		$after_widget = ( isset( $args["after_widget"] ) ) ? $args["after_widget"] : "";
		$before_title = ( isset( $args["before_title"] ) ) ? $args["before_title"] : "";
		$after_title = ( isset( $args["after_title"] ) ) ? $args["after_title"] : "";

		

		echo $before_widget.$before_title.apply_filters( "widget_title", $instance["title"] ).$after_title;
		echo $this->content().$after_widget;
	}
	
	public function form ( $instance ){ 
		$title = ( isset( $instance["title"] ) ) ? $instance["title"] : "Default title";
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
	    	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" 
	    	name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
	     </p>	<?php
	}

	public function update( $new_instance, $old_instance ){
		$instance = array();
		$instance["title"] = ( isset($new_instance["title"] ) ) ? strip_tags( $new_instance["title"] ) : "";
		return $instance;
	}

	private function content (){ ?>
		
		<form role="form" id="abbey-contact-form" method="post" >
			<div class="form-group">
    			<label for=""><?php  _e( "Email address:", "abbey-contact-form" ); ?> </label>
    			<input type="email" class="form-control" id="" placeholder="somone@example.com" name="abbey_contact_form_email" />
  			</div>
  			<div class="form-group">
   				 <label for=""> <?php _e( "Fullname: ", "abbey-contact-form" ); ?></label>
    			<input type="text" class="form-control" id="" placeholder="John Smith"  name="abbey_contact_form_fullname"/>
  			</div>
  		<div class="form-group">
    		<label for=""> <?php _e( "Message:", "abbey-contact-form" ); ?></label>
    		<textarea id="message" class="form-control" name="abbey_contact_form_message" rows="5"> </textarea>
    		
 		 </div>

 		 <div class="checkbox">
 		 	<label><input type="checkbox" name="abbey_contact_form_checkbox" value="1" />
 		 		<?php echo sprintf( 'I agree to %s contact policy and will like to receive updates and reply concerning this message', 
 		 								get_bloginfo( "name" ) 
 		 						);  ?>
 		 	</label>
 		 </div>
 		
 		<button type="submit" class="btn btn-default" name="abbey_contact_form_submit" id="abbey-contact-form-button">Submit</button>
				
		</form> <?php

	}

	private function displayStatus(){ 
		echo '<div class="alert pad-xs '.esc_attr( $this->alert_class ).'"> '.$this->status_message.'</div>';
	}

	function processForm(){
		if ( $_POST["action"] !== "abbey_process_form" ) {
			$this->status_message = sprintf( '<span class="error">%s</span>', __( "Cheating, uh?", "abbey-contact-form" ) );
		} else{
			$email = ( is_email( $_POST["abbey_contact_form_email"] ) ) ? $_POST["abbey_contact_form_email"] : "";
			$fullname = ( !empty ( $_POST["abbey_contact_form_fullname"] ) ) ? sanitize_text_field( $_POST["abbey_contact_form_fullname"] ) : "";
			$message = ( !empty ( $_POST["abbey_contact_form_message"] ) ) ? sanitize_text_field( $_POST["abbey_contact_form_message"] ) : "";
			$checkbox = ( !empty( $_POST[ "abbey_contact_form_checkbox" ] ) ) ? (int) $_POST[ "abbey_contact_form_checkbox" ] : "";
			if( empty( $email ) ){
				$this->status_message = sprintf( '<span class="error">%s</span>', __( "Email is invalid or empty", "abbey-contact-form" ) );
			} elseif( empty ( $fullname ) ) {
				$this->status_message = sprintf( '<span class="error">%s</span>', __( "Sorry, I need your name or what would I call you?", "abbey-contact-form" ) );
			} elseif ( empty ( $message ) ) {
				$this->status_message = sprintf( '<span class="error">%s</span>', __( "Message can't be empty, thats the main reason you are contacting me", "abbey-contact-form" ) );
			} 
			elseif( empty( $checbox ) ){
				$this->status_message = sprintf( '<span class="error">%s</span>', __( "Please tick the checkbox and then submit", "abbey-contact-form" ) );
			}	else {
				$headers = array();
				$headers[] = "Reply-To:".$fullname. " <".$email.">";
				$headers[] = "Content-Type: text/html";
				$to = array ( $email, "rmoayinla@hotmail.com" );
				$mail = $this->styleMessage( $fullname, $message );

				$send = wp_mail ( $to, "Feedback", $mail, $headers );

				if ( $send ){
					$this->status_message = sprintf( '<span class="success">%s</span>',__( "Message sent OOO", "abbey-contact-form" ) );
					
				}
			}

		}
		echo $this->status_message;
		wp_die();
	}

	function styleMessage ( $name, $message, $info = array() ){ 
	$html = "
		<p style='font-size:13px;'> On: ".date("Y/m/d").", Time: ". date( "h:i:sa" ).". </p>
		<h4>".$name." wrote: </h4>
		<hr style='width:100%; border:1px dashed #eee;' />
		<br />
		<p style='line-height:20px;font-weight:bold;padding-top:30px;padding-bottom:30px'>".$message."</p>
		<br/>
		<br/>
		<p style='text-align:center;font-size:13px;'> &copy;".date( "Y" )." ".get_bloginfo( "name" )."
			<br />
			All rights reserved, please <a href='".home_url( "/" )."'> Visit us </a>
			<br />
		</p>
		";
	return $html;

	}
	function configureSMTP ( $phpmailer ){
		if ( ! ( $phpmailer instanceof PHPMailer ) ) {
        	require_once ABSPATH . WPINC . '/class-phpmailer.php';
        	require_once ABSPATH . WPINC . '/class-smtp.php';
        	$phpmailer = new PHPMailer( true );
        }
        	$phpmailer->isSMTP();
        	$phpmailer->Host = "ssl://smtp.gmail.com";
        	$phpmailer->Username = "rmoayinla@gmail.com";
        	$phpmailer->Password = "ayinlaomowura";
        	$phpmailer->SMTPAuth = true;
        	$phpmailer->Port = 465;
        	$phpmailer->SMTPSecure = "ssl";
        	$phpmailer->From = "rmoayinla@gmail.com";
        	$phpmailer->FromName = "Rabiu Mustapha";
    	
	}
	function displayError ( $wp_error ){
		if ( is_wp_error( $wp_error ) ){
			$this->status_message =  $wp_error->get_error_message();
		}
	}

	function enqueJS () {
		wp_enqueue_script( "abbey-contact-script", plugin_dir_url( __FILE__ )."/contact-form.js", array( "jquery" ), 1.0, true );
		wp_localize_script( "abbey-contact-script", "abbeyContact", 
			array( 
				"ajax_url" => admin_url( "admin-ajax.php" ), 
				"spinner_url" => admin_url( "images/spinner.gif" )
			) 
		);
	}

}

add_action ( "widgets_init", function(){
	register_widget( "Abbey_Contact_Form" );
} );