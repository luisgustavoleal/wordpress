<?php

/* 
  Woocommerce e Webservice API
  Enviar pedido para o webservice quando o status do pedido for alterado para processing
*/

//add_action('woocommerce_payment_complete', 'send_order_to_api');

add_action( 'woocommerce_order_payment_status_changed', 'send_order_to_api' );


/* 
 Caso deseja enviar após conclusão do pedido, utilizar a trigger abaixo.
*/
//add_action( 'woocommerce_thankyou', 'send_order_to_api' );


function send_order_to_api( $order_id ){  

       
        // Get Vendor ID form Woocommerce Settings (Custom Field)
        $_vendor_id = get_option ('woocommerce_vendor_id', 1);

        // Get order object and order details
        $order = new WC_Order( $order_id ); 
        $email = $order->billing_email;
        $phone = $order->billing_phone;
        $shipping_type = $order->get_shipping_method();
        $shipping_cost = $order->get_total_shipping();

        // Teste para pegar o status do pedido
        $status_order = $order->status;
        
        echo "<br>Status (1): " .$status_order ;
        /**
         *  No caso de utilizar-se o woocommerce_thankyou,  valida-se o status do pedido em proceesing (pago) para enviar a Order para Techdata.
         */
                                //processing   on-hold || $order->has_status('on-hold') 
        if ( $order->has_status('processing') ) {

            echo "<br>okokokok!!!" ;

            // set the address fields
            $user_id = $order->user_id;
            $address_fields = array('country',
                'title',
                'first_name',
                'last_name',
                'company',
                'address_1',
                'address_2',
                'address_3',
                'address_4',
                'city',
                'state',
                'postcode');

            $address = array();
            if(is_array($address_fields)){
                foreach($address_fields as $field){
                    $address['billing_'.$field] = get_user_meta( $user_id, 'billing_'.$field, true );
                    $address['shipping_'.$field] = get_user_meta( $user_id, 'shipping_'.$field, true );
                }
            }

            // get coupon information (if applicable)
            $cps = array();
            $cps = $order->get_items( 'coupon' );

            $coupon = array();
            foreach($cps as $cp){
                    // get coupon titles (and additional details if accepted by the API)
                    $coupon[] = $cp['name'];
            }

            // get product details
            $items = $order->get_items();

            $item_name  = array();
            $item_qty   = array();
            $item_price = array();
            $item_sku   = array();

            $_prod_pn       = "";
            $_prod_qtd      = "";
            $_prod_vlr      = "";
            $_prod_vlr_tot  = "";
            $_line          = "";
            $_cont_item     = 1;
            $_line          = "";

            foreach( $items as $key => $item){
                $item_name[]  = $item['name'];
                $item_qty[]   = $item['qty'];
                $item_price[] = $item['line_total'];
                
                $item_id      = $item['product_id'];
                $product      = new WC_Product($item_id);
                $item_sku[]   = $product->get_sku();

                $_tagLineIncio  = '<Line ID="'.$_cont_item.'">' .PHP_EOL;
                $_prod_pn       = '<ItemID>'.$item['name'].'</ItemID>' .PHP_EOL;
                $_prod_qtd      = '<Qty>'.$item['qty'].'</Qty>' .PHP_EOL;
                $_prod_vlr_tot  = '<ValorTotal>'.$item['line_total'].'</ValorTotal>' .PHP_EOL;
                $_tagLineFim    = '</Line>'.PHP_EOL;
                $_line          = $_tagLineIncio . $_prod_pn . $_prod_qtd . $_tagLineFim . $_line ;// .PHP_EOL; 
                $_cont_item    += 1;
            }

            // for online payments, send across the transaction ID/key. If the payment is handled offline, you could send across the order key instead 
            $transaction_key = get_post_meta( $order_id, '_transaction_id', true );
            //$transaction_key = empty($transaction_key) ? $_GET['key'] : $transaction_key;  
            
            // No caso de nao haver order_id (front) ele busca (_order_key) para o backoffice 
            $transaction_key = empty($transaction_key) ? get_post_meta(get_the_id(), '_order_key', true ) : $transaction_key;   

            // Create XML

            $_MsgID   = $transaction_key;
            $_idLoja  = '440261';   // Test
            $_title   = 'Order ID:'.$order_id.'-VendorID:'.$_idLoja;
            $_data    = date('Ymd');//'20210430';
            $_itens   = $data;
            $_xml     = '<?xml version="1.0" encoding="ISO-8859-1" ?> 
            <!DOCTYPE OrderEnv SYSTEM "https://xxxxxxxxxxxxxxxxx.xxx/XMLGate/dtd/ixOrder6.dtd"> 
            <OrderEnv AuthCode="xxxxxxxxxxxxxxxxxxxxxxxxxx" MsgID="'. $_MsgID .'">
            <Order Currency="EUR" ISOCountryCode="PT"> 
            <Head>
            <BuyerID>'.$_vendor_id.'</BuyerID> 
            <PurchasingType>AAAA</PurchasingType> 
            <Title>'.$_title.'</Title> 
            <OrderDate>'.$_data.'</OrderDate> 
            <DeliverTo>
            <Consignee ID="123456" /> 
            </DeliverTo>
            <Delivery Type="" Full="n" />
            </Head>
            <Body>
            '.$_line.'
            </Body>
            </Order>
            </OrderEnv>';


            // Send XML to API by CURL  

            $_url = "https://xxxxxxxxxxx.xxx/XMLGate/inbound"; // URL test
            $ch = curl_init($_url);

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, "xml=" . $_xml);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $data = curl_exec($ch);
            
            // Imprime na tela do pedido para teste o retorno da techdata
            echo '<pre>';
            //echo htmlentities($data);
            echo '</pre>';
            
            if(curl_errno($ch))
                print curl_error($ch);
            else
                curl_close($ch);


            // Log do envio e retorno 

            $filename     = "D:/WEB/dpp/dpp.com.pt/wwwroot/wp-content/uploads/order_logs/".$_MsgID."_order.txt";
            //mb_convert_encoding($filename, 'UTF-16LE', 'UTF-8');
            $FileHandle   = fopen($filename, 'w');
            fwrite($FileHandle, $_xml);
            fclose($FileHandle); 

            // Log retorno
            $filenameRet     = "D:/WEB/dpp/dpp.com.pt/wwwroot/wp-content/uploads/order_logs/".$_MsgID."_return.txt";
            $FileHandleRet   = fopen($filenameRet, 'w');
            fwrite($FileHandleRet, $data);
            fclose($FileHandleRet); 

        }

        echo "<br>Status (2): " .$status_order ;
        
}
