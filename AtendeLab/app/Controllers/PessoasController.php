<?php

declare(strict_types=1);

class PessoasController
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
        $curso = trim((string) ($_GET['curso'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));

        $sql = 'SELECT id, nome, documento, telefone, email, curso, periodo, observacoes, status, criado_em, atualizado_em
                FROM pessoas
                WHERE 1 = 1';
        $parametros = [];

        if ($busca !== '') {
            $sql .= ' AND (nome LIKE :busca OR documento LIKE :busca OR email LIKE :busca)';
            $parametros[':busca'] = '%' . $busca . '%';
        }

        if ($curso !== '') {
            $sql .= ' AND curso = :curso';
            $parametros[':curso'] = $curso;
        }

        if ($status !== '') {
            if (!$this->statusValido($status)) {
                $this->responderErro('Status inválido. Use ativo ou inativo.', 422);
                return;
            }

            $sql .= ' AND status = :status';
            $parametros[':status'] = $status;
        }

        $sql .= ' ORDER BY nome ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);

        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function buscarPorId(): void
    {
        $this->cabecalhoJson();
        $id = $this->obterId($_GET);

        $stmt = $this->pdo->prepare('SELECT * FROM pessoas WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $pessoa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pessoa) {
            $this->responderErro('Pessoa não encontrada.', 404);
            return;
        }

        echo json_encode($pessoa, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function criar(): void
    {
        $this->cabecalhoJson();
        $this->exigirMetodoPost();

        $dados = $this->validarDados($_POST);

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO pessoas (nome, documento, telefone, email, curso, periodo, status, observacoes)
                 VALUES (:nome, :documento, :telefone, :email, :curso, :periodo, :status, :observacoes)'
            );
            $stmt->execute($dados);

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Pessoa cadastrada com sucesso.',
                'id' => (int) $this->pdo->lastInsertId(),
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (PDOException $e) {
            if ($this->ehErroDuplicidade($e)) {
                $this->responderErro('Já existe uma pessoa cadastrada com este documento.', 409);
                return;
            }

            $this->responderErro('Erro ao cadastrar pessoa.', 500);
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
                'UPDATE pessoas
                SET nome = :nome,
                    documento = :documento,
                    telefone = :telefone,
                    email = :email,
                    curso = :curso,
                    periodo = :periodo,
                    status = :status,
                    observacoes = :observacoes
                WHERE id = :id'
            );
            $stmt->execute($dados);

            if ($stmt->rowCount() === 0 && !$this->existe($id)) {
                $this->responderErro('Pessoa não encontrada.', 404);
                return;
            }

            echo json_encode(['mensagem' => 'Pessoa atualizada com sucesso.'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (PDOException $e) {
            if ($this->ehErroDuplicidade($e)) {
                $this->responderErro('Já existe uma pessoa cadastrada com este documento.', 409);
                return;
            }

            $this->responderErro('Erro ao atualizar pessoa.', 500);
        }
    }

    public function inativar(): void
    {
        $this->cabecalhoJson();
        $this->exigirMetodoPost();
        $id = $this->obterId($_POST);

        $stmt = $this->pdo->prepare("UPDATE pessoas SET status = 'inativo' WHERE id = :id");
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() === 0 && !$this->existe($id)) {
            $this->responderErro('Pessoa não encontrada.', 404);
            return;
        }

        echo json_encode(['mensagem' => 'Pessoa inativada com sucesso.'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private function validarDados(array $origem): array
    {
        $nome = trim((string) ($origem['nome'] ?? ''));
        $documento = trim((string) ($origem['documento'] ?? ''));
        $telefone = trim((string) ($origem['telefone'] ?? ''));
        $email = trim((string) ($origem['email'] ?? ''));
        $curso = trim((string) ($origem['curso'] ?? ''));
        $periodo = trim((string) ($origem['periodo'] ?? ''));
        $status = trim((string) ($origem['status'] ?? 'ativo'));
        $observacoes = trim((string) ($origem['observacoes'] ?? ''));

        if ($nome === '' || $documento === '' || $email === '') {
            $this->responderErro('Nome, documento e e-mail são obrigatórios.', 422);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->responderErro('Informe um e-mail válido.', 422);
            exit;
        }

        if (!$this->statusValido($status)) {
            $this->responderErro('Status inválido. Use ativo ou inativo.', 422);
            exit;
        }

        return [
            ':nome' => $nome,
            ':documento' => $documento,
            ':telefone' => $telefone !== '' ? $telefone : null,
            ':email' => $email,
            ':curso' => $curso !== '' ? $curso : null,
            ':periodo' => $periodo !== '' ? $periodo : null,
            ':status' => $status,
            ':observacoes' => $observacoes !== '' ? $observacoes : null,
        ];
    }

    private function existe(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM pessoas WHERE id = :id');
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
