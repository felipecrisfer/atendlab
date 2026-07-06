-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 06/07/2026 às 02:15
-- Versão do servidor: 10.4.28-MariaDB
-- Versão do PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `atendelab`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `atendimentos`
--

CREATE TABLE `atendimentos` (
  `id` int(11) NOT NULL,
  `pessoa_id` int(11) NOT NULL,
  `tipo_atendimento_id` int(11) NOT NULL,
  `atendente_id` int(11) NOT NULL,
  `data_atendimento` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fim` time DEFAULT NULL,
  `modalidade` enum('presencial','remoto','hibrido') DEFAULT 'presencial',
  `observacoes` text DEFAULT NULL,
  `resultado` text DEFAULT NULL,
  `status` enum('agendado','em_andamento','concluido','cancelado','nao_compareceu') DEFAULT 'agendado',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `atendimentos`
--

INSERT INTO `atendimentos` (`id`, `pessoa_id`, `tipo_atendimento_id`, `atendente_id`, `data_atendimento`, `hora_inicio`, `hora_fim`, `modalidade`, `observacoes`, `resultado`, `status`, `criado_em`, `atualizado_em`) VALUES
(1, 2, 2, 9, '2026-07-05', '21:15:00', NULL, 'presencial', 'teste', NULL, 'agendado', '2026-07-06 00:15:25', '2026-07-06 00:15:25');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pessoas`
--

CREATE TABLE `pessoas` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `tipo` enum('aluno','ex_aluno','externo') NOT NULL DEFAULT 'aluno',
  `matricula` varchar(20) DEFAULT NULL,
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `documento` varchar(30) DEFAULT NULL,
  `curso` varchar(120) DEFAULT NULL,
  `periodo` varchar(20) DEFAULT NULL,
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pessoas`
--

INSERT INTO `pessoas` (`id`, `nome`, `cpf`, `email`, `telefone`, `tipo`, `matricula`, `status`, `criado_em`, `atualizado_em`, `documento`, `curso`, `periodo`, `observacoes`) VALUES
(1, 'William Sestito', NULL, 'sestitio@exemplo.com', '(47) 99999-0010', 'aluno', NULL, 'ativo', '2026-06-23 22:30:00', '2026-06-23 22:30:00', '321.654.987-10', 'Engenharia de software', '5º', 'Aluno interessado em orientação sobre atividades complementares.'),
<<<<<<< HEAD
(2,'Serafim Fernandes',NULL,'serafim.fernandes@email.com','(47) 99988-4512','aluno',NULL,'ativo','2026-06-23', '20:45:32','412.587.963-21','Engenharia de Software','6º','Aluno interessado em estágio na área de desenvolvimento.'),
(3,'Marilene Fernandes',NULL,'marilene.fernandes@email.com','(47) 99977-8241','aluno',NULL,'ativo','2026-07-05','19:40:00','853.214.769-58','Análise e Desenvolvimento de Sistemas','3º','Solicitou informações sobre aproveitamento de disciplinas.');
=======
(2, 'Francisco Edilson', NULL, 'franedi@gmail.com', '(47) 99999-0011', 'aluno', NULL, 'ativo', '2026-06-23 22:30:00', '2026-06-23 22:30:00', '065.654.400-14', 'Engenharia de software', '5º', NULL),
(3, 'Mariana', NULL, 'mariana@email.com', '(47) 99999-1234', 'aluno', NULL, 'ativo', '2026-06-29 22:58:25', '2026-06-29 22:58:25', '111.222.333-44', NULL, NULL, NULL);

-- --------------------------------------------------------

>>>>>>> 97f079d2e8feb20342dd35b2c44841d6ce78172a
--
-- Estrutura para tabela `tipos_atendimentos`
--

CREATE TABLE `tipos_atendimentos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `duracao_min` int(11) DEFAULT 30 CHECK (`duracao_min` > 0),
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tipos_atendimentos`
--

INSERT INTO `tipos_atendimentos` (`id`, `nome`, `descricao`, `duracao_min`, `status`, `criado_em`, `atualizado_em`) VALUES
(1, 'Dúvida financeira', 'Dúvidas sobre disciplinas, conteúdos e atividades.', 20, 'inativo', '2026-06-22 23:08:21', '2026-06-22 23:08:21'),
(2, 'Apoio á extensão', 'Orientação relacionadas a projetos de extensão e atividades comunitárias.', 30, 'ativo', '2026-06-22 23:08:21', '2026-06-22 23:08:21'),
(3, 'orientação de projeto', 'Orientações acadêmicas sobre projetos integradores.', 30, 'ativo', '2026-06-29 23:14:02', '2026-06-29 23:14:02'),
(4, 'Dúvida acadêmica', 'Dúvidas sobre disciplinas, conteúdos e atividades.', 30, 'ativo', '2026-06-29 23:16:11', '2026-06-29 23:16:11'),
(5, 'orientações financeiras e acadêmicas', 'Orientações acadêmicas sobre projetos integradores', 30, 'ativo', '2026-07-05 23:07:05', '2026-07-05 23:07:05'),
(6, 'dúvids acdêmicas', 'Orientações acadêmicas sobre projetos integradores e financeiros', 30, 'ativo', '2026-07-05 23:08:27', '2026-07-05 23:08:27');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `perfil` enum('admin','aluno','atendente') DEFAULT 'atendente',
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `perfil`, `status`, `criado_em`, `atualizado_em`) VALUES
(2, 'Felipe', 'felipe@gmail.com', '$2y$10$1SRS/TSU3gqGlDtMJl.8HetfiQeGz2diHFa1rlgakHd6fzSDXO.Wm', 'atendente', 'ativo', '2026-06-12 21:13:20', '2026-06-22 22:47:54'),
(3, 'Robertha', 'ro@gmail.com', '$2y$10$TycW5urIU8du9ckDA7HpCeZOX4IBcSl/JpzzT0qOEJgOCzBqf0apy', 'aluno', 'ativo', '2026-06-12 21:14:01', '2026-06-22 22:47:54'),
(8, 'AdministradorAtualizado', 'admindo@atendelab.com', '$2y$10$MyCbTs3vqN6dn6gOfEdB2OtSNfx.rm.B8.A.cqimcxeEdTf/b8QUu', 'atendente', 'ativo', '2026-06-12 22:49:48', '2026-06-22 22:47:54'),
(9, 'Admin', 'admin@atendelab.com', '$2y$10$ncr0jUaDZajawge2aNd3LuooXZ58WGxX9psjr7TieFmhZUck4tNka', 'admin', 'ativo', '2026-07-06 00:01:18', '2026-07-06 00:01:18');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `atendimentos`
--
ALTER TABLE `atendimentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_atendimentos_pessoa` (`pessoa_id`),
  ADD KEY `fk_atendimentos_tipo` (`tipo_atendimento_id`),
  ADD KEY `fk_atendimentos_atendente` (`atendente_id`);

--
-- Índices de tabela `pessoas`
--
ALTER TABLE `pessoas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `matricula` (`matricula`);

--
-- Índices de tabela `tipos_atendimentos`
--
ALTER TABLE `tipos_atendimentos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `atendimentos`
--
ALTER TABLE `atendimentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `pessoas`
--
ALTER TABLE `pessoas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `tipos_atendimentos`
--
ALTER TABLE `tipos_atendimentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `atendimentos`
--
ALTER TABLE `atendimentos`
  ADD CONSTRAINT `fk_atendimentos_atendente` FOREIGN KEY (`atendente_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_atendimentos_pessoa` FOREIGN KEY (`pessoa_id`) REFERENCES `pessoas` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_atendimentos_tipo` FOREIGN KEY (`tipo_atendimento_id`) REFERENCES `tipos_atendimentos` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
