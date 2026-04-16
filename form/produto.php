<?php
// Conexão com o banco de dados
require_once '../conexao/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Receber dados do formulário
    $nome = trim($_POST['nome']);
    $preco = trim($_POST['preco']);
    $custo = trim($_POST['custo']);
    $estoque = trim($_POST['estoque']);

    try {
        // Converter preço e custo para formato decimal
        $preco_convertido = str_replace(['.', ','], ['', '.'], $preco);
        $custo_convertido = str_replace(['.', ','], ['', '.'], $custo);
        $estoque_int = (int) $estoque;

        // Validar dados
        if (empty($nome)) {
            throw new Exception("Nome do produto é obrigatório");
        }

        if (!is_numeric($preco_convertido) || $preco_convertido <= 0) {
            throw new Exception("Preço deve ser um valor positivo");
        }

        if (!is_numeric($custo_convertido) || $custo_convertido < 0) {
            throw new Exception("Custo deve ser um valor válido");
        }

        if (!is_numeric($estoque_int) || $estoque_int < 0) {
            throw new Exception("Estoque deve ser um número válido");
        }

        // Verificar se produto já existe
        $check = $pdo->prepare("SELECT id FROM produtos WHERE nome = ?");
        $check->execute([$nome]);

        if ($check->rowCount() > 0) {
            throw new Exception("Já existe um produto com este nome!");
        }

        // Inserir no banco
        $stmt = $pdo->prepare("
            INSERT INTO produtos (nome, preco, custo, estoque) 
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $nome,
            $preco_convertido,
            $custo_convertido,
            $estoque_int
        ]);

        // Mensagem de sucesso
        echo "<script>
            alert('✅ Produto cadastrado com sucesso!');
            window.location.href = 'CadastraIndex.php';
        </script>";
    } catch (Exception $e) {
        // Mensagem de erro
        echo "<script>
            alert('❌ Erro: " . addslashes($e->getMessage()) . "');
            window.history.back();
        </script>";
    }
} else {
    echo "<script>
        alert('Método inválido!');
        window.location.href = '../index.php';
    </script>";
}
?>