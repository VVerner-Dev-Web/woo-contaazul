<?php

use ContaAzul\Sale;
use ContaAzul\Customer;

defined('ABSPATH') || exit;

function getContaAzulAdminPage(): void
{
  require_once VVCA_VIEWS . '/admin/settings.php';
}

function getSaleFromWoocommerceOrder(WC_Order $order): Sale
{
  $sale  = new Sale();
  $today = new DateTime($order->get_date_created()->format('Y-m-d H:i:s'));

  foreach ($order->get_items() as $item) :
    $sale->insertProduct(
      get_the_title($item->get_product_id()),
      $item->get_quantity(),
      $item->get_total() / $item->get_quantity()
    );
  endforeach;

  $sale->setEmissionDate($today);
  $sale->setPayment('CASH', 'CREDIT_CARD');
  $sale->setPaymentInstallments(1, $order->get_total(), $today);

  $sale->customer_id = get_user_meta($order->get_customer_id(), 'contaazul_id', true);

  return apply_filters('contaazul/sale-structure', $sale, $order);
}

function getCustomerFromWoocommerceOrder(WC_Order $order): Customer
{
  $customer = new Customer();

  $customer->set('name', $order->get_formatted_billing_full_name());
  $customer->set('email', $order->get_billing_email());
  $customer->set('business_phone', preg_replace('/\D/', '', $order->get_billing_phone()));
  $customer->set('mobile_phone', preg_replace('/\D/', '', $order->get_meta('_billing_cellphone')));
  $customer->set('document', preg_replace('/\D/', '', $order->get_meta('_billing_cpf')));

  $customer->setAddress(
    $order->get_billing_postcode(),
    $order->get_billing_address_1(),
    $order->get_meta('_billing_number'),
    $order->get_billing_address_2(),
    $order->get_meta('_billing_neighborhood')
  );

  return apply_filters('contaazul/customer-structure', $customer, $order);
}
