<?php

/*
 * Função que atualiza o status do pedido para pending após conclusão da compra. 
 */

add_action( 'woocommerce_thankyou', 'woocommerce_thankyou_change_order_status', 10, 1 );

function woocommerce_thankyou_change_order_status( $order_id ){
    if( ! $order_id ) return;

    $order = wc_get_order( $order_id );

    if( $order->get_status() == 'processing'  ||  $order->get_status() == 'on-hold' )
        $order->update_status( 'pending' );
}
