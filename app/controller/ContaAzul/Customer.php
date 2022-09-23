<?php

namespace ContaAzul;

use DateTime;

defined('ABSPATH') || exit;

class Customer
{
  private $name;
  private $email;
  private $business_phone;
  private $mobile_phone;
  private $person_type = 'NATURAL';
  private $document;
  private $identity_document;
  private $date_of_birth;
  private $address;

  public function __construct()
  {
  }

  public function getStructure(): ?array
  {
    return get_object_vars($this) ? get_object_vars($this) : null;
  }

  public function set(string $prop, string $value)
  {
    $this->$prop = $value;
  }

  public function get(string $prop)
  {
    return isset($this->$prop) ? $this->$prop : null;
  }

  public function setDateOfBirth(DateTime $date): void
  {
    $this->date_of_birth = $date->format('Y-m-d\TH:i:s.196\Z');
  }

  public function setAddress(string $zip, string $street, string $number, string $complement, string $neighborhood): void
  {
    $this->address = (object) [
      'zip_code'     => $zip,
      'street'       => $street,
      'number'       => $number,
      'complement'   => $complement,
      'neighborhood' => $neighborhood,
    ];
  }
}
