<?php
// vendas/historico_compras.php
require_once '../conexao/conexao.php';
session_start();

$cliente_id = $_GET['cliente_id'] ?? null;

if (!$cliente_id) {
    $_SESSION['erro'] = "Cliente não especificado!";
    header("Location: ../tabela/tabela.php");
    exit();
}

// Buscar dados do cliente
try {
    $stmt_cliente = $pdo->prepare("SELECT id, nome, cpf FROM clientes WHERE id = ?");
    $stmt_cliente->execute([$cliente_id]);
    $cliente = $stmt_cliente->fetch();

    if (!$cliente) {
        $_SESSION['erro'] = "Cliente não encontrado!";
        header("Location: ../tabela/tabela.php");
        exit();
    }
} catch (PDOException $e) {
    die("Erro ao buscar cliente: " . $e->getMessage());
}

// Buscar histórico de compras
try {
    $sql = "SELECT 
        id,
        valor,
        lucro,
        data,
        forma_pagamento,
        descricao,
        DATE_FORMAT(data, '%d/%m/%Y') as data_formatada
    FROM vendas 
    WHERE cliente_id = ? 
    ORDER BY data DESC";

    $stmt_vendas = $pdo->prepare($sql);
    $stmt_vendas->execute([$cliente_id]);
    $vendas = $stmt_vendas->fetchAll();

    // Calcular totais
    $total_vendas = 0;
    $total_lucro = 0;
    foreach ($vendas as $venda) {
        $total_vendas += $venda['valor'];
        $total_lucro += $venda['lucro'];
    }
} catch (PDOException $e) {
    die("Erro ao buscar vendas: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Compras - Sistema Clientes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50 min-h-screen">
    <div class="max-w-6xl mx-auto p-6">

        <!-- CABEÇALHO -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">
                        <i class="fas fa-history text-purple-600 mr-3"></i>
                        Histórico de Compras
                    </h1>
                    <div class="flex items-center mt-2">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold mr-3">
                            <?= substr($cliente['nome'], 0, 2) ?>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($cliente['nome']) ?></p>
                            <p class="text-sm text-gray-600">CPF: <?= $cliente['cpf'] ?></p>
                        </div>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="../tabela/tabela.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Voltar
                    </a>
                    <a href="adicionar_compra.php?cliente_id=<?= $cliente['id'] ?>" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-cart-plus mr-2"></i>Nova Compra
                    </a>
                </div>
            </div>
        </div>

        <!-- CARDS DE RESUMO -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <i class="fas fa-shopping-cart text-blue-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gray-600">Total de Compras</p>
                        <p class="text-lg font-bold text-gray-800"><?= count($vendas) ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <i class="fas fa-dollar-sign text-green-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gray-600">Total em Vendas</p>
                        <p class="text-lg font-bold text-gray-800">R$ <?= number_format($total_vendas, 2, ',', '.') ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <i class="fas fa-chart-line text-purple-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gray-600">Lucro Total</p>
                        <p class="text-lg font-bold text-gray-800">R$ <?= number_format($total_lucro, 2, ',', '.') ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <i class="fas fa-percentage text-yellow-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gray-600">Margem Média</p>
                        <p class="text-lg font-bold text-gray-800">
                            <?= $total_vendas > 0 ? number_format(($total_lucro / $total_vendas) * 100, 1) : '0' ?>%
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABELA DE COMPRAS -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-xl font-semibold text-gray-800">
                    <i class="fas fa-receipt text-gray-600 mr-2"></i>
                    Histórico de Compras
                </h2>
                <p class="text-gray-600 text-sm"><?= count($vendas) ?> compra(s) registrada(s)</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-3 px-6 text-left font-semibold text-gray-700">Data</th>
                            <th class="py-3 px-6 text-left font-semibold text-gray-700">Descrição</th>
                            <th class="py-3 px-6 text-left font-semibold text-gray-700">Valor</th>
                            <th class="py-3 px-6 text-left font-semibold text-gray-700">Lucro</th>
                            <th class="py-3 px-6 text-left font-semibold text-gray-700">Pagamento</th>
                            <th class="py-3 px-6 text-left font-semibold text-gray-700">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (count($vendas) > 0): ?>
                            <?php foreach ($vendas as $venda): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-6">
                                        <p class="text-gray-700 font-medium"><?= $venda['data_formatada'] ?></p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="text-gray-700"><?= $venda['descricao'] ?: 'Compra geral' ?></p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="font-bold text-gray-800">R$ <?= number_format($venda['valor'], 2, ',', '.') ?></p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="font-bold text-green-600">R$ <?= number_format($venda['lucro'], 2, ',', '.') ?></p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                                            <?= $venda['forma_pagamento'] ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex gap-2">
                                            <button onclick="editarVenda(<?= $venda['id'] ?>)"
                                                class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg transition-colors"
                                                title="Editar Compra">
                                                <i class="fas fa-edit text-sm"></i>
                                            </button>
                                            <button onclick="excluirVenda(<?= $venda['id'] ?>)"
                                                class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg transition-colors"
                                                title="Excluir Compra">
                                                <i class="fas fa-trash text-sm"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="py-12 px-6 text-center text-gray-500">
                                    <i class="fas fa-shopping-cart text-4xl mb-4 text-gray-300"></i>
                                    <p class="text-lg font-semibold mb-2">Nenhuma compra registrada</p>
                                    <p class="text-sm mb-4">Este cliente ainda não realizou nenhuma compra.</p>
                                    <a href="adicionar_compra.php?cliente_id=<?= $cliente['id'] ?>"
                                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors inline-block">
                                        <i class="fas fa-cart-plus mr-2"></i>Registrar Primeira Compra
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function editarVenda(vendaId) {
            if (confirm('Deseja editar esta compra?')) {
                // Implementar edição da venda
                alert('Edição da compra #' + vendaId + ' - Funcionalidade em desenvolvimento');
            }
        }

        function excluirVenda(vendaId) {
            if (confirm('Tem certeza que deseja excluir esta compra?\nEsta ação não pode ser desfeita!')) {
                // Implementar exclusão da venda
                window.location.href = 'excluir_venda.php?id=' + vendaId + '&cliente_id=<?= $cliente['id'] ?>';
            }
        }
    </script>
</body>

</html>