<?php
require_once '../conexao/conexao.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    try {
        // Verificar se o produto existe
        $stmt = $pdo->prepare("SELECT nome FROM produtos WHERE id = ?");
        $stmt->execute([$id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$produto) {
            header("Location: listaP.php?error=Produto não encontrado!");
            exit;
        }

        // Verificar se existem vendas
        $stmt_vendas = $pdo->prepare("SELECT COUNT(*) as total FROM vendas_produtos WHERE produto_id = ?");
        $stmt_vendas->execute([$id]);
        $vendas = $stmt_vendas->fetch(PDO::FETCH_ASSOC);

        if ($vendas['total'] > 0) {
            header("Location: listaP.php?error=Não é possível excluir produto com histórico de vendas!");
            exit;
        }

        // Excluir permanentemente
        $stmt_delete = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
        $stmt_delete->execute([$id]);

        header("Location: listaP.php?success=Produto '" . urlencode($produto['nome']) . "' excluído permanentemente!");
        exit;
    } catch (Exception $e) {
        header("Location: listaP.php?error=Erro ao excluir produto: " . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: listaP.php?error=ID não especificado!");
    exit;
}
