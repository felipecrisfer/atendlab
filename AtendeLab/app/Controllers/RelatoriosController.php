<?php

declare(strict_types=1);

class RelatoriosController
{
    private PDO $pdo;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    public function atendimentos(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $dataInicio = trim((string) ($_GET['data_inicio'] ?? ''));
        $dataFim = trim((string) ($_GET['data_fim'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));
        $tipoId = trim((string) ($_GET['tipo_atendimento_id'] ?? ''));
        $usuarioId = trim((string) ($_GET['usuario_id'] ?? ''));

        $sql = "SELECT
                    a.id,
                    CONCAT('ATD-', LPAD(a.id, 4, '0')) AS protocolo,
                    p.nome AS pessoa_nome,
                    ta.nome AS tipo_atendimento_nome,
                    u.nome AS responsavel_nome,
                    a.data_atendimento,
                    a.horario_atendimento,
                    a.status,
                    a.descricao,
                    a.observacao_final
                FROM atendimentos a
                INNER JOIN pessoas p ON p.id = a.pessoa_id
                INNER JOIN tipos_atendimentos ta ON ta.id = a.tipo_atendimento_id
                INNER JOIN usuarios u ON u.id = a.usuario_id
                WHERE 1 = 1";
        $parametros = [];

        if ($dataInicio !== '') {
            $this->validarData($dataInicio, 'data_inicio');
            $sql .= ' AND a.data_atendimento >= :data_inicio';
            $parametros[':data_inicio'] = $dataInicio;
        }

        if ($dataFim !== '') {
            $this->validarData($dataFim, 'data_fim');
            $sql .= ' AND a.data_atendimento <= :data_fim';
            $parametros[':data_fim'] = $dataFim;
        }

        if ($status !== '') {
            if (!in_array($status, ['aberto', 'em_andamento', 'concluido'], true)) {
                $this->erro('Status inválido.', 422);
                return;
            }
            $sql .= ' AND a.status = :status';
            $parametros[':status'] = $status;
        }

        if ($tipoId !== '') {
            $sql .= ' AND a.tipo_atendimento_id = :tipo_id';
            $parametros[':tipo_id'] = $this->inteiroValido($tipoId, 'tipo_atendimento_id');
        }

        if ($usuarioId !== '') {
            $sql .= ' AND a.usuario_id = :usuario_id';
            $parametros[':usuario_id'] = $this->inteiroValido($usuarioId, 'usuario_id');
        }

        $sql .= ' ORDER BY a.data_atendimento DESC, a.horario_atendimento DESC, a.id DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $resumo = [
            'total' => count($registros),
            'abertos' => 0,
            'em_andamento' => 0,
            'concluidos' => 0,
        ];

        foreach ($registros as $registro) {
            if ($registro['status'] === 'aberto') {
                $resumo['abertos']++;
            } elseif ($registro['status'] === 'em_andamento') {
                $resumo['em_andamento']++;
            } elseif ($registro['status'] === 'concluido') {
                $resumo['concluidos']++;
            }
        }

        echo json_encode([
            'filtros' => [
                'data_inicio' => $dataInicio !== '' ? $dataInicio : null,
                'data_fim' => $dataFim !== '' ? $dataFim : null,
                'status' => $status !== '' ? $status : null,
                'tipo_atendimento_id' => $tipoId !== '' ? (int) $tipoId : null,
                'usuario_id' => $usuarioId !== '' ? (int) $usuarioId : null,
            ],
            'resumo' => $resumo,
            'registros' => $registros,
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private function validarData(string $data, string $campo): void
    {
        $objeto = DateTime::createFromFormat('Y-m-d', $data);
        if ($objeto === false || $objeto->format('Y-m-d') !== $data) {
            $this->erro("O campo {$campo} deve utilizar o formato YYYY-MM-DD.", 422);
            exit;
        }
    }

    private function inteiroValido(mixed $valor, string $campo): int
    {
        $inteiro = filter_var($valor, FILTER_VALIDATE_INT);
        if (!$inteiro || $inteiro < 1) {
            $this->erro("O campo {$campo} deve conter um ID válido.", 422);
            exit;
        }

        return (int) $inteiro;
    }

    private function erro(string $mensagem, int $codigo): void
    {
        http_response_code($codigo);
        echo json_encode(['erro' => $mensagem], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
