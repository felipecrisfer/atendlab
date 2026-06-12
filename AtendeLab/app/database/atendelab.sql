CREATE DATABASE IF NOT EXISTS atendelab
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;
USE atendlab;


CREATE TABLE usuarios (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nome VARCHAR(100) NOT NULL,
 email VARCHAR(100) NOT NULL UNIQUE,
 senha VARCHAR(255) NOT NULL,
 perfil ENUM('admin', 'atendente') DEFAULT 'atendente',
 status ENUM('ativo', 'inativo') DEFAULT 'ativo',
 criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pessoas (
  id         INT          AUTO_INCREMENT PRIMARY KEY,
  nome       VARCHAR(100) NOT NULL,
  cpf        VARCHAR(14)  UNIQUE,                         
  email      VARCHAR(100) UNIQUE,
  telefone   VARCHAR(20),
  tipo       ENUM('aluno', 'ex_aluno', 'externo') NOT NULL DEFAULT 'aluno',
  matricula  VARCHAR(20)  UNIQUE,                         
  status     ENUM('ativo', 'inativo') DEFAULT 'ativo',
  criado_em  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);
 
CREATE TABLE tipos_atendimentos (
  id           INT          AUTO_INCREMENT PRIMARY KEY,
  nome         VARCHAR(100) NOT NULL UNIQUE,
  descricao    TEXT,
  duracao_min  INT          DEFAULT 30                    
    CHECK (duracao_min > 0),
  status       ENUM('ativo', 'inativo') DEFAULT 'ativo',
  criado_em    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);
 
CREATE TABLE atendimentos (
  id                   INT    AUTO_INCREMENT PRIMARY KEY,
  pessoa_id            INT    NOT NULL,
  tipo_atendimento_id  INT    NOT NULL,
  atendente_id         INT    NOT NULL,
  data_atendimento     DATE   NOT NULL,
  hora_inicio          TIME   NOT NULL,
  hora_fim             TIME,
  modalidade           ENUM('presencial', 'remoto', 'hibrido') DEFAULT 'presencial',
  observacoes          TEXT,                              
  resultado            TEXT,                              
  status               ENUM('agendado', 'em_andamento', 'concluido', 'cancelado', 'nao_compareceu')
                         DEFAULT 'agendado',
  criado_em            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 
  CONSTRAINT fk_atendimentos_pessoa
    FOREIGN KEY (pessoa_id)
    REFERENCES pessoas (id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
 
  CONSTRAINT fk_atendimentos_tipo
    FOREIGN KEY (tipo_atendimento_id)
    REFERENCES tipos_atendimentos (id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
 
  CONSTRAINT fk_atendimentos_atendente
    FOREIGN KEY (atendente_id)
    REFERENCES usuarios (id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
);

INSERT INTO usuarios (nome, email, senha, perfil, status)
VALUES (
 'Administrador',
 'admin@atendelab.com',
 '$2y$10$J9P2kU2BAMZ3TZcuxTsW4e1D/lka8EocYHzvyoOZmCNcWDQz3RuVC',
 'admin',
 'ativo'
);


ALTER TABLE usuarios
MODIFY perfil ENUM('admin', 'aluno', 'atendente') DEFAULT 'atendente';