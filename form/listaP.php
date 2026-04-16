<?php
require_once '../conexao/conexao.php';
require_once '../crud_filmes/header.php';

// Buscar produtos ATIVOS (remover a query duplicada)
$sql = "SELECT id, nome, preco, custo, estoque, data_cadastro FROM produtos ORDER BY data_cadastro DESC";
$stmt = $pdo->query($sql);
$produtos = $stmt->fetchAll();
?>

<div class="container mx-auto px-4 py-6 max-w-7xl">

    <!-- CABEÇALHO -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
            <i class="fas fa-boxes text-blue-600 mr-3"></i>
            Gerenciar Produtos
        </h1>
        <p class="text-gray-600 text-lg">Cadastre, edite ou exclua produtos do sistema</p>
    </div>

    <!-- BOTÃO ADICIONAR -->
    <div class="flex justify-between items-center mb-6">
        <a href="../index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Voltar
        </a>
        <a href="CadastraIndex.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors">
            <i class="fas fa-plus mr-2"></i>Novo Produto
        </a>
    </div>

    <!-- TABELA DE PRODUTOS -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="px-6 py-4 text-left">ID</th>
                        <th class="px-6 py-4 text-left">Produto</th>
                        <th class="px-6 py-4 text-left">Preço</th>
                        <th class="px-6 py-4 text-left">Custo</th>
                        <th class="px-6 py-4 text-left">Estoque</th>
                        <th class="px-6 py-4 text-left">Data Cadastro</th>
                        <th class="px-6 py-4 text-center">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($produtos as $produto): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">#<?= str_pad($produto['id'], 3, '0', STR_PAD_LEFT) ?></td>
                            <td class="px-6 py-4 font-semibold"><?= htmlspecialchars($produto['nome']) ?></td>
                            <td class="px-6 py-4 text-green-600 font-semibold">
                                R$ <?= number_format($produto['preco'], 2, ',', '.') ?>
                            </td>
                            <td class="px-6 py-4 text-red-600">
                                R$ <?= number_format($produto['custo'], 2, ',', '.') ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-sm font-medium 
                                    <?= $produto['estoque'] > 10 ? 'bg-green-100 text-green-800' : ($produto['estoque'] > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                    <?= $produto['estoque'] ?> unidades
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                <?= date('d/m/Y', strtotime($produto['data_cadastro'])) ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center space-x-2">
                                    <a href="editarP.php?id=<?= $produto['id'] ?>"
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg transition-colors justify-center">
                                        <i class="fas fa-edit mr-1"></i>Editar
                                    </a>

                                    <!-- Botão Inativar -->
                                    <button onclick="confirmarInativacao(<?= $produto['id'] ?>, '<?= addslashes($produto['nome']) ?>')"
                                        class="bg-orange-500 hover:bg-orange-600 text-white px-3 py-2 rounded-lg transition-colors">
                                        <i class="fas fa-ban mr-1"></i>Inativar
                                    </button>

                                    <!-- Botão Excluir (apenas se não tiver vendas) -->
                                    <button onclick="confirmarExclusao(<?= $produto['id'] ?>, '<?= addslashes($produto['nome']) ?>')"
                                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg transition-colors">
                                        <i class="fas fa-trash mr-1"></i>Excluir
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ESTATÍSTICAS -->
    <?php
    $total_produtos = count($produtos);
    $total_estoque = array_sum(array_column($produtos, 'estoque'));
    $produtos_baixo_estoque = count(array_filter($produtos, function ($p) {
        return $p['estoque'] <= 5;
    }));
    ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
        <div class="bg-white p-6 rounded-xl shadow-lg text-center">
            <div class="text-2xl font-bold text-blue-600"><?= $total_produtos ?></div>
            <div class="text-gray-600">Total de Produtos</div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg text-center">
            <div class="text-2xl font-bold text-green-600"><?= $total_estoque ?></div>
            <div class="text-gray-600">Unidades em Estoque</div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg text-center">
            <div class="text-2xl font-bold text-red-600"><?= $produtos_baixo_estoque ?></div>
            <div class="text-gray-600">Produtos com Estoque Baixo</div>
        </div>
    </div>
</div>

<script>
    function confirmarInativacao(id, nome) {
        if (confirm(`Tem certeza que deseja inativar o produto "${nome}"?\n\nO estoque será zerado, mas o produto permanecerá no sistema.`)) {
            window.location.href = `inativo.php?id=${id}`;
        }
    }

    function confirmarExclusao(id, nome) {
        if (confirm(`ATENÇÃO: Tem certeza que deseja EXCLUIR PERMANENTEMENTE o produto "${nome}"?\n\nEsta ação não pode ser desfeita!`)) {
            window.location.href = `excluir.php?id=${id}`;
        }
    }

    // Mostrar mensagens de sucesso/erro
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success')) {
        alert('✅ ' + urlParams.get('success'));
    }
    if (urlParams.get('error')) {
        alert('❌ ' + urlParams.get('error'));
    }
</script>
</body>

</html>