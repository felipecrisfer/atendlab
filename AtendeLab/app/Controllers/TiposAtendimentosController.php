<?php

declare(strict_types=1);

class TiposAtendimentosController
{
    private PDO $pdo;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    public function listar(): void
    {
        $this->cabecalhoJson();

        $busca = trim((string) ($_GET['busca'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));

        $sql = 'SELECT ta.id, ta.nome, ta.descricao, ta.status, ta.criado_em, ta.atualizado_em,
                       COUNT(a.id) AS total_atendimentos
                FROM tipos_atendimentos ta
                LEFT JOIN atendimentos a ON a.tipo_atendimento_id = ta.id
                WHERE 1 = 1';
        $parametros = [];

        if ($busca !== '') {
            $sql .= ' AND (ta.nome LIKE :busca OR ta.descricao LIKE :busca)';
            $parametros[':busca'] = '%' . $busca . '%';
        }

        if ($status !== '') {
            if (!$this->statusValido($status)) {
                $this->responderErro('Status inválido. Use ativo ou inativo.', 422);
                return;
            }

            $sql .= ' AND ta.status = :status';
            $parametros[':status'] = $status;
        }

        $sql .= ' GROUP BY ta.id, ta.nome, ta.descricao, ta.status, ta.criado_em, ta.atualizado_em
                  ORDER BY ta.nome ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);

        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function buscarPorId(): void
    {
        $this->cabecalhoJson();
        $id = $this->obterId($_GET);

        $stmt = $this->pdo->prepare('SELECT * FROM tipos_atendimentos WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $tipo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tipo) {
            $this->responderErro('Tipo de atendimento não encontrado.', 404);
            return;
        }

        echo json_encode($tipo, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function criar(): void
    {
        $this->cabecalhoJson();
        $this->exigirMetodoPost();
        $dados = $this->validarDados($_POST);

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO tipos_atendimentos (nome, descricao, status)
                 VALUES (:nome, :descricao, :status)'
            );
            $stmt->execute($dados);

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Tipo de atendimento cadastrado com sucesso.',
                'id' => (int) $this->pdo->lastInsertId(),
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (PDOException $e) {
            if ($this->ehErroDuplicidade($e)) {
                $this->responderErro('Já existe um tipo de atendimento com este nome.', 409);
                return;
            }

            $this->responderErro('Erro ao cadastrar tipo de atendimento.', 500);
        }
    }

    public function atualizar(): void
    {
        $this->cabecalhoJson();
        $this->exigirMetodoPost();

        $id = $this->obterId($_POST);
        $dados = $this->validarDados($_POST);
        $dados[':id'] = $id;

        try {
            $stmt = $this->pdo->prepare(
                'UPDATE tipos_atendimentos
                 SET nome = :nome,
                     descricao = :descricao,
                     status = :status
                 WHERE id = :id'
            );
            $stmt->execute($dados);

            if ($stmt->rowCount() === 0 && !$this->existe($id)) {
                $this->responderErro('Tipo de atendimento não encontrado.', 404);
                return;
            }

            echo json_encode(['mensagem' => 'Tipo de atendimento atualizado com sucesso.'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (PDOException $e) {
            if ($this->ehErroDuplicidade($e)) {
                $this->responderErro('Já existe um tipo de atendimento com este nome.', 409);
                return;
            }

            $this->responderErro('Erro ao atualizar tipo de atendimento.', 500);
        }
    }

    public function inativar(): void
    {
        $this->cabecalhoJson();
        $this->exigirMetodoPost();
        $id = $this->obterId($_POST);

        $stmt = $this->pdo->prepare("UPDATE tipos_atendimentos SET status = 'inativo' WHERE id = :id");
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() === 0 && !$this->existe($id)) {
            $this->responderErro('Tipo de atendimento não encontrado.', 404);
            return;
        }

        echo json_encode(['mensagem' => 'Tipo de atendimento inativado com sucesso.'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private function validarDados(array $origem): array
    {
        $nome = trim((string) ($origem['nome'] ?? ''));
        $descricao = trim((string) ($origem['descricao'] ?? ''));
        $status = trim((string) ($origem['status'] ?? 'ativo'));

        if ($nome === '') {
            $this->responderErro('O nome do tipo de atendimento é obrigatório.', 422);
            exit;
        }

        if (!$this->statusValido($status)) {
            $this->responderErro('Status inválido. Use ativo ou inativo.', 422);
            exit;
        }

        return [
            ':nome' => $nome,
            ':descricao' => $descricao !== '' ? $descricao : null,
            ':status' => $status,
        ];
    }

    private function existe(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM tipos_atendimentos WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function statusValido(string $status): bool
    {
        return in_array($status, ['ativo', 'inativo'], true);
    }

    private function obterId(array $origem): int
    {
        $id = filter_var($origem['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            $this->responderErro('ID inválido.', 422);
            exit;
        }

        return (int) $id;
    }

    private function exigirMetodoPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responderErro('Método não permitido. Utilize POST com x-www-form-urlencoded.', 405);
            exit;
        }
    }

    private function ehErroDuplicidade(PDOException $e): bool
    {
        return $e->getCode() === '23000';
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
