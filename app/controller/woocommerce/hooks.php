<?php defined('ABSPATH') || exit;

add_action('woocommerce_order_status_processing', function (int $orderId, WC_Order $order) {
  $api    = new \ContaAzul\API();

  $customerId = get_user_meta($order->get_customer_id(), 'contaazul_id', true);

  if (!$customerId) :

    $customer = getCustomerFromWoocommerceOrder($order);
    $contaAzulCustomer = $api->createCustomer($customer);

    if ($contaAzulCustomer['success']) :
      update_user_meta($order->get_customer_id(), 'contaazul_id', $contaAzulCustomer['response']->id);
    endif;
  endif;

  $sale   = getSaleFromWoocommerceOrder($order);
  $contaAzulSale = $api->createSale($sale);

  if ($contaAzulSale['success']) :
    $order->add_meta_data('contaazul_id', $contaAzulSale['response']->id, true);
    $order->add_meta_data('contaazul_ca_id', $contaAzulSale['response']->ca_id, true);

    $order->add_order_note('Pedido vinculado ao ContaAzul com o ID' . $contaAzulSale['response']->ca_id);
    $order->save();
  endif;
}, 999, 2);
