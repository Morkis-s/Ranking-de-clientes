<?php

require_once '../conexao/conexao.php';
require_once '../crud_filmes/header.php';

session_start();

// Processar exclusão se veio por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_cliente'])) {
    $cliente_id = intval($_POST['cliente_id']);

    try {
        $sql_verifica = "SELECT id FROM clientes WHERE id = ?";
        $stmt_verifica = $pdo->prepare($sql_verifica);
        $stmt_verifica->execute([$cliente_id]);

        if ($stmt_verifica->rowCount() > 0) {
            $sql_excluir = "DELETE FROM clientes WHERE id = ?";
            $stmt_excluir = $pdo->prepare($sql_excluir);

            if ($stmt_excluir->execute([$cliente_id])) {
                $_SESSION['sucesso'] = "Cliente excluído com sucesso!";
            } else {
                $_SESSION['erro'] = "Erro ao excluir cliente!";
            }
        } else {
            $_SESSION['erro'] = "Cliente não encontrado!";
        }
    } catch (PDOException $e) {
        $_SESSION['erro'] = "Erro ao excluir cliente: " . $e->getMessage();
    }

    header("Location: tabela.php");
    exit();
}

// =============================================
// CONFIGURAÇÃO DA PAGINAÇÃO
// =============================================
$clientes_por_pagina = 10; // 👈 MUDE AQUI para quantos clientes por página
$pagina_atual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina_atual - 1) * $clientes_por_pagina;

// Buscar termo de pesquisa
$termo_pesquisa = isset($_GET['pesquisa']) ? trim($_GET['pesquisa']) : '';

try {
    // CONSULTA PARA CONTAR TOTAL DE CLIENTES
    $sql_count = "SELECT COUNT(*) as total FROM clientes c";
    $params_count = [];

    if (!empty($termo_pesquisa)) {
        $sql_count .= " WHERE c.nome LIKE ? OR c.cpf LIKE ? OR c.telefone LIKE ?";
        $termo_like = "%$termo_pesquisa%";
        $params_count = [$termo_like, $termo_like, $termo_like];
    }

    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->execute($params_count);
    $total_clientes = $stmt_count->fetch()['total'];

    // CALCULAR TOTAL DE PÁGINAS
    $total_paginas = ceil($total_clientes / $clientes_por_pagina);

    // CONSULTA PARA BUSCAR CLIENTES DA PÁGINA ATUAL
    $sql = "SELECT 
    c.id,
    c.nome,
    c.cpf,
    c.telefone,
    c.email,
    DATE_FORMAT(c.data_cadastro, '%d/%m/%Y') as data_cadastro,
    COALESCE(SUM(vp.quantidade), 0) as total_compras,
    COALESCE(SUM(v.valor), 0) as total_vendas
    FROM clientes c 
    LEFT JOIN vendas v ON c.id = v.cliente_id 
    LEFT JOIN vendas_produtos vp ON v.id = vp.venda_id ";

    $params = [];
    if (!empty($termo_pesquisa)) {
        $sql .= " WHERE c.nome LIKE ? OR c.cpf LIKE ? OR c.telefone LIKE ?";
        $termo_like = "%$termo_pesquisa%";
        $params = [$termo_like, $termo_like, $termo_like];
    }

    $sql .= " GROUP BY c.id, c.nome, c.cpf, c.telefone, c.email, c.data_cadastro
          ORDER BY c.id ASC
          LIMIT $clientes_por_pagina OFFSET $offset";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $clientes = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro ao buscar clientes: " . $e->getMessage());
}
?>

<div class="max-w-7xl mx-auto p-6">

    <!-- CABEÇALHO -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-users text-blue-600 mr-3"></i>
                    Tabela de Clientes
                </h1>
                <p class="text-gray-600">Gerencie todos os clientes do sistema</p>
            </div>
            <div class="flex gap-3">
                <a href="../index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar
                </a>
                <a href="../form/index(1).php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-user-plus mr-2"></i>Novo Cliente
                </a>
            </div>
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

    <!-- BARRA DE PESQUISA -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <form method="GET" action="">
            <div class="flex flex-col md:flex-row gap-4 items-center">
                <div class="flex-1 w-full">
                    <div class="relative">
                        <input
                            type="text"
                            name="pesquisa"
                            value="<?= htmlspecialchars($termo_pesquisa) ?>"
                            placeholder="Buscar por nome, CPF ou telefone..."
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg transition-colors">
                    <i class="fas fa-search mr-2"></i>Buscar
                </button>
                <?php if (!empty($termo_pesquisa)): ?>
                    <a href="tabela.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-3 rounded-lg transition-colors">
                        <i class="fas fa-times mr-2"></i>Limpar
                    </a>
                <?php endif; ?>
            </div>
            <!-- Manter página atual na pesquisa -->
            <input type="hidden" name="pagina" value="1">
        </form>
    </div>

    <!-- TABELA -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-xl font-semibold text-gray-800">
                <i class="fas fa-table text-gray-600 mr-2"></i>
                Lista de Clientes
            </h2>
            <p class="text-gray-600 text-sm">
                Mostrando <?= count($clientes) ?> de <?= $total_clientes ?> cliente(s)
                <?= !empty($termo_pesquisa) ? ' para "' . htmlspecialchars($termo_pesquisa) . '"' : '' ?>
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-6 text-left font-semibold text-gray-700">ID</th>
                        <th class="py-3 px-6 text-left font-semibold text-gray-700">Cliente</th>
                        <th class="py-3 px-6 text-left font-semibold text-gray-700">CPF</th>
                        <th class="py-3 px-6 text-left font-semibold text-gray-700">Telefone</th>
                        <th class="py-3 px-6 text-left font-semibold text-gray-700">Data Cadastro</th>
                        <th class="py-3 px-6 text-left font-semibold text-gray-700">Compras</th>
                        <th class="py-3 px-6 text-left font-semibold text-gray-700">Total Gasto</th>
                        <th class="py-3 px-6 text-left font-semibold text-gray-700">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (count($clientes) > 0): ?>
                        <?php foreach ($clientes as $cliente): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="py-4 px-6">
                                    <span class="font-mono text-sm text-gray-500">#<?= str_pad($cliente['id'], 3, '0', STR_PAD_LEFT) ?></span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold mr-3">
                                            <?= substr($cliente['nome'], 0, 2) ?>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($cliente['nome']) ?></p>
                                            <p class="text-sm text-gray-500"><?= $cliente['email'] ?: 'Sem e-mail' ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <p class="text-gray-700 font-mono text-sm"><?= $cliente['cpf'] ?></p>
                                </td>
                                <td class="py-4 px-6">
                                    <p class="text-gray-700"><?= $cliente['telefone'] ?></p>
                                </td>
                                <td class="py-4 px-6">
                                    <p class="text-gray-700 text-sm"><?= $cliente['data_cadastro'] ?></p>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center px-3 py-1 <?= $cliente['total_compras'] > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?> rounded-full text-sm font-medium">
                                        <i class="fas fa-shopping-cart mr-1"></i>
                                        <?= $cliente['total_compras'] ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <p class="text-gray-700 font-semibold">
                                        R$ <?= number_format($cliente['total_vendas'], 2, ',', '.') ?>
                                    </p>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex gap-2">
                                        <!-- Botão Editar -->
                                        <a href="../tabela/editar.php?id=<?= $cliente['id'] ?>"
                                            class="fas fa-edit text-sm bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg transition-colors"
                                            title="Editar Cliente">
                                        </a>

                                        <!-- BOTÃO CORRIGIDO - Adicionar Compra -->
                                        <a href="../form/Cliente_Compra.php?cliente_id=<?= $cliente['id'] ?>"
                                            class="bg-green-500 hover:bg-green-600 text-white p-2 rounded-lg transition-colors"
                                            title="Adicionar Compra">
                                            <i class="fas fa-cart-plus text-sm"></i>
                                        </a>

                                        <!-- Botão Excluir -->
                                        <button onclick="confirmarExclusao(<?= $cliente['id'] ?>, '<?= htmlspecialchars($cliente['nome']) ?>')"
                                            class="fas fa-trash text-sm bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg transition-colors"
                                            title="Excluir Cliente">
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="py-12 px-6 text-center text-gray-500">
                                <i class="fas fa-users text-4xl mb-4 text-gray-300"></i>
                                <p class="text-lg font-semibold mb-2">
                                    <?= !empty($termo_pesquisa) ? 'Nenhum cliente encontrado' : 'Nenhum cliente cadastrado' ?>
                                </p>
                                <p class="text-sm mb-4">
                                    <?= !empty($termo_pesquisa) ? 'Tente ajustar os termos da pesquisa.' : 'Cadastre o primeiro cliente para começar.' ?>
                                </p>
                                <a href="../form/index(1).php"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors inline-block">
                                    <i class="fas fa-user-plus mr-2"></i>Cadastrar Cliente
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ============================================= -->
        <!-- PAGINAÇÃO -->
        <!-- ============================================= -->
        <?php if ($total_paginas > 1): ?>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    <p class="text-sm text-gray-600">
                        Página <strong><?= $pagina_atual ?></strong> de <strong><?= $total_paginas ?></strong>
                        - <strong><?= $total_clientes ?></strong> cliente(s) no total
                    </p>

                    <div class="flex gap-2">
                        <!-- Botão Anterior -->
                        <?php if ($pagina_atual > 1): ?>
                            <a href="?pagina=<?= $pagina_atual - 1 ?><?= !empty($termo_pesquisa) ? '&pesquisa=' . urlencode($termo_pesquisa) : '' ?>"
                                class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                <i class="fas fa-chevron-left mr-2"></i>Anterior
                            </a>
                        <?php else: ?>
                            <span class="bg-gray-100 border border-gray-300 text-gray-400 px-4 py-2 rounded-lg text-sm font-medium cursor-not-allowed">
                                <i class="fas fa-chevron-left mr-2"></i>Anterior
                            </span>
                        <?php endif; ?>

                        <!-- Números das Páginas -->
                        <div class="hidden md:flex gap-1">
                            <?php
                            // Mostrar até 5 páginas ao redor da atual
                            $inicio = max(1, $pagina_atual - 2);
                            $fim = min($total_paginas, $pagina_atual + 2);

                            for ($i = $inicio; $i <= $fim; $i++):
                            ?>
                                <a href="?pagina=<?= $i ?><?= !empty($termo_pesquisa) ? '&pesquisa=' . urlencode($termo_pesquisa) : '' ?>"
                                    class="px-3 py-2 rounded-lg text-sm font-medium transition-colors <?= $i == $pagina_atual ? 'bg-blue-500 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </div>

                        <!-- Botão Próximo -->
                        <?php if ($pagina_atual < $total_paginas): ?>
                            <a href="?pagina=<?= $pagina_atual + 1 ?><?= !empty($termo_pesquisa) ? '&pesquisa=' . urlencode($termo_pesquisa) : '' ?>"
                                class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                Próximo<i class="fas fa-chevron-right ml-2"></i>
                            </a>
                        <?php else: ?>
                            <span class="bg-gray-100 border border-gray-300 text-gray-400 px-4 py-2 rounded-lg text-sm font-medium cursor-not-allowed">
                                Próximo<i class="fas fa-chevron-right ml-2"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL DE EXCLUSÃO -->
<div id="modal-excluir" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden z-50 grid place-items-center">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96">
        <div class="text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Confirmar Exclusão</h3>
            <p class="text-gray-600 mb-4" id="texto-confirmacao">Tem certeza que deseja excluir este cliente?</p>
            <p class="text-sm text-red-500 mb-6">Esta ação não pode ser desfeita!</p>

            <form method="POST" action="" id="form-excluir">
                <input type="hidden" name="excluir_cliente" value="1">
                <input type="hidden" name="cliente_id" id="cliente_id_excluir">

                <div class="flex justify-center gap-3">
                    <button type="button" id="cancelar-exclusao" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg transition-colors">
                        <i class="fas fa-trash mr-2"></i>Excluir
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Funções JavaScript
    function editarCliente(clienteId) {
        if (confirm(`Deseja editar o cliente #${clienteId}?`)) {
            window.location.href = `../tabela/editar.php?id=${clienteId}`;
        }
    }

    function confirmarExclusao(clienteId, clienteNome) {
        document.getElementById('cliente_id_excluir').value = clienteId;
        document.getElementById('texto-confirmacao').innerHTML =
            `Tem certeza que deseja excluir o cliente <strong>"${clienteNome}"</strong>?`;

        const modal = document.getElementById('modal-excluir');
        modal.classList.remove('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('modal-excluir');
        const btnCancelar = document.getElementById('cancelar-exclusao');

        btnCancelar.addEventListener('click', function() {
            modal.classList.add('hidden');
        });

        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.add('hidden');
            }
        });
    });
</script>
</body>

</html>