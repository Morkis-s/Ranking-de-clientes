<?php
// vendas/excluir_venda.php
require_once '../conexao/conexao.php';
session_start();

$venda_id = $_GET['id'] ?? null;
$cliente_id = $_GET['cliente_id'] ?? null;

if (!$venda_id) {
    $_SESSION['erro'] = "Venda não especificada!";
    header("Location: ../tabela/tabela.php");
    exit();
}

try {
    // Verificar se a venda existe
    $stmt = $pdo->prepare("SELECT id FROM vendas WHERE id = ?");
    $stmt->execute([$venda_id]);

    if ($stmt->rowCount() > 0) {
        // Excluir a venda
        $sql_excluir = "DELETE FROM vendas WHERE id = ?";
        $stmt_excluir = $pdo->prepare($sql_excluir);
        $stmt_excluir->execute([$venda_id]);

        $_SESSION['sucesso'] = "Compra excluída com sucesso!";
    } else {
        $_SESSION['erro'] = "Compra não encontrada!";
    }
} catch (PDOException $e) {
    $_SESSION['erro'] = "Erro ao excluir compra: " . $e->getMessage();
}

// Redirecionar de volta
if ($cliente_id) {
    header("Location: historico_compras.php?cliente_id=" . $cliente_id);
} else {
    header("Location: ../tabela/tabela.php");
}
exit;
