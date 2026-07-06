<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function usuarioAutenticado(): bool
{
    return isset($_SESSION['usuario'])
        && is_array($_SESSION['usuario'])
        && isset($_SESSION['usuario']['id']);
}
function requisicaoEsperaJson(): bool
{
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';

    return str_contains($accept, 'application/json');
}
function exigirAutenticacao(): void
{
    if (usuarioAutenticado()) {
        return;
    }

    if (requisicaoEsperaJson()) {
        header('Content-Type: application/json; charset=utf-8');

        http_response_code(401);

        echo json_encode(
            [
                'erro' => true,
                'mensagem' => 'Usuário não autenticado.'
            ],
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }

    $_SESSION['mensagem'] =
        'Faça login para acessar a área restrita.';

    header('Location: ?controller=auth&action=login');

    exit;
}
function usuarioAtual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}


