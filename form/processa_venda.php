<?php
require_once '../conexao/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id = $_POST['cliente_id'];
    $produto_id = $_POST['produto_id'];
    $quantidade = (int)$_POST['quantidade'];

    try {
        // Validar dados
        if (empty($cliente_id) || empty($produto_id) || $quantidade <= 0) {
            throw new Exception("Dados inválidos!");
        }

        // Buscar informações do produto
        $stmt_produto = $pdo->prepare("SELECT preco, custo, estoque FROM produtos WHERE id = ?");
        $stmt_produto->execute([$produto_id]);
        $produto = $stmt_produto->fetch(PDO::FETCH_ASSOC);

        if (!$produto) {
            throw new Exception("Produto não encontrado!");
        }

        // Verificar estoque
        if ($quantidade > $produto['estoque']) {
            throw new Exception("Estoque insuficiente! Disponível: " . $produto['estoque']);
        }

        // Calcular valores
        $valor_total = $produto['preco'] * $quantidade;
        $lucro_total = ($produto['preco'] - $produto['custo']) * $quantidade;

        // Iniciar transação
        $pdo->beginTransaction();

        // 1. Registrar venda na tabela vendas
        $stmt_venda = $pdo->prepare("
            INSERT INTO vendas (cliente_id, valor, lucro, data) 
            VALUES (?, ?, ?, CURDATE())
        ");
        $stmt_venda->execute([$cliente_id, $valor_total, $lucro_total]);
        $venda_id = $pdo->lastInsertId();

        // 2. Registrar produtos da venda na tabela vendas_produtos
        $stmt_venda_produto = $pdo->prepare("
            INSERT INTO vendas_produtos (venda_id, produto_id, quantidade) 
            VALUES (?, ?, ?)
        ");
        $stmt_venda_produto->execute([$venda_id, $produto_id, $quantidade]);

        // 3. Atualizar estoque
        $stmt_estoque = $pdo->prepare("
            UPDATE produtos SET estoque = estoque - ? WHERE id = ?
        ");
        $stmt_estoque->execute([$quantidade, $produto_id]);

        // Buscar nome do cliente e produto para mensagem
        $stmt_cliente = $pdo->prepare("SELECT nome FROM clientes WHERE id = ?");
        $stmt_cliente->execute([$cliente_id]);
        $cliente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);

        $stmt_produto_nome = $pdo->prepare("SELECT nome FROM produtos WHERE id = ?");
        $stmt_produto_nome->execute([$produto_id]);
        $produto_info = $stmt_produto_nome->fetch(PDO::FETCH_ASSOC);

        $pdo->commit();

        // Mensagem de sucesso
        echo "<script>
            alert('✅ Produto cadastrado ao cliente com sucesso!');
            window.location.href = '../tabelaSec/tabela.php';
        </script>";
    } catch (Exception $e) {
        // Rollback em caso de erro
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        echo "<script>
            alert('❌ Erro: " . addslashes($e->getMessage()) . "');
            window.history.back();
        </script>";
    }
} else {
    echo "<script>
        alert('Método inválido!');
        window.location.href = 'atribuir_produto.php';
    </script>";
}
