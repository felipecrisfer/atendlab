<?php

declare(strict_types=1);

require_once __DIR__ . '/app/Middleware/auth.php';

$controller = $_GET['controller'] ?? 'auth';
$action = $_GET['action'] ?? 'login';


switch ($controller) {
    case 'auth':
        require_once __DIR__ . '/app/Controllers/AuthController.php';
        $authController = new AuthController();

        switch ($action) {
            case 'login':
                $authController->exibirLogin();
                break;
            case 'entrar':
                $authController->entrar();
                break;
            case 'dashboard':
                $authController->dashboard();
                break;
            case 'logout':
                $authController->logout();
                break;
            default:
                http_response_code(404);
                echo 'Ação de autenticação não encontrada.';
        }
        break;

    case 'usuarios':
        exigirAutenticacao();
        require_once __DIR__ . '/app/Controllers/UsuariosController.php';
        $usuariosController = new UsuariosController();

        switch ($action) {
            case 'listar':
                $usuariosController->listar();
                break;
            case 'buscarPorId':
                $usuariosController->buscarPorId();
                break;
            case 'criar':
                $usuariosController->criar();
                break;
            case 'atualizar':
                $usuariosController->atualizar();
                break;
            case 'excluir':
                $usuariosController->excluir();
                break;
            default:
                responderRotaNaoEncontrada('Ação de usuários não encontrada.');
        }
        break;
    case 'pessoas':
        exigirAutenticacao();

        require_once __DIR__ . '/app/Controllers/PessoasController.php';

        $pessoasController = new PessoasController();

        switch ($action) {
            case 'listar':
                $pessoasController->listar();
                break;

            /*
            |--------------------------------------------------------------------------
            | BUSCAR PESSOA
            |--------------------------------------------------------------------------
            | Aceita as duas formas para manter compatibilidade:
            | - action=buscar
            | - action=buscarPorId
            */
            case 'buscar':
            case 'buscarPorId':
                $pessoasController->buscarPorId();
                break;

            case 'criar':
                $pessoasController->criar();
                break;

            case 'atualizar':
                $pessoasController->atualizar();
                break;

            /*
            |--------------------------------------------------------------------------
            | INATIVAR PESSOA
            |--------------------------------------------------------------------------
            | O projeto preserva o histórico.
            | Mesmo quando a tela envia action=excluir, o backend executa inativação.
            */
            case 'excluir':
            case 'inativar':
                $pessoasController->inativar();
                break;

            default:
                responderRotaNaoEncontrada(
                    'Ação de pessoas não encontrada.'
                );
        }

        break;

    case 'tipos':
        exigirAutenticacao();
        require_once __DIR__ . '/app/Controllers/TiposAtendimentosController.php';
        $tiposController = new TiposAtendimentosController();

        switch ($action) {
            case 'listar':
                $tiposController->listar();
                break;
            case 'buscarPorId':
                $tiposController->buscarPorId();
                break;
            case 'criar':
                $tiposController->criar();
                break;
            case 'atualizar':
                $tiposController->atualizar();
                break;
            case 'inativar':
                $tiposController->inativar();
                break;
            default:
                responderRotaNaoEncontrada('Ação de tipos de atendimento não encontrada.');
        }
        break;

    case 'atendimentos':
        exigirAutenticacao();
        require_once __DIR__
            . '/app/Controllers/AtendimentosController.php';
        $atendimentosController = new AtendimentosController();
        switch ($action) {
            case 'listar':
                $atendimentosController->listar();
                break;
            case 'visualizar':
                $atendimentosController->visualizar();
                break;
            case 'criar':
                $atendimentosController->criar();
                break;
            case 'alterarStatus':
            case 'atualizarStatus':
                $atendimentosController->atualizarStatus();
                break;
            case 'opcoesFormulario':
                $atendimentosController->opcoesFormulario();
                break;
            default:
                responderRotaNaoEncontrada(
                    'Ação de atendimentos não encontrada.'
                );
        }
        break;

        switch ($action) {
            case 'listar':
                $atendimentosController->listar();
                break;

            case 'visualizar':
                $atendimentosController->visualizar();
                break;

            case 'criar':
                $atendimentosController->criar();
                break;


            case 'alterarStatus':
            case 'atualizarStatus':
                $atendimentosController->atualizarStatus();
                break;

            case 'opcoesFormulario':
                $atendimentosController->opcoesFormulario();
                break;

            default:
                responderRotaNaoEncontrada(
                    'Ação de atendimentos não encontrada.'
                );
        }

    case 'dashboard':
        exigirAutenticacao();
        require_once __DIR__ . '/app/Controllers/DashboardController.php';
        $dashboardController = new DashboardController();

        switch ($action) {
            case 'resumo':
                $dashboardController->resumo();
                break;
            default:
                responderRotaNaoEncontrada('Ação do dashboard não encontrada.');
        }
        break;

    case 'relatorios':
        exigirAutenticacao();
        require_once __DIR__ . '/app/Controllers/RelatoriosController.php';
        $relatoriosController = new RelatoriosController();

        switch ($action) {
            case 'atendimentos':
                $relatoriosController->atendimentos();
                break;
            default:
                responderRotaNaoEncontrada('Ação de relatórios não encontrada.');
        }
        break;

    case 'frontend':
        require_once __DIR__ . '/app/Controllers/FrontendController.php';

        $frontendController = new FrontendController();

        switch ($action) {
            case 'pessoas':
                $frontendController->pessoas();
                break;

            case 'tipos':
                $frontendController->tiposAtendimentos();
                break;

            case 'atendimentos':
                $frontendController->atendimentos();
                break;

            default:
                responderRotaNaoEncontrada(
                    'Página visual não encontrada.'
                );
        }

        break;

    default:
        responderRotaNaoEncontrada('Controller não encontrado.');
}

function responderRotaNaoEncontrada(string $mensagem): void
{
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(404);
    echo json_encode(['erro' => $mensagem], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
