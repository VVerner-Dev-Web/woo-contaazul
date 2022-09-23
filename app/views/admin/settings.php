<?php defined('ABSPATH') || exit('No direct script access allowed');

$api = new ContaAzul\API();
$env = new ContaAzul\Env();
$exchanged = null;

if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], $_POST['action'])) :
    $redirectUri  = admin_url('admin.php?page=' . $_GET['page']);
    $clientId     = $_POST['ca_client_id'];
    $clientSecret = $_POST['ca_client_secret'];
    $urlState     = $_POST['ca_state_key'];


    $env->setRedirectUri($redirectUri);
    $env->setClientId($clientId);
    $env->setClientSecret($clientSecret);
elseif (isset($_GET['code']) && isset($_GET['state'])) :
    $exchanged = $api->exchangeCodeForToken($_GET['code']);
endif; 
?>

<div class="wrap">
    <h1>Configurações de integração Conta Azul</h1>
    <p>Abaixo você deverá colocar suas credenciais da API</p>

    <?php if ($exchanged) : ?>
        <div class="notice notice-success">
            <p>
                <strong>Tudo ok! </strong>Sua autenticação está correta.
            </p>
        </div>
    <?php elseif ($exchanged === false) : ?>
        <div class="notice notice-error">
            <p>
                <strong>Algo deu errado! </strong>Tente novamente e confira suas informações, caso o erro ainda persista, contate os desenvolvedores responsáveis.
            </p>
        </div>
    <?php endif; ?>

    <form method="POST" id="conta_azul_config">
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="ca_client_id">Client ID</label>
                    </th>
                    <td>
                        <input type="text" value="<?= $env->getClientId() ?>" name="ca_client_id" id="ca_client_id" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ca_client_secret">Client Secret</label>
                    </th>
                    <td>
                        <input type="text" value="<?= $env->getClientSecret() ?>" name="ca_client_secret" id="ca_client_secret" class="regular-text" required>
                        <input type="hidden" value="<?= $env->getStateKey() ?>" name="ca_state_key">
                    </td>
                </tr>
            </tbody>
        </table>
        <input type="hidden" name="action" value="ca_save_app_info">
        <?php wp_nonce_field('ca_save_app_info'); ?>
        <button class="button-primary">Salvar configurações</button>
    </form>

    <?php if ($env->getClientId() && $env->getClientSecret() && !$api->isAvailable()) : ?>
        <div class="connnect-conta-azul-wrap">
            <h3>Conecte com a Conta Azul</h3>
            <a href="<?= $api->getAuthorizationUrl('/auth/authorize', $env->getRedirectUri()) ?>" class="connect-btn button-primary">Conectar</a>
        </div>
    <?php endif; ?>

</div>