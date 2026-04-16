<?php
require_once '../crud_filmes/header.php'
?>
<div class="max-w-4xl mx-auto p-6">

    <!-- CABEÇALHO -->
    <div class="mb-8 px-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-box text-blue-600 mr-3"></i>
                    Cadastrar Produto
                </h1>
                <p class="text-gray-600">Adicione um novo produto ao sistema</p>
            </div>
            <a href="../index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Voltar
            </a>
        </div>
    </div>

    <!-- FORMULÁRIO -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <form action="produto.php" method="POST" class="space-y-6">

            <!-- Dados do Produto -->
            <div class="border-b border-gray-200 pb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">
                    <i class="fas fa-box-open text-blue-500 mr-2"></i>
                    Dados do Produto
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Nome do Produto -->
                    <div>
                        <label for="nome" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome do Produto *
                        </label>
                        <input
                            type="text"
                            id="nome"
                            name="nome"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            placeholder="Digite o nome do produto">
                    </div>

                    <!-- Preço -->
                    <div>
                        <label for="preco" class="block text-sm font-medium text-gray-700 mb-2">
                            Preço de Venda *
                        </label>
                        <input
                            type="text"
                            id="preco"
                            name="preco"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all money-input"
                            placeholder="Ex: 50,00">
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
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            placeholder="Ex: 100">
                    </div>

                    <!-- Custo -->
                    <div>
                        <label for="custo" class="block text-sm font-medium text-gray-700 mb-2">
                            Custo de Produção *
                        </label>
                        <input
                            type="text"
                            id="custo"
                            name="custo"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all money-input"
                            placeholder="Ex: 25,00">
                    </div>
                </div>
            </div>

            <!-- Informações de Lucro (calculadas) -->
            <div id="info-lucro" class="p-4 bg-gray-50 rounded-lg hidden">
                <h4 class="font-semibold text-gray-800 mb-3">
                    <i class="fas fa-chart-line text-green-600 mr-2"></i>
                    Informações de Lucro:
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div class="text-center">
                        <span class="font-semibold">Margem de Lucro:</span>
                        <span id="margem-lucro" class="text-green-600 ml-2 font-semibold">0%</span>
                    </div>
                    <div class="text-center">
                        <span class="font-semibold">Lucro Unitário:</span>
                        <span id="lucro-unitario" class="text-green-600 ml-2 font-semibold">R$ 0,00</span>
                    </div>
                    <div class="text-center">
                        <span class="font-semibold">Valor em Estoque:</span>
                        <span id="valor-estoque" class="text-blue-600 ml-2 font-semibold">R$ 0,00</span>
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex flex-col sm:flex-row gap-4 justify-end pt-6">
                <a href="../index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition-colors text-center">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>

                <button type="reset" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    <i class="fas fa-broom mr-2"></i>Limpar
                </button>

                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    <i class="fas fa-save mr-2"></i>Cadastrar Produto
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Formatação de moeda simplificada e eficiente
    function formatarMoeda(input) {
        // Remove tudo que não é número
        let value = input.value.replace(/\D/g, '');

        // Se estiver vazio, define como 0
        if (value === '') {
            input.value = '0,00';
            return;
        }

        // Converte para número e formata como moeda
        value = (parseInt(value) / 100).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        input.value = value;
    }

    // Função para converter valor formatado para número
    function converterParaNumero(valorFormatado) {
        if (!valorFormatado) return 0;
        // Remove pontos (separadores de milhar) e substitui vírgula por ponto
        return parseFloat(
            valorFormatado.replace(/\./g, '').replace(',', '.')
        ) || 0;
    }

    // Calcular lucro em tempo real
    function calcularLucro() {
        const preco = converterParaNumero(document.getElementById('preco').value);
        const custo = converterParaNumero(document.getElementById('custo').value);
        const estoque = parseInt(document.getElementById('estoque').value) || 0;

        if (preco > 0 && custo > 0) {
            const lucroUnitario = preco - custo;
            const margem = custo > 0 ? (lucroUnitario / custo) * 100 : 0;
            const valorEstoque = preco * estoque;

            // Formata os resultados
            document.getElementById('margem-lucro').textContent = margem.toFixed(1).replace('.', ',') + '%';
            document.getElementById('lucro-unitario').textContent = 'R$ ' + lucroUnitario.toLocaleString('pt-BR', {
                minimumFractionDigits: 2
            });
            document.getElementById('valor-estoque').textContent = 'R$ ' + valorEstoque.toLocaleString('pt-BR', {
                minimumFractionDigits: 2
            });

            // Mostra a seção de informações
            document.getElementById('info-lucro').classList.remove('hidden');
        } else {
            // Esconde a seção se não houver dados suficientes
            document.getElementById('info-lucro').classList.add('hidden');
        }
    }

    // Event listeners para os campos de moeda
    document.querySelectorAll('.money-input').forEach(input => {
        // Formata quando o usuário digita
        input.addEventListener('input', function() {
            formatarMoeda(this);
            calcularLucro();
        });

        // Formata quando o campo perde o foco (para garantir)
        input.addEventListener('blur', function() {
            formatarMoeda(this);
            calcularLucro();
        });

        // Permite apenas números, vírgula e backspace
        input.addEventListener('keydown', function(e) {
            // Permite: backspace, delete, tab, escape, enter, ponto, vírgula e números
            if ([46, 8, 9, 27, 13, 110, 190, 188].includes(e.keyCode) ||
                // Permite: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true) ||
                // Permite: números do teclado principal e numérico
                (e.keyCode >= 48 && e.keyCode <= 57) ||
                (e.keyCode >= 96 && e.keyCode <= 105)) {
                return;
            }
            // Previne qualquer outra tecla
            e.preventDefault();
        });
    });

    // Event listener para estoque
    document.getElementById('estoque').addEventListener('input', calcularLucro);

    // Inicializar formatação quando a página carregar
    document.addEventListener('DOMContentLoaded', function() {
        // Formata os campos de moeda
        document.querySelectorAll('.money-input').forEach(input => {
            if (input.value === '') {
                input.value = '0,00';
            } else {
                formatarMoeda(input);
            }
        });

        // Calcula lucro inicial
        calcularLucro();
    });

    // Reset do formulário também deve recalcular
    document.querySelector('button[type="reset"]').addEventListener('click', function() {
        setTimeout(function() {
            document.querySelectorAll('.money-input').forEach(input => {
                input.value = '0,00';
            });
            calcularLucro();
        }, 100);
    });
</script>

