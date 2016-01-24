<?php
add_action( 'wp_enqueue_scripts', 'setupAJAX' );  
add_action( 'wp_ajax_ajax-submit', 'submitAJAX' );
add_action( 'wp_ajax_nopriv_ajax-submit', 'submitAJAX' );

function setupAJAX() {
	// creates
    wp_localize_script( 'utility', 'qb_ajax', array(
        'ajaxURL'  	=> admin_url( 'admin-ajax.php' ),
        'ajaxNonce'	=> wp_create_nonce( 'qb_ajax_nonce' )
        )
    );
}

function submitAJAX() {
	// check noncee
	$nonce = $_POST['nonce'];
	if ( !wp_verify_nonce( $nonce, 'qb_ajax_nonce' ) ) {
		echo json_encode( array( 'errors' => 'Invalid nonce; please try refreshing the page' ) );
    	exit;
	}

	$form_name = $_POST['form_name'];

	/*	Permissions levels:
		edit_posts		= contributor
		publish_posts	= author
		edit_pages		= editor
		edit_users		= admin
	*/
	//if ( current_user_can( 'publish_posts' ) ) {
		include_once( TEMPLATEPATH . '/includes/' . $form_name . '.php' );

		// generate the response
		$response = json_encode( $_POST );
	 
		// response output
		header( "Content-Type: application/json" );
		echo $response;
	//}
 
	exit();
}