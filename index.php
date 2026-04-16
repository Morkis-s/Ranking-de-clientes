<?php
require_once __DIR__ . '/crud_filmes/header.php';
require_once 'conexao/conexao.php';
?>


<main class="grid grid-cols-1 gap-8 max-w-7xl mx-auto p-6">


  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

    <!-- CARD 1: TOTAL DE CLIENTES -->
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center">
        <!-- Ícone decorativo -->
        <div class="p-3 bg-blue-100 rounded-lg">
          <i class="fas fa-users text-blue-600 text-xl"></i>
        </div>
        <!-- Conteúdo do card -->
        <div class="ml-4">
          <p class="text-sm text-gray-600">Total de Clientes</p>
          <?php
          try {
            $stmt = $pdo->query("SELECT COUNT(*) AS total FROM clientes");
            $row = $stmt->fetch();
            $total_clientes = $row['total'];
          } catch (PDOException $e) {
            $total_clientes = 0;
            echo "Sem clientes cadastrados: " . $e->getMessage();
          }
          ?>

          <p class="text-2xl font-bold text-gray-800"><?php echo
                                                      $total_clientes; ?> </p>
        </div>
      </div>
    </div>

    <!-- CARD 2: STATUS DO RANKING -->
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center">
        <!-- Ícone decorativo -->
        <div class="p-3 bg-green-100 rounded-lg">
          <i class="fas fa-trophy text-green-600 text-xl"></i>
        </div>
        <!-- Conteúdo do card -->
        <div class="ml-4">
          <p class="text-sm text-gray-600">Ranking Atualizado</p>
          <?php
          try {
            // Buscar a data da última venda que afetou o ranking (1°, 2° ou 3° lugar)
            $stmt = $pdo->query("
                    SELECT MAX(v.data) AS ultima_atualizacao 
                    FROM vendas v 
                    WHERE v.cliente_id IN (
                        SELECT cliente_id 
                        FROM (
                            SELECT cliente_id, SUM(valor) as total_vendas
                            FROM vendas 
                            GROUP BY cliente_id 
                            ORDER BY total_vendas DESC 
                            LIMIT 3
                        ) top3
                    )
                ");
            $row = $stmt->fetch();
            $ultima_atualizacao = $row['ultima_atualizacao'];

            if ($ultima_atualizacao) {
              $data_formatada = date('d/m/Y', strtotime($ultima_atualizacao));
            } else {
              // Se não houver vendas nos top 3, usar a data da última venda geral
              $stmt_geral = $pdo->query("SELECT MAX(data) AS ultima_venda FROM vendas");
              $row_geral = $stmt_geral->fetch();
              $ultima_atualizacao = $row_geral['ultima_venda'];
              $data_formatada = $ultima_atualizacao ? date('d/m/Y', strtotime($ultima_atualizacao)) : 'N/A';
            }
          } catch (PDOException $e) {
            $data_formatada = 'N/A';
          }
          ?>
          <p class="text-2xl font-bold text-gray-800"><?= $data_formatada ?></p>
        </div>
      </div>
    </div>

    <!-- CARD 3: ÚLTIMO CADASTRO -->
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center">
        <!-- Ícone decorativo -->
        <div class="p-3 bg-purple-100 rounded-lg">
          <i class="fas fa-clock text-purple-600 text-xl"></i>
        </div>
        <!-- Conteúdo do card -->
        <div class="ml-4">
          <p class="text-sm text-gray-600">Último Cadastro</p>
          <?php
          $stmt = $pdo->query("SELECT MAX(data_cadastro) AS ultima_data FROM clientes");
          $row = $stmt->fetch();
          $ultima_data = $row['ultima_data'];
          // CORREÇÃO: Formatar data e hora completa
          if ($ultima_data) {
            $hora_formatada = date('H:i', strtotime($ultima_data));
            echo "<p class='text-2xl font-bold text-gray-800'>" . $hora_formatada . "</p>";
          } else {
            echo "<p class='text-2xl font-bold text-gray-800'>--:--</p>";
          }
          ?>
        </div>
      </div>
    </div>
  </div>


  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

    <!-- CARD: VER RANKING -->
    <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-300">
      <div class="p-6">
        <!-- Ícone com gradiente -->
        <div class="w-12 h-12 bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-lg flex items-center justify-center mb-4">
          <i class="fas fa-trophy text-white text-xl"></i>
        </div>
        <h3 class="text-xl font-semibold text-gray-800 mb-2">Ver Ranking</h3>
        <p class="text-gray-600 mb-4">Visualize a classificação atual dos clientes</p>
        <!-- 
          BACKEND: LINK PARA PÁGINA DE RANKING
          Atualizar href para o arquivo correto do ranking
        -->
        <a href="tabela/ranking.php">
          <button class="w-full bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-4 rounded-lg transition-colors duration-300">
            Acessar Ranking
          </button>
        </a>
      </div>
    </div>

    <!-- CARD: CADASTRAR CLIENTES -->
    <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-300">
      <div class="p-6">
        <!-- Ícone com gradiente -->
        <div class="w-12 h-12 bg-gradient-to-r from-green-400 to-green-500 rounded-lg flex items-center justify-center mb-4">
          <i class="fas fa-user-plus text-white text-xl"></i>
        </div>
        <h3 class="text-xl font-semibold text-gray-800 mb-2">Cadastrar Clientes/Produtos</h3>
        <p class="text-gray-600 mb-4">Adicione um novo cliente ou produto ao sistema</p>
        <a href="form/index(1).php">
          <button class="w-full bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg transition-colors duration-300">
            Novo Cliente/Produto
          </button>
        </a>
      </div>
    </div>

    <!-- CARD: VER TABELA DE CLIENTES -->
    <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-300">
      <div class="p-6">
        <!-- Ícone com gradiente -->
        <div class="w-12 h-12 bg-gradient-to-r from-blue-400 to-blue-500 rounded-lg flex items-center justify-center mb-4">
          <i class="fas fa-table text-white text-xl"></i>
        </div>
        <h3 class="text-xl font-semibold text-gray-800 mb-2">Ver Tabela de Clientes</h3>
        <p class="text-gray-600 mb-4">Consulte e edite os registros existentes</p>
        <!-- 
          BACKEND: LINK PARA TABELA DE CLIENTES
          Atualizar href para o arquivo correto da tabela
        -->
        <a href="tabelaSec/tabela.php">
          <button class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg transition-colors duration-300">
            Ver Tabela
          </button>
        </a>
      </div>
    </div>
  </div>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

    <!-- CARD: CLIENTES RECENTES -->
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-xl font-semibold text-gray-800 mb-4">
        <i class="fas fa-history text-gray-400 mr-2"></i>
        Clientes Recentes
      </h3>

      <div class="space-y-3">
        <?php
        try {
          $stmt = $pdo->query("SELECT nome, data_cadastro FROM clientes ORDER BY data_cadastro DESC LIMIT 5");
          $clientes_recentes = $stmt->fetchAll();

          if (count($clientes_recentes) > 0) {
            foreach ($clientes_recentes as $cliente) {
              // CORREÇÃO: Formatar a hora de CADA cliente individualmente
              $hora_cliente = date('H:i', strtotime($cliente['data_cadastro']));
              $iniciais = substr($cliente['nome'], 0, 2);
              $cores = ['bg-blue-100 text-blue-600', 'bg-green-100 text-green-600', 'bg-purple-100 text-purple-600', 'bg-yellow-100 text-yellow-600', 'bg-red-100 text-red-600'];
              $cor_aleatoria = $cores[array_rand($cores)];
        ?>
              <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors cursor-pointer"
                onclick="buscarCliente('<?= htmlspecialchars($cliente['nome']) ?>')">
                <div class="flex items-center">
                  <div class="w-8 h-8 <?= $cor_aleatoria ?> rounded-full flex items-center justify-center font-bold text-sm">
                    <?= $iniciais ?>
                  </div>
                  <span class="ml-3 font-medium"><?= htmlspecialchars($cliente['nome']) ?></span>
                </div>
                <!-- CORREÇÃO: Mostrar hora do cliente específico -->
                <span class="text-sm text-gray-500"><?= $hora_cliente ?></span>
              </div>
        <?php
            }
          } else {
            echo '<p class="text-gray-500 text-center py-4">Nenhum cliente cadastrado</p>';
          }
        } catch (PDOException $e) {
          echo '<p class="text-red-500 text-center py-4">Erro ao carregar clientes</p>';
        }
        ?>
      </div>
    </div>

    <!-- CARD: AÇÕES RÁPIDAS -->
    <!-- CARD: AÇÕES RÁPIDAS -->
    <div class="bg-white rounded-lg shadow p-6">




      <!-- Card Exportar Dados que ocupa o restante do espaço -->
      <div class="bg-gray-50 rounded-lg p-4 flex-grow">
        <h4 class="text-lg font-semibold text-gray-800 mb-3">
          <i class="fas fa-file-export text-purple-600 mr-2"></i>
          Exportar Dados
        </h4>

        <div class="space-y-3">
          <!-- Formato -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Formato</label>
            <select id="formatoExport" class="w-full border border-gray-300 rounded-lg p-2 bg-white">
              <option value="csv">CSV</option>
              <option value="excel">Excel</option>
              <option value="pdf">PDF</option>
            </select>
          </div>

          <!-- Tipo de Dados -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dados</label>
            <select id="tipoExport" class="w-full border border-gray-300 rounded-lg p-2 bg-white">
              <option value="clientes">Clientes</option>
              <option value="vendas">Vendas</option>
              <option value="ranking">Ranking</option>
            </select>
          </div>

          <!-- Botão Exportar -->
          <button onclick="exportarDados()"
            class="w-full bg-purple-500 hover:bg-purple-600 text-white py-2 px-4 rounded-lg transition-colors mt-2">
            <i class="fas fa-download mr-2"></i>Exportar Dados
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    function exportarDados() {
      const formato = document.getElementById('formatoExport').value;
      const tipo = document.getElementById('tipoExport').value;

      // Redirecionar para a página de exportação
      window.location.href = `exporta.php?formato=${formato}&tipo=${tipo}`;
    }
  </script>
  </div>
  </div>
  </div>
</main>

<?php
require_once __DIR__ . '/crud_filmes/footer.php';
?>

<script>
  const modalConfig = document.getElementById('modal-config');
  const btnFechar = document.getElementById('fechar-modal');

  function abrirModalConfig() {
    modalConfig.classList.remove('hidden');
  }

  function fecharModal() {
    modalConfig.classList.add('hidden');
  }

  btnFechar.addEventListener('click', fecharModal);

  modalConfig.addEventListener('click', function(e) {
    if (e.target === modalConfig) {
      fecharModal();
    }
  });

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !modalConfig.classList.contains('hidden')) {
      fecharModal();
    }
  });
</script>