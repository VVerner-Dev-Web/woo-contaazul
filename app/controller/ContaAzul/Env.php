<?php

namespace ContaAzul;

defined('ABSPATH') || exit;

class Env
{
  private const PREFIX = '_vv-contaazul-';
  private const STATE  = 'contaAzul123';

  protected $client_id;
  protected $client_secret;
  protected $redirect_uri;

  public function getStateKey()
  {
    return self::STATE;
  }

  public function setRedirectUri(string $value)
  {
    $value = sanitize_text_field($value);
    $this->set('redirect_uri', $value);
  }

  public function getRedirectUri(): string
  {
    return $this->get('redirect_uri');
  }

  public function setClientId(string $value)
  {
    $value = sanitize_text_field($value);
    $this->set('client_id', $value);
  }

  public function getClientId(): string
  {
    return $this->get('client_id');
  }

  public function setClientSecret(string $value)
  {
    $value = sanitize_text_field($value);
    $this->set('client_secret', $value);
  }

  public function getClientSecret(): string
  {
    return $this->get('client_secret');
  }

  private function get(string $prop)
  {
    return get_option(self::PREFIX . $prop, '');
  }

  private function set(string $prop, $value)
  {
    update_option(self::PREFIX . $prop, $value, false);
  }
}
