<?php
// Proteção CSRF: cada sessão tem um token secreto que vai num campo hidden
// de todos os formulários POST e é verificado antes de executar a ação.
// Um site externo não conhece o token, por isso não consegue forjar o pedido.

function tokenCsrf() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function campoCsrf() {
    return '<input type="hidden" name="csrf_token" value="' . tokenCsrf() . '">';
}

// Termina o pedido se o token enviado não for igual ao da sessão
function validarCsrf() {
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || !hash_equals(tokenCsrf(), $token)) {
        http_response_code(403);
        exit("Pedido inválido.");
    }
}
