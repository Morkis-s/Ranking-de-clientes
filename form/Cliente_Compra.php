<?php
require_once '../crud_filmes/header.php';
require_once '../conexao/conexao.php';

// Verificar se veio com ID do cliente pela URL
$cliente_selecionado_id = $_GET['cliente_id'] ?? null;
$cliente_selecionado = null;

// Se veio ID, buscar dados do cliente
if ($cliente_selecionado_id) {
    try {
        $stmt_cliente = $pdo->prepare("SELECT id, nome, cpf FROM clientes WHERE id = ?");
        $stmt_cliente->execute([$cliente_selecionado_id]);
        $cliente_selecionado = $stmt_cliente->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<script>alert('Erro ao buscar cliente: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// Buscar clientes para o select (mantém para caso queira trocar)
$stmt_clientes = $pdo->query("SELECT id, nome, cpf FROM clientes ORDER BY nome");
$clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);

// Buscar produtos para o select
$stmt_produtos = $pdo->query("SELECT id, nome, preco, custo, estoque FROM produtos ORDER BY nome");
$produtos = $stmt_produtos->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mx-auto px-4 py-6 max-w-7xl">

    <!-- CABEÇALHO CENTRALIZADO -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
            <i class="fas fa-cart-plus text-green-600 mr-3"></i>
            Atribuir Produto ao Cliente
        </h1>
        <p class="text-gray-600 text-lg">Registre uma venda para um cliente</p>
    </div>

    <!-- FORMULÁRIO CENTRALIZADO -->
    <div class="flex justify-center">
        <div class="w-full max-w-4xl">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <form action="processa_venda.php" method="POST" class="space-y-6">

                    <!-- Seleção do Cliente -->
                    <div class="border-b border-gray-200 pb-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4 text-start">
                            <i class="fas fa-user text-blue-500 mr-2"></i>
                            Selecionar Cliente
                        </h2>

                        <div class="grid grid-cols-1 gap-4">
                            <!-- Cliente -->
                            <div>
                                <label for="cliente_id" class="block text-sm font-medium text-gray-700 mb-2 text-start">
                                    Cliente *
                                </label>

                                <?php if ($cliente_selecionado): ?>
                                    <!-- Se veio cliente pela URL, mostrar como selecionado -->
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold mr-3">
                                                <?= strtoupper(substr($cliente_selecionado['nome'], 0, 2)) ?>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($cliente_selecionado['nome']) ?></p>
                                                <p class="text-sm text-gray-600">CPF: <?= htmlspecialchars($cliente_selecionado['cpf']) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="cliente_id" value="<?= $cliente_selecionado['id'] ?>">
                                    <p class="text-sm text-gray-500 text-center">
                                        <a href="Cliente_Compra.php" class="text-blue-500 hover:text-blue-700 underline">
                                            Clique aqui para selecionar outro cliente
                                        </a>
                                    </p>
                                <?php else: ?>
                                    <!-- Se não veio cliente, mostrar select normal -->
                                    <select
                                        id="cliente_id"
                                        name="cliente_id"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                        <option value="">Selecione um cliente</option>
                                        <?php foreach ($clientes as $cliente): ?>
                                            <option value="<?= $cliente['id'] ?>">
                                                <?= htmlspecialchars($cliente['nome']) ?> - <?= htmlspecialchars($cliente['cpf']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Seleção do Produto -->
                    <div class="border-b border-gray-200 pb-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4 text-start">
                            <i class="fas fa-box text-orange-500 mr-2"></i>
                            Selecionar Produto
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Produto -->
                            <div>
                                <label for="produto_id" class="block text-sm font-medium text-gray-700 mb-2 text-start">
                                    Produto *
                                </label>
                                <select
                                    id="produto_id"
                                    name="produto_id"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                    <option value="">Selecione um produto</option>
                                    <?php foreach ($produtos as $produto): ?>
                                        <option value="<?= $produto['id'] ?>" data-preco="<?= $produto['preco'] ?>" data-custo="<?= $produto['custo'] ?>" data-estoque="<?= $produto['estoque'] ?>">
                                            <?= htmlspecialchars($produto['nome']) ?> - R$ <?= number_format($produto['preco'], 2, ',', '.') ?> (Estoque: <?= $produto['estoque'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Quantidade -->
                            <div>
                                <label for="quantidade" class="block text-sm font-medium text-gray-700 mb-2 text-start">
                                    Quantidade *
                                </label>
                                <input
                                    type="number"
                                    id="quantidade"
                                    name="quantidade"
                                    required
                                    min="1"
                                    value="1"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            </div>
                        </div>

                        <!-- Informações do Produto Selecionado -->
                        <div id="info-produto" class="mt-4 p-4 bg-gray-50 rounded-lg hidden">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div class="text-center">
                                    <span class="font-semibold">Preço Unitário:</span>
                                    <span id="preco-unitario" class="text-green-600 ml-2">R$ 0,00</span>
                                </div>
                                <div class="text-center">
                                    <span class="font-semibold">Custo Unitário:</span>
                                    <span id="custo-unitario" class="text-red-600 ml-2">R$ 0,00</span>
                                </div>
                                <div class="text-center">
                                    <span class="font-semibold">Estoque Disponível:</span>
                                    <span id="estoque-disponivel" class="text-blue-600 ml-2">0</span>
                                </div>
                            </div>
                        </div>

                        <!-- Resumo da Venda -->
                        <div id="resumo-venda" class="mt-4 p-4 bg-blue-50 rounded-lg hidden">
                            <h4 class="font-semibold text-blue-800 mb-2 text-center">Resumo da Venda:</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div class="text-center">
                                    <span class="font-semibold">Valor Total:</span>
                                    <span id="valor-total" class="text-green-600 ml-2">R$ 0,00</span>
                                </div>
                                <div class="text-center">
                                    <span class="font-semibold">Lucro Total:</span>
                                    <span id="lucro-total" class="text-green-600 ml-2">R$ 0,00</span>
                                </div>
                                <div class="text-center">
                                    <span class="font-semibold">Margem de Lucro:</span>
                                    <span id="margem-lucro" class="text-green-600 ml-2">0%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botões CENTRALIZADOS -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center pt-6">
                        <a href="../index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition-colors text-center">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>

                        <button type="reset" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-3 rounded-lg font-medium transition-colors text-center">
                            <i class="fas fa-broom mr-2"></i>Limpar
                        </button>

                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-medium transition-colors text-center">
                            <i class="fas fa-check mr-2"></i>Registrar Venda
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const produtoSelect = document.getElementById('produto_id');
        const quantidadeInput = document.getElementById('quantidade');
        const infoProduto = document.getElementById('info-produto');
        const resumoVenda = document.getElementById('resumo-venda');

        // Elementos de informação
        const precoUnitario = document.getElementById('preco-unitario');
        const custoUnitario = document.getElementById('custo-unitario');
        const estoqueDisponivel = document.getElementById('estoque-disponivel');
        const valorTotal = document.getElementById('valor-total');
        const lucroTotal = document.getElementById('lucro-total');
        const margemLucro = document.getElementById('margem-lucro');

        function atualizarInformacoes() {
            const produtoSelecionado = produtoSelect.options[produtoSelect.selectedIndex];

            if (produtoSelecionado.value) {
                const preco = parseFloat(produtoSelecionado.getAttribute('data-preco'));
                const custo = parseFloat(produtoSelecionado.getAttribute('data-custo'));
                const estoque = parseInt(produtoSelecionado.getAttribute('data-estoque'));
                const quantidade = parseInt(quantidadeInput.value) || 1;

                // Atualizar informações básicas
                precoUnitario.textContent = 'R$ ' + preco.toFixed(2).replace('.', ',');
                custoUnitario.textContent = 'R$ ' + custo.toFixed(2).replace('.', ',');
                estoqueDisponivel.textContent = estoque;

                // Calcular totais
                const totalVenda = preco * quantidade;
                const totalCusto = custo * quantidade;
                const totalLucro = totalVenda - totalCusto;
                const margem = custo > 0 ? (totalLucro / totalCusto) * 100 : 0;

                // Atualizar resumo
                valorTotal.textContent = 'R$ ' + totalVenda.toFixed(2).replace('.', ',');
                lucroTotal.textContent = 'R$ ' + totalLucro.toFixed(2).replace('.', ',');
                margemLucro.textContent = margem.toFixed(1) + '%';

                // Mostrar seções
                infoProduto.classList.remove('hidden');
                resumoVenda.classList.remove('hidden');

                // Validar estoque
                if (quantidade > estoque) {
                    quantidadeInput.setCustomValidity('Quantidade maior que estoque disponível!');
                    estoqueDisponivel.classList.add('text-red-600');
                } else {
                    quantidadeInput.setCustomValidity('');
                    estoqueDisponivel.classList.remove('text-red-600');
                }
            } else {
                infoProduto.classList.add('hidden');
                resumoVenda.classList.add('hidden');
                quantidadeInput.setCustomValidity('');
            }
        }

        // Event listeners
        produtoSelect.addEventListener('change', atualizarInformacoes);
        quantidadeInput.addEventListener('input', atualizarInformacoes);

        // Validar formulário antes de enviar
        document.querySelector('form').addEventListener('submit', function(e) {
            const produtoSelecionado = produtoSelect.options[produtoSelect.selectedIndex];
            const quantidade = parseInt(quantidadeInput.value);
            const estoque = parseInt(produtoSelecionado.getAttribute('data-estoque'));

            if (!produtoSelecionado.value) {
                e.preventDefault();
                alert('Selecione um produto!');
                return;
            }

            if (quantidade > estoque) {
                e.preventDefault();
                alert('Quantidade indisponível em estoque!');
                return;
            }

            if (quantidade <= 0) {
                e.preventDefault();
                alert('Quantidade deve ser maior que zero!');
                return;
            }
        });

        // Se veio com cliente pré-selecionado, mostrar mensagem
        <?php if ($cliente_selecionado): ?>
            console.log('Cliente pré-selecionado: <?= $cliente_selecionado['nome'] ?>');
        <?php endif; ?>
    });
</script>