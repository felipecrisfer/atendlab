<?php

declare(strict_types=1);

require_once __DIR__ . '/../Middleware/auth.php';

/**
 * Controller mínimo para servir as páginas HTML/PHP da integração visual.
 * As operações de banco continuam nos controllers já criados nas aulas.
 */
class FrontendController
{
    public function __construct()
    {
        exigirAutenticacao();
    }

    public function pessoas(): void
    {
        require __DIR__ . '/../Views/pessoas/index.php';
    }

    public function tiposAtendimentos(): void
    {
        require __DIR__ . '/../Views/tipos-atendimentos/index.php';
    }

    public function atendimentos(): void
    {
        require __DIR__ . '/../Views/atendimentos/index.php';
    }
}
