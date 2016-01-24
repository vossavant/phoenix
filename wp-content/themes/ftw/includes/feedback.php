<?php
/*
 *	QUOTEBOARD
 *	Send beta feedback
 *
 *	Called after a beta tester completes the feedback form. This script
 *	sends the feedback via Mandrill's API to ensure delivery.
 */

// check honeypot
if ( !empty( $_POST['location'] ) ) :
	exit;
endif;

$user_email = sanitize_email( $_POST['email'] );

if ( empty( $user_email ) ) {
	echo json_encode( array( 'errors' => 'Please enter your email address, so we can respond if needed' ) );
	exit;
}

$feedback	= sanitize_text_field( $_POST['feedback'] );

if ( empty( $feedback ) ) {
	echo json_encode( array( 'errors' => 'Please provide some feedback' ) );
	exit;
}

$user_name	= sanitize_text_field( $_POST['username'] );
$user_agent = sanitize_text_field( $_POST['user_agent'] );
$page_url	= sanitize_text_field( $_POST['current_page'] );

// load up Mandrill dependencies
require_once( 'mandrill/send.php' );

// build and send email
$email_to		= 'feedback@quoteboard.com';
$email_to_name 	= 'Ryan Burney';
$email_subject	= 'Beta Feedback';
$email_tags		= array( 'beta-feedback' );
$email_html		= qb_build_email_html( $user_email, $page_url, $user_agent, $feedback );

// I don't care about getting text emails here
$email_text = $email_html;

// send the email
$result[] = mandrill_sendmail( $email_html, $email_text, $email_subject, $email_to, $email_to_name, $email_tags );

// if an exception was thrown, try sending with PHP mail
if ( $result[0]['mandrill_error'] ) {
	mail( $email_to, 'Beta Tester Feedback', $feedback, 'From: noreply@quoteboard.com' );
}

// return result for jQuery goodness
echo json_encode(
	array(
		'result' => $result
	)
);

exit;


/*
 *	This function loads the master template and replaces the
 *	placeholder values with text specific to beta tester feedback.
 */
function qb_build_email_html( $user_email, $page_url, $user_agent, $feedback ) {
	$email_header = "New Beta Feedback";
	$email_body = '
	<p>Someone just left some beta feedback. His information is below.</p>
	<p><b>Email: </b> ' . $user_email . '</p>
	<p><b>Page: </b> ' . $page_url . '</p>
	<p><b>Browser: </b> ' . $user_agent . '</p>
	<p><b>Feedback: </b></p>' . $feedback;

	$template = @file_get_contents( get_template_directory_uri() . '/includes/mandrill/templates/master-html.html' );
	
	if ( $template ) {
		$template = str_replace( '{{HEADER}}', $email_header, $template );
		$template = str_replace( '{{BODY}}', $email_body, $template );
		$template = str_replace( '{{NOTIFICATION}}', '', $template );
	}

	return $template;
}