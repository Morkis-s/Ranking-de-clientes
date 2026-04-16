<?php
require_once '../conexao/conexao.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    try {
        // Verificar se o produto existe
        $stmt = $pdo->prepare("SELECT nome, estoque FROM produtos WHERE id = ?");
        $stmt->execute([$id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$produto) {
            header("Location: listaP.php?error=Produto não encontrado!");
            exit;
        }

        // Verificar se existem vendas para este produto - CORREÇÃO AQUI
        $stmt_vendas = $pdo->prepare("
            SELECT COUNT(*) as total 
            FROM vendas_produtos 
            WHERE produto_id = ?
        ");
        $stmt_vendas->execute([$id]);
        $vendas = $stmt_vendas->fetch(PDO::FETCH_ASSOC);

        // CORREÇÃO: Verificar se tem vendas (maior que 0, não menor que 0)
        if ($vendas['total'] > 0) {
            // Tem vendas - marcar como inativo (estoque zerado)
            $stmt_update = $pdo->prepare("UPDATE produtos SET estoque = 0 WHERE id = ?");
            $stmt_update->execute([$id]);

            header("Location: listaP.php?success=Produto '" . urlencode($produto['nome']) . "' marcado como inativo (estoque zerado). O histórico de vendas foi preservado.");
            exit;
        } else {
            // Não tem vendas - pode excluir permanentemente
            $stmt_delete = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
            $stmt_delete->execute([$id]);
            header("Location: listaP.php?success=Produto '" . urlencode($produto['nome']) . "' excluído permanentemente!");
            exit;
        }
    } catch (Exception $e) {
        header("Location: listaP.php?error=Erro ao processar produto: " . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: listaP.php?error=ID do produto não especificado!");
    exit;
}
