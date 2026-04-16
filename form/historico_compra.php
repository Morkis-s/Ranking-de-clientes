<?php
require_once '../conexao/conexao.php';

// Buscar todas as vendas
$stmt_vendas = $pdo->query("
    SELECT 
        v.*,
        c.nome as cliente_nome,
        p.nome as produto_nome,
        p.preco as preco_unitario
    FROM vendas v
    JOIN clientes c ON v.cliente_id = c.id
    JOIN produtos p ON v.produto_id = p.id
    ORDER BY v.data_venda DESC
");
$vendas = $stmt_vendas->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendas Registradas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto p-6">

        <!-- CABEÇALHO -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">
                        <i class="fas fa-shopping-cart text-green-600 mr-3"></i>
                        Vendas Registradas
                    </h1>
                    <p class="text-gray-600">Histórico completo de vendas</p>
                </div>
                <div class="flex gap-4">
                    <a href="atribuir_produto.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>Nova Venda
                    </a>
                    <a href="../index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Voltar
                    </a>
                </div>
            </div>
        </div>

        <!-- TABELA DE VENDAS -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3 px-6 text-left font-semibold text-gray-700">Data</th>
                            <th class="py-3 px-6 text-left font-semibold text-gray-700">Cliente</th>
                            <th class="py-3 px-6 text-left font-semibold text-gray-700">Produto</th>
                            <th class="py-3 px-6 text-left font-semibold text-gray-700">Quantidade</th>
                            <th class="py-3 px-6 text-left font-semibold text-gray-700">Valor Unit.</th>
                            <th class="py-3 px-6 text-left font-semibold text-gray-700">Valor Total</th>
                            <th class="py-3 px-6 text-left font-semibold text-gray-700">Lucro</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($vendas as $venda): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="py-4 px-6">
                                    <?= date('d/m/Y H:i', strtotime($venda['data_venda'])) ?>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold mr-3">
                                            <?= strtoupper(substr($venda['cliente_nome'], 0, 2)) ?>
                                        </div>
                                        <?= htmlspecialchars($venda['cliente_nome']) ?>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <?= htmlspecialchars($venda['produto_nome']) ?>
                                </td>
                                <td class="py-4 px-6">
                                    <?= $venda['quantidade'] ?>
                                </td>
                                <td class="py-4 px-6">
                                    R$ <?= number_format($venda['preco_unitario'], 2, ',', '.') ?>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="font-bold text-green-600">
                                        R$ <?= number_format($venda['valor'], 2, ',', '.') ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="font-bold text-blue-600">
                                        R$ <?= number_format($venda['lucro'], 2, ',', '.') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>