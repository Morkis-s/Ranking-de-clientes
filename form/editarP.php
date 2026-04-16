<?php
require_once '../conexao/conexao.php';
require_once '../crud_filmes/header.php';


$produto = null;
$titulo = "Novo Produto";
$is_edit = false;

// Se veio ID, é edição
if (isset($_GET['id'])) {
    $is_edit = true;
    $titulo = "Editar Produto";

    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produto) {
        header("Location: listaP.php?error=Produto não encontrado!");
        exit;
    }
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $preco = str_replace(['.', ','], ['', '.'], $_POST['preco']);
    $custo = str_replace(['.', ','], ['', '.'], $_POST['custo']);
    $estoque = (int)$_POST['estoque'];

    try {
        // Validações
        if (empty($nome) || $preco <= 0 || $custo < 0 || $estoque < 0) {
            throw new Exception("Preencha todos os campos corretamente!");
        }

        if ($is_edit) {
            // Atualizar produto
            $stmt = $pdo->prepare("
                UPDATE produtos 
                SET nome = ?, preco = ?, custo = ?, estoque = ? 
                WHERE id = ?
            ");
            $stmt->execute([$nome, $preco, $custo, $estoque, $_POST['id']]);
            $mensagem = "Produto atualizado com sucesso!";
        } else {
            // Inserir novo produto
            $stmt = $pdo->prepare("
                INSERT INTO produtos (nome, preco, custo, estoque, data_cadastro) 
                VALUES (?, ?, ?, ?, CURDATE())
            ");
            $stmt->execute([$nome, $preco, $custo, $estoque]);
            $mensagem = "Produto cadastrado com sucesso!";
        }

        header("Location: listaP.php?success=" . urlencode($mensagem));
        exit;
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}
?>

<div class="container mx-auto px-4 py-6 max-w-4xl">

    <!-- CABEÇALHO -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
            <i class="fas <?= $is_edit ? 'fa-edit' : 'fa-plus' ?> text-blue-600 mr-3"></i>
            <?= $titulo ?>
        </h1>
        <p class="text-gray-600 text-lg">
            <?= $is_edit ? 'Atualize as informações do produto' : 'Cadastre um novo produto no sistema' ?>
        </p>
    </div>

    <!-- FORMULÁRIO -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <?php if (isset($erro)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <?php if ($is_edit): ?>
                <input type="hidden" name="id" value="<?= $produto['id'] ?>">
            <?php endif; ?>

            <!-- Nome -->
            <div>
                <label for="nome" class="block text-sm font-medium text-gray-700 mb-2">
                    Nome do Produto *
                </label>
                <input
                    type="text"
                    id="nome"
                    name="nome"
                    required
                    value="<?= htmlspecialchars($produto['nome'] ?? '') ?>"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    placeholder="Ex: Notebook Dell, Mouse Logitech...">
            </div>

            <!-- Preço e Custo -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="preco" class="block text-sm font-medium text-gray-700 mb-2">
                        Preço de Venda *
                    </label>
                    <input
                        type="text"
                        id="preco"
                        name="preco"
                        required
                        value="<?= number_format($produto['preco'] ?? 0, 2, ',', '.') ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="R$ 0,00">
                </div>

                <div>
                    <label for="custo" class="block text-sm font-medium text-gray-700 mb-2">
                        Custo Unitário *
                    </label>
                    <input
                        type="text"
                        id="custo"
                        name="custo"
                        required
                        value="<?= number_format($produto['custo'] ?? 0, 2, ',', '.') ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="R$ 0,00">
                </div>
            </div>

            <!-- Estoque -->
            <div>
                <label for="estoque" class="block text-sm font-medium text-gray-700 mb-2">
                    Quantidade em Estoque *
                </label>
                <input
                    type="number"
                    id="estoque"
                    name="estoque"
                    required
                    min="0"
                    value="<?= $produto['estoque'] ?? 0 ?>"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    placeholder="0">
            </div>

            <!-- Informações de Lucro (calculadas) -->
            <div id="info-lucro" class="p-4 bg-gray-50 rounded-lg <?= !$is_edit ? 'hidden' : '' ?>">
                <h4 class="font-semibold text-gray-800 mb-3">Informações de Lucro:</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div class="text-center">
                        <span class="font-semibold">Margem de Lucro:</span>
                        <span id="margem-lucro" class="text-green-600 ml-2 font-semibold">
                            <?php if ($is_edit && $produto['custo'] > 0): ?>
                                <?= number_format((($produto['preco'] - $produto['custo']) / $produto['custo']) * 100, 1) ?>%
                            <?php else: ?>
                                0%
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="text-center">
                        <span class="font-semibold">Lucro Unitário:</span>
                        <span id="lucro-unitario" class="text-green-600 ml-2 font-semibold">
                            R$ <?= $is_edit ? number_format($produto['preco'] - $produto['custo'], 2, ',', '.') : '0,00' ?>
                        </span>
                    </div>
                    <div class="text-center">
                        <span class="font-semibold">Valor em Estoque:</span>
                        <span id="valor-estoque" class="text-blue-600 ml-2 font-semibold">
                            R$ <?= $is_edit ? number_format($produto['preco'] * $produto['estoque'], 2, ',', '.') : '0,00' ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center pt-6">
                <a href="listaP.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition-colors text-center">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>


                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    <i class="fas fa-check mr-2"></i><?= $is_edit ? 'Atualizar' : 'Cadastrar' ?>
                </button>
            </div>
        </form>
    </div>
</div>
</body>
<script>
    // Formatação de moeda
    function formatarMoeda(input) {
        let value = input.value.replace(/\D/g, '');
        value = (value / 100).toFixed(2) + '';
        value = value.replace(".", ",");
        value = value.replace(/(\d)(\d{3})(\d{3}),/g, "$1.$2.$3,");
        value = value.replace(/(\d)(\d{3}),/g, "$1.$2,");
        input.value = value;
    }

    // Calcular lucro em tempo real
    function calcularLucro() {
        const preco = parseFloat(document.getElementById('preco').value.replace('R$ ', '').replace('.', '').replace(',', '.')) || 0;
        const custo = parseFloat(document.getElementById('custo').value.replace('R$ ', '').replace('.', '').replace(',', '.')) || 0;
        const estoque = parseInt(document.getElementById('estoque').value) || 0;

        if (preco > 0 && custo > 0) {
            const lucroUnitario = preco - custo;
            const margem = custo > 0 ? (lucroUnitario / custo) * 100 : 0;
            const valorEstoque = preco * estoque;

            document.getElementById('margem-lucro').textContent = margem.toFixed(1) + '%';
            document.getElementById('lucro-unitario').textContent = 'R$ ' + lucroUnitario.toFixed(2).replace('.', ',');
            document.getElementById('valor-estoque').textContent = 'R$ ' + valorEstoque.toFixed(2).replace('.', ',');

            document.getElementById('info-lucro').classList.remove('hidden');
        }
    }

    // Event listeners
    document.getElementById('preco').addEventListener('input', function() {
        formatarMoeda(this);
        calcularLucro();
    });

    document.getElementById('custo').addEventListener('input', function() {
        formatarMoeda(this);
        calcularLucro();
    });

    document.getElementById('estoque').addEventListener('input', calcularLucro);

    // Inicializar formatação
    document.addEventListener('DOMContentLoaded', function() {
        formatarMoeda(document.getElementById('preco'));
        formatarMoeda(document.getElementById('custo'));
        <?php if ($is_edit): ?>calcularLucro();
    <?php endif; ?>
    });
</script>
</body>

</html>