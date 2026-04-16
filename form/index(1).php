<?php
require_once '../crud_filmes/header.php'

?>
<div class="max-w-4xl mx-auto p-6">

    <!-- ============================================= -->
    <!-- CABEÇALHO -->
    <!-- ============================================= -->
    <div class="mb-8 px-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-user-plus text-blue-600 mr-3"></i>
                    Cadastrar Cliente
                </h1>
                <p class="text-gray-600">Adicione um novo cliente ao sistema de ranking</p>
            </div>
            <a href="../index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Voltar
            </a>
            <a href="/CRUD2/form/CadastraIndex.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-arrow-right mr-2"></i>Próximo
            </a>
        </div>
    </div>

    <!-- ============================================= -->
    <!-- FORMULÁRIO DE CADASTRO -->
    <!-- ============================================= -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <form action="processa_cadastrar.php" method="POST" class="space-y-6">

            <!-- Dados Pessoais -->
            <div class="border-b border-gray-200 pb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">
                    <i class="fas fa-id-card text-blue-500 mr-2"></i>
                    Dados Pessoais
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Nome Completo -->
                    <div>
                        <label for="nome" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome Completo *
                        </label>
                        <input
                            type="text"
                            id="nome"
                            name="nome"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            placeholder="Digite o nome completo">
                    </div>

                    <!-- CPF -->
                    <div>
                        <label for="cpf" class="block text-sm font-medium text-gray-700 mb-2">
                            CPF *
                        </label>
                        <input
                            type="text"
                            id="cpf"
                            name="cpf"
                            required
                            maxlength="14"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            placeholder="000.000.000-00"
                            oninput="formatarCPF(this)">
                        <p class="text-xs text-gray-500 mt-1">Formato: Somente Números</p>
                    </div>
                </div>
            </div>

            <!-- Contato -->
            <div class="border-b border-gray-200 pb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">
                    <i class="fas fa-phone text-green-500 mr-2"></i>
                    Contato
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Telefone -->
                    <div>
                        <label for="telefone" class="block text-sm font-medium text-gray-700 mb-2">
                            Telefone *
                        </label>
                        <input
                            type="tel"
                            id="telefone"
                            name="telefone"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            placeholder="(11) 00000-0000"
                            oninput="formatarTelefone(this)">
                    </div>

                    <!-- Email (Opcional) -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            E-mail
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            placeholder="cliente@email.com">
                    </div>
                </div>
            </div>

            <!-- Informações Adicionais -->
            <div class="border-b border-gray-200 pb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">
                    <i class="fas fa-info-circle text-purple-500 mr-2"></i>
                    Informações Adicionais
                </h2>

                <div class="grid grid-cols-1 gap-4">
                    <!-- Endereço -->
                    <div>
                        <label for="endereco" class="block text-sm font-medium text-gray-700 mb-2">
                            Endereço
                        </label>
                        <input
                            type="text"
                            id="endereco"
                            name="endereco"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            placeholder="Rua, número, bairro">
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="flex flex-col sm:flex-row gap-4 justify-end pt-6">
                <!-- Botão Cancelar -->
                <a href="../index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition-colors text-center">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>

                <!-- Botão Limpar -->
                <button type="reset" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    <i class="fas fa-broom mr-2"></i>Limpar
                </button>

                <!-- Botão Cadastrar -->
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    <i class="fas fa-save mr-2"></i>Cadastrar Cliente
                </button>
            </div>
        </form>
    </div>

    <!-- ============================================= -->
    <!-- INSTRUÇÕES -->
    <!-- ============================================= -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-blue-800 mb-2">
            <i class="fas fa-info-circle mr-2"></i>Instruções
        </h3>
        <ul class="text-blue-700 space-y-1">
            <li>• Campos marcados com * são obrigatórios</li>
            <li>• O CPF será validado automaticamente</li>
            <li>• Após o cadastro, o cliente aparecerá no ranking após a primeira compra</li>
        </ul>
    </div>
</div>


<!-- ============================================= -->
<!-- JAVASCRIPT - Formatação e Validação -->
<!-- ============================================= -->
<script>
    // Formatar CPF (000.000.000-00)
    function formatarCPF(campo) {
        let valor = campo.value.replace(/\D/g, '');

        if (valor.length <= 11) {
            valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
            valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
            valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        }

        campo.value = valor;
    }

    // Formatar Telefone ((11) 99999-9999)
    function formatarTelefone(campo) {
        let valor = campo.value.replace(/\D/g, '');

        if (valor.length <= 11) {
            valor = valor.replace(/(\d{2})(\d)/, '($1) $2');
            valor = valor.replace(/(\d{5})(\d)/, '$1-$2');
        }

        campo.value = valor;
    }

    // Validação do formulário antes de enviar
    document.querySelector('form').addEventListener('submit', function(e) {
        const nome = document.getElementById('nome').value.trim();
        const cpf = document.getElementById('cpf').value.trim();
        const telefone = document.getElementById('telefone').value.trim();

        if (!nome) {
            alert('Por favor, preencha o nome do cliente.');
            e.preventDefault();
            return;
        }

        if (!cpf || cpf.length !== 14) {
            alert('Por favor, preencha um CPF válido.');
            e.preventDefault();
            return;
        }

        if (!telefone || telefone.length < 14) {
            alert('Por favor, preencha um telefone válido.');
            e.preventDefault();
            return;
        }

        // Se tudo estiver ok, pode enviar
        console.log('Formulário validado com sucesso!');
    });
</script>
<?php
require_once '../crud_filmes/footer.php'

?>