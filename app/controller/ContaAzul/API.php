<?php

namespace ContaAzul;

defined('ABSPATH') || exit;

class API
{
  private const URL  = 'https://api.contaazul.com';

  private $env = null;

  public function __construct()
  {
    $this->env = new Env();
  }

  public function createSale(Sale $sale)
  {
    return $this->post('/v1/sales', $sale->getStructure());
  }

  public function createCustomer(Customer $customer)
  {
    return $this->post('/v1/customers', $customer->getStructure());
  }

  public function getCustomer(string $email)
  {
    return $this->get('/v1/customers', ['search' => $email]);
  }

  public function isAvailable(): bool
  {
    return $this->getRefreshToken() ? true : false;
  }

  public function getAuthorizationUrl(string $endpoint, string $redirectUri): string
  {
    $authUrl     = self::URL . $endpoint . '?';
    $data = [
      'redirect_uri' => $redirectUri,
      'client_id'    => $this->env->getClientId(),
      'scope'        => 'sales',
      'state'        => $this->env->getStateKey()
    ];

    return add_query_arg($data, $authUrl);
  }

  public function exchangeCodeForToken(string $code): bool
  {
    $code     = sanitize_text_field($code);
    $tokenUrl = self::URL . '/oauth2/token?';
    $data     = [
      'grant_type'   => 'authorization_code',
      'redirect_uri' => $this->env->getRedirectUri(),
      'code'         => $code
    ];
    $url      = add_query_arg($data, $tokenUrl);

    $request = wp_remote_post($url, [
      'headers' => [
        'Authorization'  => 'Basic ' . base64_encode($this->env->getClientId() . ':' . $this->env->getClientSecret())
      ]
    ]);

    $response = wp_remote_retrieve_body($request);
    $response = json_decode($response);

    if (!isset($response->access_token)) :
      return false;
    endif;

    $this->setAccessToken($response->access_token, (int) $response->expires_in);
    $this->setRefreshToken($response->refresh_token);

    return true;
  }

  private function getAccessToken(): ?string
  {
    $token = get_transient('contaazul_access_token');
    return $token ? $token : null;
  }

  private function setAccessToken(string $token, int $expireIn): void
  {
    set_transient('contaazul_access_token', $token, $expireIn);
  }

  private function setRefreshToken(string $token): void
  {
    update_option('contaazul_refresh_token', $token, false);
  }

  private function getRefreshToken(): ?string
  {
    return get_option('contaazul_refresh_token', null);
  }

  private function get(string $endpoint, array $data): array
  {
    return $this->fetch('GET', $endpoint, $data);
  }

  private function post(string $endpoint, array $data): array
  {
    return $this->fetch('POST', $endpoint, $data);
  }

  private function put(string $endpoint, array $data): array
  {
    return $this->fetch('PUT', $endpoint, $data);
  }

  private function delete(string $endpoint, array $data): array
  {
    return $this->fetch('DELETE', $endpoint, $data);
  }

  private function fetch(string $method, string $endpoint, array $data = []): array
  {
    if (!$this->isAvailable()) :
      return ['success' => false, 'error' => 'not_available'];
    endif;

    $accessToken = $this->getAccessToken();

    if (!$accessToken) :
      $accessToken = $this->refreshAccessToken();
    endif;

    // error_log($method . ' : ' . self::URL . $endpoint);
    // error_log(print_r(json_encode($data), true));

    $request = wp_remote_request(self::URL . $endpoint, [
      'headers' => [
        'Authorization' => 'Bearer ' .  $accessToken,
        'Content-Type'  => 'application/json',
      ],
      'method'  => strtoupper($method),
      'body'    => $method === 'GET' ? $data : json_encode($data),
      'timeout' => 60,
      'sslverify' => false
    ]);

    if (is_wp_error($request)) :
      return ['success' => false, 'error' => 'wp_error', 'context' => $request->get_error_message()];
    endif;

    $code     = (int) wp_remote_retrieve_response_code($request);
    $response = wp_remote_retrieve_body($request);
    // error_log($response);

    $response = json_decode($response);

    if ($code !== 200 && $code !== 201) :
      return ['success' => false, 'error' => 'wrong_status', 'context' => $response];
      return false;
    endif;

    return ['success' => true, 'response' => $response];
  }

  private function refreshAccessToken(): bool
  {
    $tokenUrl = self::URL . '/oauth2/token';
    $data     = [
      'grant_type'    => 'refresh_token',
      'refresh_token' => $this->getRefreshToken(),
    ];
    $url      = add_query_arg($data, $tokenUrl);

    $request  = wp_remote_post($url, [
      'headers' => [
        'Authorization'  => 'Basic ' . base64_encode($this->env->getClientId() . ':' . $this->env->getClientSecret())
      ]
    ]);

    $response = wp_remote_retrieve_body($request);
    $response = json_decode($response);

    if (!isset($response->access_token)) :
      return false;
    endif;

    $this->setAccessToken($response->access_token, (int) $response->expires_in);
    $this->setRefreshToken($response->refresh_token);

    return $response->access_token;
  }
}
