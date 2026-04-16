<?php
// vendas/adicionar_compra.php
require_once '../conexao/conexao.php';
session_start();

// Verificar se veio com ID do cliente
$cliente_id = $_GET['cliente_id'] ?? null;

// Buscar dados do cliente se tiver ID
$cliente = null;
if ($cliente_id) {
    try {
        $stmt = $pdo->prepare("SELECT id, nome, cpf FROM clientes WHERE id = ?");
        $stmt->execute([$cliente_id]);
        $cliente = $stmt->fetch();
    } catch (PDOException $e) {
        $_SESSION['erro'] = "Erro ao buscar cliente: " . $e->getMessage();
    }
}

// Processar formulário de cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id = $_POST['cliente_id'];
    $valor = str_replace(['.', ','], ['', '.'], $_POST['valor']);
    $lucro = str_replace(['.', ','], ['', '.'], $_POST['lucro']);
    $data = $_POST['data'];
    $forma_pagamento = $_POST['forma_pagamento'];
    $descricao = $_POST['descricao'] ?? '';

    try {
        // Inserir a venda
        $sql = "INSERT INTO vendas (cliente_id, valor, lucro, data, forma_pagamento, descricao) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$cliente_id, $valor, $lucro, $data, $forma_pagamento, $descricao]);

        $_SESSION['sucesso'] = "Compra registrada com sucesso!";
        header("Location: ../tabela/tabela.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['erro'] = "Erro ao registrar compra: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Compra - Sistema Clientes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50 min-h-screen">
    <div class="max-w-2xl mx-auto p-6">

        <!-- CABEÇALHO -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">
                        <i class="fas fa-cart-plus text-green-600 mr-3"></i>
                        Registrar Compra
                    </h1>
                    <p class="text-gray-600">Adicione uma nova compra ao sistema</p>
                </div>
                <a href="../tabela/tabela.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar
                </a>
            </div>
        </div>

        <!-- MENSAGENS -->
        <?php if (isset($_SESSION['sucesso'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-check-circle mr-2"></i><?= $_SESSION['sucesso'] ?>
            </div>
            <?php unset($_SESSION['sucesso']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['erro'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-exclamation-triangle mr-2"></i><?= $_SESSION['erro'] ?>
            </div>
            <?php unset($_SESSION['erro']); ?>
        <?php endif; ?>

        <!-- FORMULÁRIO -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <form method="POST" action="" class="space-y-6">

                <!-- Informações do Cliente -->
                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">
                        <i class="fas fa-user text-blue-500 mr-2"></i>
                        Dados do Cliente
                    </h2>

                    <?php if ($cliente): ?>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold mr-4">
                                    <?= substr($cliente['nome'], 0, 2) ?>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($cliente['nome']) ?></p>
                                    <p class="text-sm text-gray-600">CPF: <?= $cliente['cpf'] ?></p>
                                </div>
                            </div>
                            <input type="hidden" name="cliente_id" value="<?= $cliente['id'] ?>">
                        </div>
                    <?php else: ?>
                        <div class="mb-4">
                            <label for="cliente_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Selecionar Cliente *
                            </label>
                            <select id="cliente_id" name="cliente_id" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Selecione um cliente...</option>
                                <?php
                                try {
                                    $stmt = $pdo->query("SELECT id, nome, cpf FROM clientes ORDER BY nome");
                                    $clientes = $stmt->fetchAll();
                                    foreach ($clientes as $c) {
                                        echo "<option value='{$c['id']}'>{$c['nome']} - {$c['cpf']}</option>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<option value=''>Erro ao carregar clientes</option>";
                                }
                                ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Dados da Compra -->
                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">
                        <i class="fas fa-shopping-cart text-green-500 mr-2"></i>
                        Dados da Compra
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Valor da Venda -->
                        <div>
                            <label for="valor" class="block text-sm font-medium text-gray-700 mb-2">
                                Valor da Venda *
                            </label>
                            <input type="text" id="valor" name="valor" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="0,00"
                                oninput="formatarMoeda(this)">
                        </div>

                        <!-- Lucro -->
                        <div>
                            <label for="lucro" class="block text-sm font-medium text-gray-700 mb-2">
                                Lucro *
                            </label>
                            <input type="text" id="lucro" name="lucro" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="0,00"
                                oninput="formatarMoeda(this)">
                        </div>

                        <!-- Data -->
                        <div>
                            <label for="data" class="block text-sm font-medium text-gray-700 mb-2">
                                Data da Compra *
                            </label>
                            <input type="date" id="data" name="data" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                value="<?= date('Y-m-d') ?>">
                        </div>

                        <!-- Forma de Pagamento -->
                        <div>
                            <label for="forma_pagamento" class="block text-sm font-medium text-gray-700 mb-2">
                                Forma de Pagamento *
                            </label>
                            <select id="forma_pagamento" name="forma_pagamento" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="Dinheiro">Dinheiro</option>
                                <option value="Cartão Débito">Cartão Débito</option>
                                <option value="Cartão Crédito">Cartão Crédito</option>
                                <option value="PIX">PIX</option>
                                <option value="Boleto">Boleto</option>
                                <option value="Transferência">Transferência</option>
                            </select>
                        </div>
                    </div>

                    <!-- Descrição -->
                    <div class="mt-4">
                        <label for="descricao" class="block text-sm font-medium text-gray-700 mb-2">
                            Descrição/Produto
                        </label>
                        <textarea id="descricao" name="descricao" rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Descrição do produto ou serviço..."></textarea>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex flex-col sm:flex-row gap-4 justify-end pt-6">
                    <a href="../tabela/tabela.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition-colors text-center">
                        <i class="fas fa-times mr-2"></i>Cancelar
                    </a>
                    <button type="reset" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                        <i class="fas fa-broom mr-2"></i>Limpar
                    </button>
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                        <i class="fas fa-save mr-2"></i>Registrar Compra
                    </button>
                </div>
            </form>
        </div>

        <!-- Informações -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-blue-800 mb-2">
                <i class="fas fa-info-circle mr-2"></i>Informações
            </h3>
            <ul class="text-blue-700 space-y-1 text-sm">
                <li>• Campos marcados com * são obrigatórios</li>
                <li>• O valor e lucro serão formatados automaticamente</li>
                <li>• A data padrão é a data atual</li>
                <li>• Após o registro, o ranking será atualizado automaticamente</li>
            </ul>
        </div>
    </div>

    <script>
        // Formatar campo de moeda
        function formatarMoeda(campo) {
            let valor = campo.value.replace(/\D/g, '');
            valor = (valor / 100).toFixed(2) + '';
            valor = valor.replace(".", ",");
            valor = valor.replace(/(\d)(\d{3})(\d{3}),/g, "$1.$2.$3,");
            valor = valor.replace(/(\d)(\d{3}),/g, "$1.$2,");
            campo.value = valor;
        }

        // Calcular lucro automaticamente baseado no valor (opcional)
        document.getElementById('valor').addEventListener('blur', function() {
            const valor = parseFloat(this.value.replace('.', '').replace(',', '.'));
            const lucroInput = document.getElementById('lucro');

            if (!isNaN(valor) && !lucroInput.value) {
                // Exemplo: 30% de lucro - ajuste conforme sua necessidade
                const lucroCalculado = valor * 0.3;
                lucroInput.value = lucroCalculado.toFixed(2).replace('.', ',');
                formatarMoeda(lucroInput);
            }
        });

        // Validação do formulário
        document.querySelector('form').addEventListener('submit', function(e) {
            const valor = document.getElementById('valor').value;
            const lucro = document.getElementById('lucro').value;
            const data = document.getElementById('data').value;

            if (!valor || parseFloat(valor.replace('.', '').replace(',', '.')) <= 0) {
                alert('Por favor, informe um valor válido para a venda.');
                e.preventDefault();
                return;
            }

            if (!lucro || parseFloat(lucro.replace('.', '').replace(',', '.')) < 0) {
                alert('Por favor, informe um lucro válido.');
                e.preventDefault();
                return;
            }

            if (!data) {
                alert('Por favor, selecione uma data.');
                e.preventDefault();
                return;
            }
        });
    </script>
</body>

</html>