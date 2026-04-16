<?php
require_once '../conexao/conexao.php';
require_once '../crud_filmes/header.php';

// 1) Recebe filtro do front (GET). Pode vir do select "periodo"
$periodo = $_GET['periodo'] ?? 'mes';

// Definir condições WHERE baseadas no período
if ($periodo === 'hoje') {
    $where = "WHERE DATE(v.data) = CURDATE()";
    $where_totais = "WHERE DATE(data) = CURDATE()";
} elseif ($periodo === 'semana') {
    $where = "WHERE YEARWEEK(v.data, 1) = YEARWEEK(CURDATE(), 1)";
    $where_totais = "WHERE YEARWEEK(data, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($periodo === 'trimestre') {
    $where = "WHERE QUARTER(v.data) = QUARTER(CURDATE()) AND YEAR(v.data) = YEAR(CURDATE())";
    $where_totais = "WHERE QUARTER(data) = QUARTER(CURDATE()) AND YEAR(data) = YEAR(CURDATE())";
} elseif ($periodo === 'ano') {
    $where = "WHERE YEAR(v.data) = YEAR(CURDATE())";
    $where_totais = "WHERE YEAR(data) = YEAR(CURDATE())";
} else {
    // mês atual (padrão)
    $where = "WHERE MONTH(v.data) = MONTH(CURDATE()) AND YEAR(v.data) = YEAR(CURDATE())";
    $where_totais = "WHERE MONTH(data) = MONTH(CURDATE()) AND YEAR(data) = YEAR(CURDATE())";
}

// 2) CONSULTA DOS CARDS (TOTAL, LUCRO, ETC.) - AGORA COM FILTRO
$stmtTotais = $pdo->query("
    SELECT 
        COALESCE(SUM(valor), 0) AS total_vendas, 
        COALESCE(SUM(lucro), 0) AS lucro_total, 
        COALESCE(AVG(lucro), 0) AS lucro_medio,
        COUNT(*) as total_vendas_count
    FROM vendas
    $where_totais
");
$totais = $stmtTotais->fetch(PDO::FETCH_ASSOC);

// 3) Ranking principal (ordenado por total_vendas desc) - AGORA COM FILTRO
$stmt = $pdo->prepare("
    SELECT 
        c.id,
        c.nome,
        c.cpf,
        c.telefone,
        COUNT(v.id) AS quantidade_vendas,
        COALESCE(SUM(v.valor),0) AS total_vendas,
        COALESCE(SUM(v.lucro),0) AS total_lucro,
        CASE 
            WHEN COALESCE(SUM(v.valor),0) >= 10000 THEN 'Ouro'
            WHEN COALESCE(SUM(v.valor),0) >= 6600 THEN 'Prata'
            ELSE 'Bronze'
        END AS status
    FROM clientes c
    LEFT JOIN vendas v ON v.cliente_id = c.id
    $where
    GROUP BY c.id
    ORDER BY total_vendas DESC
    LIMIT 10
");
$stmt->execute();
$ranking = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4) Top do período (Top 3) — por lucro - AGORA COM FILTRO
$stmt = $pdo->prepare("
    SELECT 
      c.nome,
      COALESCE(SUM(v.lucro),0) AS total_lucro
    FROM clientes c
    LEFT JOIN vendas v ON v.cliente_id = c.id
    $where
    GROUP BY c.id
    ORDER BY total_lucro DESC
    LIMIT 3
");
$stmt->execute();
$topMes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5) CONSULTA TAXA DE FIDELIDADE - AGORA COM FILTRO
$total_clientes = $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
$clientes_com_venda = $pdo->query("
    SELECT COUNT(DISTINCT cliente_id) 
    FROM vendas 
    $where_totais
")->fetchColumn();

// 6) DADOS PARA O GRÁFICO COMPARATIVO MENSAL
$stmt_mensal = $pdo->query("
    SELECT 
        MONTH(data) as mes,
        YEAR(data) as ano,
        COALESCE(SUM(valor), 0) as total_vendas,
        COALESCE(SUM(lucro), 0) as total_lucro,
        COUNT(*) as qtd_vendas
    FROM vendas 
    WHERE YEAR(data) = YEAR(CURDATE())
    GROUP BY YEAR(data), MONTH(data)
    ORDER BY ano, mes
");
$dados_mensais = $stmt_mensal->fetchAll(PDO::FETCH_ASSOC);

// Preparar dados para o gráfico
$meses = [];
$vendas_mensais = [];
$lucros_mensais = [];

foreach ($dados_mensais as $dado) {
    $meses[] = DateTime::createFromFormat('!m', $dado['mes'])->format('M');
    $vendas_mensais[] = floatval($dado['total_vendas']);
    $lucros_mensais[] = floatval($dado['total_lucro']);
}

// 7) DADOS DO MÊS ANTERIOR PARA COMPARAÇÃO
$mes_atual = date('m');
$ano_atual = date('Y');
$mes_anterior = $mes_atual - 1;
$ano_anterior = $ano_atual;
if ($mes_anterior == 0) {
    $mes_anterior = 12;
    $ano_anterior = $ano_atual - 1;
}

$stmt_mes_anterior = $pdo->prepare("
    SELECT 
        COALESCE(SUM(valor), 0) as total_vendas,
        COALESCE(SUM(lucro), 0) as total_lucro
    FROM vendas 
    WHERE MONTH(data) = ? AND YEAR(data) = ?
");
$stmt_mes_anterior->execute([$mes_anterior, $ano_anterior]);
$mes_anterior_data = $stmt_mes_anterior->fetch(PDO::FETCH_ASSOC);

// Calcular variações percentuais
$variacao_vendas = 0;
$variacao_lucro = 0;
$variacao_lucro_medio = 0;

if ($mes_anterior_data['total_vendas'] > 0) {
    $variacao_vendas = (($totais['total_vendas'] - $mes_anterior_data['total_vendas']) / $mes_anterior_data['total_vendas']) * 100;
    $variacao_lucro = (($totais['lucro_total'] - $mes_anterior_data['total_lucro']) / $mes_anterior_data['total_lucro']) * 100;
}

// Segurança: garantir valores
$totais['total_vendas'] = $totais['total_vendas'] ?? 0;
$totais['lucro_total'] = $totais['lucro_total'] ?? 0;
$totais['lucro_medio'] = $totais['lucro_medio'] ?? 0;
$totais['total_vendas_count'] = $totais['total_vendas_count'] ?? 0;

$taxa_fidelidade = $total_clientes > 0
    ? ($clientes_com_venda / $total_clientes) * 100
    : 0;
?>

<div class="max-w-7xl mx-auto p-6">

    <!-- CABEÇALHO -->
    <div class="mb-8 px-6">
        <h1 class="text-3xl font-bold text-gray-800">🏆 Ranking de Clientes</h1>
        <p class="text-gray-600">Sistema 18 - Análise completa de performance</p>
    </div>

    <!-- FILTROS AVANÇADOS -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-center">
            <!-- Filtro de Período -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Período</label>
                <select name="periodo" class="w-48 bg-gray-50 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="hoje" <?= $periodo === 'hoje' ? 'selected' : '' ?>>Hoje</option>
                    <option value="semana" <?= $periodo === 'semana' ? 'selected' : '' ?>>Esta Semana</option>
                    <option value="mes" <?= $periodo === 'mes' ? 'selected' : '' ?>>Este Mês</option>
                    <option value="trimestre" <?= $periodo === 'trimestre' ? 'selected' : '' ?>>Este Trimestre</option>
                    <option value="ano" <?= $periodo === 'ano' ? 'selected' : '' ?>>Este Ano</option>
                </select>
            </div>

            <!-- Botão Aplicar Filtros -->
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium transition-colors mt-6">
                <i class="fas fa-filter mr-2"></i>Aplicar Filtros
            </button>
        </form>
    </div>

    <!-- CARDS DE MÉTRICAS -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">

        <!-- Card Total Vendas -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-600 text-sm">Total Vendas</p>
                    <h3 class="text-2xl font-bold text-gray-800">R$ <?= number_format($totais['total_vendas'], 2, ',', '.') ?></h3>
                </div>
                <div class="bg-blue-100 p-3 rounded-lg">
                    <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Card Lucro Total -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-600 text-sm">Lucro Total</p>
                    <h3 class="text-2xl font-bold text-gray-800">R$ <?= number_format($totais['lucro_total'], 2, ',', '.') ?></h3>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <i class="fas fa-chart-line text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Card Lucro Médio -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-600 text-sm">Lucro Médio</p>
                    <h3 class="text-2xl font-bold text-gray-800">R$ <?= number_format($totais['lucro_medio'], 2, ',', '.') ?></h3>
                    <p class="text-gray-500 text-sm mt-1">
                        Por venda
                    </p>
                </div>
                <div class="bg-purple-100 p-3 rounded-lg">
                    <i class="fas fa-balance-scale text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Card Fidelidade -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-orange-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-600 text-sm">Taxa de Fidelidade</p>
                    <h3 class="text-2xl font-bold text-gray-800"><?= round($taxa_fidelidade, 1) ?>%</h3>
                    <p class="text-gray-500 text-sm mt-1">
                        <?= $clientes_com_venda ?> de <?= $total_clientes ?> clientes
                    </p>
                </div>
                <div class="bg-orange-100 p-3 rounded-lg">
                    <i class="fas fa-heart text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- TABELA PRINCIPAL -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">📊 Ranking Geral de Clientes</h2>
                    <p class="text-gray-600 text-sm">Ordenado por volume de vendas - <?=
                                                                                        $periodo === 'hoje' ? 'Hoje' : ($periodo === 'semana' ? 'Esta Semana' : ($periodo === 'trimestre' ? 'Este Trimestre' : ($periodo === 'ano' ? 'Este Ano' : 'Este Mês')))
                                                                                        ?></p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3 px-6 text-left font-semibold text-gray-700">Posição</th>
                                <th class="py-3 px-6 text-left font-semibold text-gray-700">Cliente</th>
                                <th class="py-3 px-6 text-left font-semibold text-gray-700">CPF</th>
                                <th class="py-3 px-6 text-left font-semibold text-gray-700">Total Vendas</th>
                                <th class="py-3 px-6 text-left font-semibold text-gray-700">Lucro</th>
                                <th class="py-3 px-6 text-left font-semibold text-gray-700">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php $pos = 1;
                            foreach ($ranking as $r): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center">
                                            <span class="w-8 h-8 <?=
                                                                    $pos === 1 ? 'bg-yellow-500' : ($pos === 2 ? 'bg-gray-400' :
                                                                            'bg-orange-500')
                                                                    ?> rounded-full flex items-center justify-center text-white font-bold">
                                                <?= $pos ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold mr-3">
                                                <?= strtoupper(substr($r['nome'], 0, 2)) ?>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($r['nome']) ?></p>
                                                <p class="text-gray-500 text-sm"><?= $r['quantidade_vendas'] ?> vendas</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="text-gray-700"><?= htmlspecialchars($r['cpf']) ?></p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="font-bold text-gray-800">R$ <?= number_format($r['total_vendas'], 2, ',', '.') ?></span>
                                    </td>
                                    <td class="py-4 px-6">  
                                        <span class="font-bold text-green-600">R$ <?= number_format($r['total_lucro'], 2, ',', '.') ?></span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <?php if ($r['status'] === 'Ouro'): ?>
                                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                                                <i class="fas fa-medal mr-1"></i> Ouro
                                            </span>
                                        <?php elseif ($r['status'] === 'Prata'): ?>
                                            <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">
                                                <i class="fas fa-medal mr-1"></i> Prata
                                            </span>
                                        <?php else: ?>
                                            <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-sm font-medium">
                                                <i class="fas fa-medal mr-1"></i> Bronze
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php $pos++;
                            endforeach; ?>

                            <?php if (empty($ranking)): ?>
                                <tr>
                                    <td colspan="6" class="py-8 px-6 text-center text-gray-500">
                                        <i class="fas fa-inbox text-4xl mb-2 block"></i>
                                        Nenhuma venda encontrada para o período selecionado
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SIDEBAR COM RELATÓRIOS ESPECÍFICOS -->
        <div class="space-y-6">

            <!-- TOP DO PERÍODO -->
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-lg font-bold text-yellow-600 mb-4">
                    ⭐ Top do <?= $periodo === 'hoje' ? 'Dia' : ($periodo === 'semana' ? 'Semana' : ($periodo === 'trimestre' ? 'Trimestre' : ($periodo === 'ano' ? 'Ano' : 'Mês'))) ?>
                </h2>
                <?php $pos = 1;
                foreach ($topMes as $cliente): ?>
                    <div class="flex justify-between items-center mb-3 p-3 rounded-lg 
                                <?= $pos === 1 ? 'bg-yellow-50 border border-yellow-200' : ($pos === 2 ? 'bg-gray-100 border border-gray-200' :
                                        'bg-orange-50 border border-orange-200') ?>">
                        <div class="flex items-center gap-3">
                            <span class="text-lg font-bold <?=
                                                            $pos === 1 ? 'text-yellow-600' : ($pos === 2 ? 'text-gray-600' :
                                                                    'text-orange-600')
                                                            ?>"><?= $pos ?>º</span>
                            <span class="font-semibold text-gray-800"><?= htmlspecialchars($cliente['nome']) ?></span>
                        </div>
                        <span class="font-bold text-green-600">
                            R$ <?= number_format($cliente['total_lucro'], 2, ',', '.') ?>
                        </span>
                    </div>
                <?php $pos++;
                endforeach; ?>

                <?php if (empty($topMes)): ?>
                    <p class="text-gray-500 text-center py-4">Nenhum dado disponível</p>
                <?php endif; ?>
            </div>

            <!-- GRÁFICO COMPARATIVO MENSAL -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">📊 Comparativo Mensal <?= date('Y') ?></h3>
                <canvas id="comparativoChart" height="200"></canvas>
            </div>

            <!-- RESUMO DO PERÍODO -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">📈 Resumo do Período</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total de Vendas:</span>
                        <span class="font-semibold"><?= $totais['total_vendas_count'] ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Clientes Ativos:</span>
                        <span class="font-semibold"><?= $clientes_com_venda ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Ticket Médio:</span>
                        <span class="font-semibold">R$ <?= number_format($totais['total_vendas_count'] > 0 ? $totais['total_vendas'] / $totais['total_vendas_count'] : 0, 2, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Margem de Lucro:</span>
                        <span class="font-semibold text-green-600">
                            <?= $totais['total_vendas'] > 0 ? number_format(($totais['lucro_total'] / $totais['total_vendas']) * 100, 1) : '0' ?>%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico Comparativo Mensal
    const ctx = document.getElementById('comparativoChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($meses) ?>,
            datasets: [{
                    label: 'Vendas',
                    data: <?= json_encode($vendas_mensais) ?>,
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Lucro',
                    data: <?= json_encode($lucros_mensais) ?>,
                    backgroundColor: 'rgba(34, 197, 94, 0.8)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += 'R$ ' + context.parsed.y.toLocaleString('pt-BR', {
                                minimumFractionDigits: 2
                            });
                            return label;
                        }
                    }
                }
            }
        }
    });

    // Atualizar página quando mudar o filtro
    document.querySelector('select[name="periodo"]').addEventListener('change', function() {
        document.querySelector('form').submit();
    });
</script>