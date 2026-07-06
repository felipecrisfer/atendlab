<?php

declare(strict_types=1);

class AtendimentosController
{
    private PDO $pdo;
    private bool $usaSchemaNovo;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
        $this->usaSchemaNovo = $this->detectarSchemaNovoAtendimentos();
    }

    public function listar(): void
    {
        $this->cabecalhoJson();

        $busca = trim((string) ($_GET['busca'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));
        $tipoId = trim((string) ($_GET['tipo_atendimento_id'] ?? ''));
        $usuarioId = trim((string) ($_GET['usuario_id'] ?? ''));
        $dataInicio = trim((string) ($_GET['data_inicio'] ?? ''));
        $dataFim = trim((string) ($_GET['data_fim'] ?? ''));

        [$sql, $parametros] = $this->montarConsultaBaseComFiltros(
            $busca,
            $status,
            $tipoId,
            $usuarioId,
            $dataInicio,
            $dataFim
        );

        $sql .= ' ORDER BY a.data_atendimento DESC, a.'
            . $this->colunaHorarioAtendimento()
            . ' DESC, a.id DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);

        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function visualizar(): void
    {
        $this->cabecalhoJson();
        $id = $this->obterId($_GET);

        $sql = $this->consultaBase() . ' WHERE a.id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $atendimento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$atendimento) {
            $this->responderErro('Atendimento não encontrado.', 404);
            return;
        }

        echo json_encode($atendimento, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function criar(): void
    {
        $this->cabecalhoJson();
        $this->exigirMetodoPost();

        $pessoaId = $this->inteiroObrigatorio($_POST['pessoa_id'] ?? null, 'pessoa_id');
        $tipoId = $this->inteiroObrigatorio($_POST['tipo_atendimento_id'] ?? null, 'tipo_atendimento_id');
        $usuarioId = $this->usuarioResponsavel();
        $descricao = trim((string) ($_POST['descricao'] ?? ''));
        $data = trim((string) ($_POST['data_atendimento'] ?? ''));
        $horario = trim((string) ($_POST['horario_atendimento'] ?? ''));

        if ($descricao === '' || $data === '' || $horario === '') {
            $this->responderErro('Pessoa, tipo, descrição, data e horário são obrigatórios.', 422);
            return;
        }

        if (!$this->dataValida($data)) {
            $this->responderErro('Data inválida. Utilize o formato YYYY-MM-DD.', 422);
            return;
        }

        if (!$this->horarioValido($horario)) {
            $this->responderErro('Horário inválido. Utilize o formato HH:MM ou HH:MM:SS.', 422);
            return;
        }

        if (!$this->registroAtivoExiste('pessoas', $pessoaId)) {
            $this->responderErro('A pessoa informada não existe ou está inativa.', 422);
            return;
        }

        if (!$this->registroAtivoExiste('tipos_atendimentos', $tipoId)) {
            $this->responderErro('O tipo de atendimento informado não existe ou está inativo.', 422);
            return;
        }

        if (!$this->registroAtivoExiste('usuarios', $usuarioId)) {
            $this->responderErro('O usuário responsável não existe ou está inativo.', 422);
            return;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO atendimentos
                (pessoa_id, tipo_atendimento_id, '
                . $this->colunaUsuarioResponsavel()
                . ', '
                . $this->colunaDescricao()
                . ', status, data_atendimento, '
                . $this->colunaHorarioAtendimento()
                . ')
             VALUES
                (:pessoa_id, :tipo_atendimento_id, :usuario_id, :descricao, :status, :data_atendimento, :horario_atendimento)'
        );
        $stmt->execute([
            ':pessoa_id' => $pessoaId,
            ':tipo_atendimento_id' => $tipoId,
            ':usuario_id' => $usuarioId,
            ':descricao' => $descricao,
            ':status' => $this->statusAplicacaoParaBanco('aberto'),
            ':data_atendimento' => $data,
            ':horario_atendimento' => $horario,
        ]);

        $id = (int) $this->pdo->lastInsertId();
        http_response_code(201);
        echo json_encode([
            'mensagem' => 'Atendimento registrado com sucesso.',
            'id' => $id,
            'protocolo' => $this->formatarProtocolo($id),
            'status' => 'aberto',
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function atualizarStatus(): void
    {
        $this->cabecalhoJson();
        $this->exigirMetodoPost();

        $id = $this->obterId($_POST);
        $status = trim((string) ($_POST['status'] ?? ''));
        $observacaoFinal = trim((string) ($_POST['observacao_final'] ?? ''));

        if (!$this->statusValido($status)) {
            $this->responderErro('Status inválido. Use aberto, em_andamento ou concluido.', 422);
            return;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE atendimentos
             SET status = :status,
                 ' . $this->colunaObservacaoFinal() . ' = :observacao_final
             WHERE id = :id'
        );
        $stmt->execute([
            ':status' => $this->statusAplicacaoParaBanco($status),
            ':observacao_final' => $status === 'concluido' && $observacaoFinal !== '' ? $observacaoFinal : null,
            ':id' => $id,
        ]);

        if ($stmt->rowCount() === 0 && !$this->atendimentoExiste($id)) {
            $this->responderErro('Atendimento não encontrado.', 404);
            return;
        }

        echo json_encode(['mensagem' => 'Status do atendimento atualizado com sucesso.'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function opcoesFormulario(): void
    {
        $this->cabecalhoJson();

        $pessoas = $this->pdo->query("SELECT id, nome FROM pessoas WHERE status = 'ativo' ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
        $tipos = $this->pdo->query("SELECT id, nome FROM tipos_atendimentos WHERE status = 'ativo' ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
        $usuarios = $this->pdo->query("SELECT id, nome, perfil FROM usuarios WHERE status = 'ativo' ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'pessoas' => $pessoas,
            'tipos_atendimentos' => $tipos,
            'usuarios_responsaveis' => $usuarios,
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private function montarConsultaBaseComFiltros(
        string $busca,
        string $status,
        string $tipoId,
        string $usuarioId,
        string $dataInicio,
        string $dataFim
    ): array {
        $sql = $this->consultaBase() . ' WHERE 1 = 1';
        $parametros = [];

        if ($busca !== '') {
            $somenteNumero = preg_replace('/\D+/', '', $busca);
            $sql .= ' AND (p.nome LIKE :busca OR CONCAT("ATD-", LPAD(a.id, 4, "0")) LIKE :busca';
            if ($somenteNumero !== '') {
                $sql .= ' OR a.id = :id_busca';
                $parametros[':id_busca'] = (int) $somenteNumero;
            }
            $sql .= ')';
            $parametros[':busca'] = '%' . $busca . '%';
        }

        if ($status !== '') {
            if (!$this->statusValido($status)) {
                $this->responderErro('Status inválido. Use aberto, em_andamento ou concluido.', 422);
                exit;
            }
            $sql .= ' AND a.status = :status';
            $parametros[':status'] = $this->statusAplicacaoParaBanco($status);
        }

        if ($tipoId !== '') {
            $sql .= ' AND a.tipo_atendimento_id = :tipo_id';
            $parametros[':tipo_id'] = $this->inteiroObrigatorio($tipoId, 'tipo_atendimento_id');
        }

        if ($usuarioId !== '') {
            $sql .= ' AND a.' . $this->colunaUsuarioResponsavel() . ' = :usuario_id';
            $parametros[':usuario_id'] = $this->inteiroObrigatorio($usuarioId, 'usuario_id');
        }

        if ($dataInicio !== '') {
            if (!$this->dataValida($dataInicio)) {
                $this->responderErro('Data inicial inválida. Utilize YYYY-MM-DD.', 422);
                exit;
            }
            $sql .= ' AND a.data_atendimento >= :data_inicio';
            $parametros[':data_inicio'] = $dataInicio;
        }

        if ($dataFim !== '') {
            if (!$this->dataValida($dataFim)) {
                $this->responderErro('Data final inválida. Utilize YYYY-MM-DD.', 422);
                exit;
            }
            $sql .= ' AND a.data_atendimento <= :data_fim';
            $parametros[':data_fim'] = $dataFim;
        }

        return [$sql, $parametros];
    }

    private function consultaBase(): string
    {
        return 'SELECT
                    a.id,
                    CONCAT("ATD-", LPAD(a.id, 4, "0")) AS protocolo,
                    a.pessoa_id,
                    p.nome AS pessoa_nome,
                    a.tipo_atendimento_id,
                    ta.nome AS tipo_atendimento_nome,
                    a.' . $this->colunaUsuarioResponsavel() . ' AS usuario_id,
                    u.nome AS responsavel_nome,
                    a.' . $this->colunaDescricao() . ' AS descricao,
                    CASE
                        WHEN a.status = "agendado" THEN "aberto"
                        ELSE a.status
                    END AS status,
                    a.data_atendimento,
                    a.' . $this->colunaHorarioAtendimento() . ' AS horario_atendimento,
                    a.' . $this->colunaObservacaoFinal() . ' AS observacao_final,
                    a.criado_em,
                    a.atualizado_em
                FROM atendimentos a
                INNER JOIN pessoas p ON p.id = a.pessoa_id
                INNER JOIN tipos_atendimentos ta ON ta.id = a.tipo_atendimento_id
                INNER JOIN usuarios u ON u.id = a.' . $this->colunaUsuarioResponsavel();
    }

    private function usuarioResponsavel(): int
    {
        if (isset($_SESSION['usuario']['id'])) {
            return (int) $_SESSION['usuario']['id'];
        }

        return $this->inteiroObrigatorio($_POST['usuario_id'] ?? null, 'usuario_id');
    }

    private function registroAtivoExiste(string $tabela, int $id): bool
    {
        $tabelasPermitidas = ['pessoas', 'tipos_atendimentos', 'usuarios'];
        if (!in_array($tabela, $tabelasPermitidas, true)) {
            return false;
        }

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM {$tabela} WHERE id = :id AND status = 'ativo'");
        $stmt->execute([':id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function atendimentoExiste(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM atendimentos WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function statusValido(string $status): bool
    {
        return in_array($status, ['aberto', 'em_andamento', 'concluido'], true);
    }

    private function statusAplicacaoParaBanco(string $status): string
    {
        if ($status === 'aberto' && !$this->usaSchemaNovo) {
            return 'agendado';
        }

        return $status;
    }

    private function detectarSchemaNovoAtendimentos(): bool
    {
        $stmt = $this->pdo->query('SHOW COLUMNS FROM atendimentos');
        $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);

        return in_array('usuario_id', $colunas, true)
            && in_array('horario_atendimento', $colunas, true)
            && in_array('descricao', $colunas, true)
            && in_array('observacao_final', $colunas, true);
    }

    private function colunaUsuarioResponsavel(): string
    {
        return $this->usaSchemaNovo ? 'usuario_id' : 'atendente_id';
    }

    private function colunaHorarioAtendimento(): string
    {
        return $this->usaSchemaNovo ? 'horario_atendimento' : 'hora_inicio';
    }

    private function colunaDescricao(): string
    {
        return $this->usaSchemaNovo ? 'descricao' : 'observacoes';
    }

    private function colunaObservacaoFinal(): string
    {
        return $this->usaSchemaNovo ? 'observacao_final' : 'resultado';
    }

    private function dataValida(string $data): bool
    {
        $objeto = DateTime::createFromFormat('Y-m-d', $data);
        return $objeto !== false && $objeto->format('Y-m-d') === $data;
    }

    private function horarioValido(string $horario): bool
    {
        foreach (['H:i', 'H:i:s'] as $formato) {
            $objeto = DateTime::createFromFormat($formato, $horario);
            if ($objeto !== false && $objeto->format($formato) === $horario) {
                return true;
            }
        }

        return false;
    }

    private function formatarProtocolo(int $id): string
    {
        return 'ATD-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
    }

    private function inteiroObrigatorio(mixed $valor, string $campo): int
    {
        $inteiro = filter_var($valor, FILTER_VALIDATE_INT);
        if (!$inteiro || $inteiro < 1) {
            $this->responderErro("O campo {$campo} deve conter um ID válido.", 422);
            exit;
        }

        return (int) $inteiro;
    }

    private function obterId(array $origem): int
    {
        return $this->inteiroObrigatorio($origem['id'] ?? null, 'id');
    }

    private function exigirMetodoPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responderErro('Método não permitido. Utilize POST com x-www-form-urlencoded.', 405);
            exit;
        }
    }

    private function cabecalhoJson(): void
    {
        header('Content-Type: application/json; charset=utf-8');
    }

    private function responderErro(string $mensagem, int $codigo): void
    {
        http_response_code($codigo);
        echo json_encode(['erro' => $mensagem], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
