-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 16/04/2026 às 22:36
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `ranking_clientes`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `endereco` varchar(150) DEFAULT NULL,
  `data_cadastro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `clientes`
--

INSERT INTO `clientes` (`id`, `nome`, `cpf`, `telefone`, `email`, `endereco`, `data_cadastro`) VALUES
(1, 'Maria Souza', '123.456.789-00', '(11) 98888-1111', 'maria@exemplo.com', 'Rua das Flores, 123 - São Paulo/SP', '2025-03-12 10:42:17'),
(2, 'João Pereira', '222.333.444-55', '(11) 97777-2222', 'joao@exemplo.com', 'Av. Paulista, 456 - São Paulo/SP', '2025-04-05 16:28:45'),
(3, 'Ana Lima', '999.888.777-66', '(11) 96666-3333', 'ana@exemplo.com', 'Rua das Acácias, 78 - Campinas/SP', '2025-02-20 08:15:09'),
(4, 'Carlos Silva', '555.444.333-22', '(11) 95555-4444', 'carlos@exemplo.com', 'Av. Brasil, 890 - Rio de Janeiro/RJ', '2025-01-29 19:33:51'),
(5, 'Fernanda Alves', '888.777.666-55', '(11) 94444-5555', 'fernanda@exemplo.com', 'Rua Paraná, 102 - Curitiba/PR', '2025-05-11 14:17:38'),
(6, 'Roberto Dias', '111.222.333-44', '(11) 93333-6666', 'roberto@exemplo.com', 'Rua Goiás, 59 - Belo Horizonte/MG', '2025-06-03 09:56:22'),
(7, 'Juliana Castro', '444.555.666-77', '(11) 92222-7777', 'juliana@exemplo.com', 'Av. Independência, 321 - Porto Alegre/RS', '2025-07-22 18:49:05'),
(8, 'Paulo Mendes', '333.222.111-00', '(11) 91111-8888', 'paulo@exemplo.com', 'Rua Amazonas, 65 - Salvador/BA', '2025-08-14 11:24:33'),
(9, 'Lucas Nogueira', '777.888.999-11', '(11) 91234-9876', 'lucas@exemplo.com', 'Rua XV de Novembro, 222 - Florianópolis/SC', '2025-09-10 17:38:54'),
(10, 'Amanda Torres', '999.000.111-22', '(11) 98765-4321', 'amanda@exemplo.com', 'Av. Atlântica, 999 - Recife/PE', '2025-10-27 07:52:10');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `custo` decimal(10,2) NOT NULL,
  `estoque` int(11) DEFAULT 0,
  `data_cadastro` date DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `nome`, `preco`, `custo`, `estoque`, `data_cadastro`) VALUES
(1, 'Notebook Dell', 4500.00, 3800.00, 10, '2025-11-10'),
(2, 'Mouse Logitech', 120.00, 70.00, 50, '2025-11-10'),
(3, 'Teclado Mecânico Redragon', 350.00, 200.00, 30, '2025-11-10'),
(4, 'Monitor LG 24\"', 950.00, 700.00, 20, '2025-11-10'),
(5, 'Cadeira Gamer', 1200.00, 800.00, 15, '2025-11-10'),
(6, 'Headset HyperX', 480.00, 300.00, 25, '2025-11-10'),
(7, 'SSD 1TB Kingston', 550.00, 400.00, 40, '2025-11-10'),
(8, 'Pendrive 64GB Sandisk', 80.00, 40.00, 100, '2025-11-10'),
(9, 'Impressora HP Laser', 900.00, 650.00, 10, '2025-11-10'),
(10, 'Smartphone Samsung A15', 1300.00, 950.00, 12, '2025-11-10');

-- --------------------------------------------------------

--
-- Estrutura para tabela `vendas`
--

CREATE TABLE `vendas` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `lucro` decimal(10,2) NOT NULL,
  `data` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `vendas`
--

INSERT INTO `vendas` (`id`, `cliente_id`, `valor`, `lucro`, `data`) VALUES
(1, 1, 4500.00, 700.00, '2025-01-10'),
(2, 2, 1200.00, 300.00, '2025-02-12'),
(3, 3, 950.00, 250.00, '2025-03-03'),
(4, 4, 1800.00, 450.00, '2025-04-10'),
(5, 5, 2300.00, 600.00, '2025-05-05'),
(6, 6, 350.00, 120.00, '2025-06-15'),
(7, 7, 3100.00, 700.00, '2025-07-10'),
(8, 8, 900.00, 180.00, '2025-08-02'),
(9, 9, 2200.00, 550.00, '2025-09-14'),
(10, 10, 1400.00, 350.00, '2025-10-25'),
(11, 1, 950.00, 200.00, '2025-11-01'),
(12, 2, 480.00, 120.00, '2025-11-03'),
(13, 3, 550.00, 100.00, '2025-11-05'),
(14, 4, 1300.00, 300.00, '2025-11-06'),
(15, 5, 120.00, 40.00, '2025-11-07'),
(16, 6, 950.00, 200.00, '2025-11-08'),
(17, 7, 350.00, 80.00, '2025-11-09'),
(18, 8, 900.00, 180.00, '2025-11-09'),
(19, 9, 480.00, 100.00, '2025-11-09'),
(20, 10, 1500.00, 400.00, '2025-11-10');

-- --------------------------------------------------------

--
-- Estrutura para tabela `vendas_produtos`
--

CREATE TABLE `vendas_produtos` (
  `id` int(11) NOT NULL,
  `venda_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `vendas_produtos`
--

INSERT INTO `vendas_produtos` (`id`, `venda_id`, `produto_id`, `quantidade`) VALUES
(1, 1, 1, 1),
(2, 2, 5, 1),
(3, 3, 4, 1),
(4, 4, 3, 2),
(5, 5, 1, 1),
(6, 6, 2, 1),
(7, 7, 4, 2),
(8, 8, 7, 1),
(9, 9, 10, 1),
(10, 10, 8, 2),
(11, 11, 9, 1),
(12, 12, 2, 2),
(13, 13, 3, 1),
(14, 14, 5, 1),
(15, 15, 8, 3),
(16, 16, 6, 1),
(17, 17, 2, 1),
(18, 18, 7, 1),
(19, 19, 4, 1),
(20, 20, 10, 1);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cpf` (`cpf`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `vendas`
--
ALTER TABLE `vendas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Índices de tabela `vendas_produtos`
--
ALTER TABLE `vendas_produtos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venda_id` (`venda_id`),
  ADD KEY `produto_id` (`produto_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `vendas`
--
ALTER TABLE `vendas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `vendas_produtos`
--
ALTER TABLE `vendas_produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `vendas`
--
ALTER TABLE `vendas`
  ADD CONSTRAINT `vendas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `vendas_produtos`
--
ALTER TABLE `vendas_produtos`
  ADD CONSTRAINT `vendas_produtos_ibfk_1` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendas_produtos_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
