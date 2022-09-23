<?php

namespace ContaAzul;

defined('ABSPATH') || exit;

use DateTime;

class Sale
{
  private $emission   = '';
  private $status     = '';
  private $products   = [];
  private $payment    = [];
  private $discount;

  public function __construct()
  {
    $this->status            = 'PENDING';
    $this->payment['type']   = 'CASH';
    $this->payment['method'] = 'CREDIT_CARD';
  }

  public function getStructure(): ?array
  {
    return get_object_vars($this) ? get_object_vars($this) : null;
  }

  public function setPaymentInstallments(int $quantity, float $total, DateTime $paymentDate): void
  {
    $this->payment['installments'] = [];
    $installmentAmount = $total / $quantity;

    for ($i = 0; $i <= $quantity; $i++) :
      $paymentDate->modify('+ ' . $i . ' months');

      $this->payment['installments'][] = [
        'number'   => $i + 1,
        'value'    => round($installmentAmount, 2),
        'due_date' => $paymentDate->format('Y-m-d\TH:i:s.196\Z')
      ];
    endfor;
  }

  public function setDiscount(float $value): void
  {
    $this->discount['measure_unit'] = 'VALUE';
    $this->discount['rate'] = $value;
  }

  public function setPayment(string $type, string $method): void
  {
    $type   = strtoupper($type);
    $method = strtoupper($method);

    $this->payment['type']   = $type === 'CASH' ? 'CASH' : 'TIMES';
    $this->payment['method'] = $method === 'CREDIT_CARD' ? 'CREDIT_CARD' : 'INSTANT_PAYMENT';
  }

  public function insertProduct(string $title, int $quantity, float $value): void
  {
    $this->products[] = [
      'description' => sanitize_text_field($title),
      'quantity'    => (int) $quantity,
      'value'       => (float) $value
    ];
  }

  public function setEmissionDate(DateTime $date): void
  {
    $this->emission = $date->format('Y-m-d\TH:i:s.196\Z');
  }

  public function setStatus(string $status): void
  {
    $status = strtoupper($status);
    $this->status = $status === 'PENDING' ? 'PENDING' : 'COMMITTED';
  }

  public function get(string $prop)
  {
    return isset($prop) ? $this->$prop : null;
  }
}
