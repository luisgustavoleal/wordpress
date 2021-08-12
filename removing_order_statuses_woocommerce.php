<?php

/*
 * Removing core order statuses
 * @param array $wc_statuses_arr Array of all order statuses on the website
 */

function remove_order_statuses( $wc_statuses_arr ){

	// Processing
	if( isset( $wc_statuses_arr['wc-processing'] ) ) { // if exists
		unset( $wc_statuses_arr['wc-processing'] ); // remove it from array
	}

	// Refunded
	if( isset( $wc_statuses_arr['wc-refunded'] ) ){
		unset( $wc_statuses_arr['wc-refunded'] );
	}

	// On Hold
	if( isset( $wc_statuses_arr['wc-on-hold'] ) ){
		unset( $wc_statuses_arr['wc-on-hold'] );
	}

	// Failed
	if( isset( $wc_statuses_arr['wc-failed'] ) ){
		unset( $wc_statuses_arr['wc-failed'] );
	}

	// Pending payment
	if( isset( $wc_statuses_arr['wc-pending'] ) ){
		unset( $wc_statuses_arr['wc-pending'] );
	}

	// Completed
	//if( isset( $wc_statuses_arr['wc-completed'] ) ){
	//    unset( $wc_statuses_arr['wc-completed'] );
	//}
	// Cancelled
	//if( isset( $wc_statuses_arr['wc-cancelled'] ) ){
	//    unset( $wc_statuses_arr['wc-cancelled'] );
	//}
	return $wc_statuses_arr; // return result statuses
}
add_filter( 'wc_order_statuses', 'remove_order_statuses' );
