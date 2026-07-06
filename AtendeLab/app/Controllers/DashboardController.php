<?php

declare(strict_types=1);

class DashboardController
{
    private PDO $pdo;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }
    public function resumo(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $indicadoresAtendimentos = $this->pdo->query(
            "SELECT
            COUNT(*) AS total_atendimentos,

            COALESCE(
                SUM(status = 'aberto'),
                0
            ) AS total_abertos,

            COALESCE(
                SUM(status = 'em_andamento'),
                0
            ) AS total_em_andamento,

            COALESCE(
                SUM(status = 'concluido'),
                0
            ) AS total_concluidos,

            COALESCE(
                SUM(
                    status = 'concluido'
                    AND YEAR(data_atendimento) = YEAR(CURRENT_DATE)
                    AND MONTH(data_atendimento) = MONTH(CURRENT_DATE)
                ),
                0
            ) AS concluidos_no_mes
         FROM atendimentos"
        )->fetch(PDO::FETCH_ASSOC);

        $totalPessoas = $this->pdo->query(
            "SELECT COUNT(*) AS total
         FROM pessoas"
        )->fetch(PDO::FETCH_ASSOC);

        $totalTipos = $this->pdo->query(
            "SELECT COUNT(*) AS total
         FROM tipos_atendimentos"
        )->fetch(PDO::FETCH_ASSOC);

        $recentes = $this->pdo->query(
            "SELECT
            a.id,
            CONCAT('ATD-', LPAD(a.id, 4, '0')) AS protocolo,
            p.nome AS pessoa_nome,
            ta.nome AS tipo_atendimento_nome,
            u.nome AS responsavel_nome,
            a.data_atendimento,
            a.horario_atendimento,
            a.status
         FROM atendimentos a
         INNER JOIN pessoas p
            ON p.id = a.pessoa_id
         INNER JOIN tipos_atendimentos ta
            ON ta.id = a.tipo_atendimento_id
         INNER JOIN usuarios u
            ON u.id = a.usuario_id
         ORDER BY
            a.data_atendimento DESC,
            a.horario_atendimento DESC,
            a.id DESC
         LIMIT 5"
        )->fetchAll(PDO::FETCH_ASSOC);

        $categorias = $this->pdo->query(
            "SELECT
            ta.id,
            ta.nome,
            COUNT(a.id) AS total
         FROM tipos_atendimentos ta
         LEFT JOIN atendimentos a
            ON a.tipo_atendimento_id = ta.id
         GROUP BY
            ta.id,
            ta.nome
         ORDER BY
            total DESC,
            ta.nome ASC"
        )->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(
            [
                'indicadores' => [
                    'total_pessoas' =>
                        (int) ($totalPessoas['total'] ?? 0),

                    'total_tipos' =>
                        (int) ($totalTipos['total'] ?? 0),

                    'total_atendimentos' =>
                        (int) (
                            $indicadoresAtendimentos['total_atendimentos']
                            ?? 0
                        ),

                    'total_abertos' =>
                        (int) (
                            $indicadoresAtendimentos['total_abertos']
                            ?? 0
                        ),

                    'total_em_andamento' =>
                        (int) (
                            $indicadoresAtendimentos['total_em_andamento']
                            ?? 0
                        ),

                    'total_concluidos' =>
                        (int) (
                            $indicadoresAtendimentos['total_concluidos']
                            ?? 0
                        ),

                    'concluidos_no_mes' =>
                        (int) (
                            $indicadoresAtendimentos['concluidos_no_mes']
                            ?? 0
                        ),
                ],

                'atendimentos_recentes' => $recentes,

                'atendimentos_por_categoria' => $categorias,
            ],
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );
    }

}
