<?php
// exporta.php - SISTEMA DE EXPORTAÇÃO CORRIGIDO COM CSV E EXCEL REAIS
require_once 'conexao/conexao.php';
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

session_start();

// Verificar se foi solicitado exportação
$formato = $_GET['formato'] ?? 'pdf';
$tipo_dados = $_GET['tipo'] ?? 'clientes';

// Configurar headers para download
function setDownloadHeaders($filename, $contentType)
{
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');
}

// Função para buscar dados de clientes
function buscarDadosClientes($pdo)
{
    $sql = "SELECT 
        c.id,
        c.nome,
        c.cpf,
        c.telefone,
        c.email,
        c.endereco,
        DATE_FORMAT(c.data_cadastro, '%d/%m/%Y %H:%i') as data_cadastro,
        COUNT(v.id) as total_compras,
        COALESCE(SUM(v.valor), 0) as total_vendas,
        COALESCE(SUM(v.lucro), 0) as total_lucro,
        CASE 
            WHEN COALESCE(SUM(v.valor), 0) >= 2000 THEN 'Ouro'
            WHEN COALESCE(SUM(v.valor), 0) >= 1000 THEN 'Prata'
            ELSE 'Bronze'
        END AS status
    FROM clientes c 
    LEFT JOIN vendas v ON c.id = v.cliente_id 
    GROUP BY c.id
    ORDER BY total_vendas DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Função para buscar dados de vendas
function buscarDadosVendas($pdo)
{
    $sql = "SELECT 
        v.id,
        c.nome as cliente,
        v.valor,
        v.lucro,
        DATE_FORMAT(v.data, '%d/%m/%Y') as data_venda,
        (v.lucro / v.valor * 100) as margem
    FROM vendas v
    LEFT JOIN clientes c ON v.cliente_id = c.id
    ORDER BY v.data DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Função para buscar dados do ranking
function buscarDadosRanking($pdo)
{
    $sql = "SELECT 
        c.id,
        c.nome,
        c.cpf,
        COUNT(v.id) as quantidade_vendas,
        COALESCE(SUM(v.valor), 0) as total_vendas,
        COALESCE(SUM(v.lucro), 0) as total_lucro,
        CASE 
            WHEN COALESCE(SUM(v.valor), 0) >= 2000 THEN 'Ouro'
            WHEN COALESCE(SUM(v.valor), 0) >= 1000 THEN 'Prata'
            ELSE 'Bronze'
        END AS status,
        ROUND((COALESCE(SUM(v.lucro), 0) / COALESCE(SUM(v.valor), 1) * 100), 2) as margem_percentual
    FROM clientes c
    LEFT JOIN vendas v ON v.cliente_id = c.id
    GROUP BY c.id
    ORDER BY total_vendas DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

// FUNÇÃO PARA EXPORTAR CSV
function exportarCSV($dados, $cabecalhos, $filename, $titulo)
{
    setDownloadHeaders($filename, 'text/csv; charset=utf-8');

    // Criar output em memória
    $output = fopen('php://output', 'w');

    // Adicionar BOM para UTF-8 (importante para Excel)
    fwrite($output, "\xEF\xBB\xBF");

    // Escrever título como comentário
    fputcsv($output, ["# $titulo"], ';');
    fputcsv($output, ["# Exportado em: " . date('d/m/Y H:i:s')], ';');
    fputcsv($output, [""], ';'); // Linha vazia

    // Escrever cabeçalhos
    fputcsv($output, $cabecalhos, ';');

    // Escrever dados
    foreach ($dados as $linha) {
        fputcsv($output, $linha, ';');
    }

    fclose($output);
    exit;
}

// FUNÇÃO PARA EXPORTAR EXCEL (XLS)
function exportarExcel($dados, $cabecalhos, $filename, $titulo)
{
    setDownloadHeaders($filename, 'application/vnd.ms-excel');

    echo "<html>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<style>
        table { border-collapse: collapse; width: 100%; }
        th { background-color: #366092; color: white; font-weight: bold; padding: 8px; border: 1px solid #ddd; }
        td { padding: 6px; border: 1px solid #ddd; }
        .titulo { font-size: 16px; font-weight: bold; margin-bottom: 10px; }
        .info { font-size: 12px; color: #666; margin-bottom: 15px; }
    </style>";
    echo "</head>";
    echo "<body>";

    // Título e informações
    echo "<div class='titulo'>$titulo</div>";
    echo "<div class='info'>Exportado em: " . date('d/m/Y H:i:s') . " | Total de registros: " . count($dados) . "</div>";

    // Tabela com dados
    echo "<table>";

    // Cabeçalhos
    echo "<tr>";
    foreach ($cabecalhos as $cabecalho) {
        echo "<th>" . htmlspecialchars($cabecalho) . "</th>";
    }
    echo "</tr>";

    // Dados
    foreach ($dados as $linha) {
        echo "<tr>";
        foreach ($linha as $celula) {
            echo "<td>" . htmlspecialchars($celula) . "</td>";
        }
        echo "</tr>";
    }

    echo "</table>";
    echo "</body>";
    echo "</html>";
    exit;
}

// FUNÇÃO PARA EXPORTAR PDF (mantida igual)
function exportarPDF($dados, $cabecalhos, $filename, $titulo)
{
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'Arial');
    $options->set('isPhpEnabled', true);

    $dompdf = new Dompdf($options);

    $estilos = '
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .header h1 { color: #2c3e50; margin: 0; font-size: 24px; }
        .info { margin: 10px 0; color: #666; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 10px; }
        th { background-color: #34495e; color: white; font-weight: bold; padding: 8px; text-align: left; border: 1px solid #ddd; }
        td { padding: 6px; border: 1px solid #ddd; text-align: left; }
        tr:nth-child(even) { background-color: #f8f9fa; }
        .footer { margin-top: 30px; text-align: center; color: #666; font-size: 9px; border-top: 1px solid #ddd; padding-top: 10px; }
        .status-ouro { color: #ffd700; font-weight: bold; }
        .status-prata { color: #c0c0c0; font-weight: bold; }
        .status-bronze { color: #cd7f32; font-weight: bold; }
    </style>';

    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>' . htmlspecialchars($titulo) . '</title>
        ' . $estilos . '
    </head>
    <body>
        <div class="header">
            <h1>' . htmlspecialchars($titulo) . '</h1>
            <div class="info">
                <strong>Data:</strong> ' . date('d/m/Y') . ' | 
                <strong>Hora:</strong> ' . date('H:i') . ' | 
                <strong>Registros:</strong> ' . count($dados) . '
            </div>
        </div>
        
        <table>
            <thead><tr>';

    foreach ($cabecalhos as $cabecalho) {
        $html .= '<th>' . htmlspecialchars($cabecalho) . '</th>';
    }

    $html .= '</tr></thead><tbody>';

    foreach ($dados as $linha) {
        $html .= '<tr>';
        foreach ($linha as $celula) {
            $html .= '<td>' . htmlspecialchars($celula) . '</td>';
        }
        $html .= '</tr>';
    }

    $html .= '</tbody></table>
        <div class="footer">
            <p>Exportado pelo Sistema - ' . date('d/m/Y H:i') . '</p>
        </div>
    </body>
    </html>';

    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    echo $dompdf->output();
    exit;
}

try {
    // Buscar dados baseado no tipo
    switch ($tipo_dados) {
        case 'vendas':
            $dados_brutos = buscarDadosVendas($pdo);
            $cabecalhos = ['ID', 'Cliente', 'Valor (R$)', 'Lucro (R$)', 'Data', 'Margem (%)'];
            $titulo = 'Relatório de Vendas - Sistema de Gestão';
            $filename = "relatorio_vendas_" . date('Y-m-d_H-i') . ".{$formato}";

            $dados_processados = [];
            foreach ($dados_brutos as $venda) {
                $dados_processados[] = [
                    $venda['id'],
                    $venda['cliente'],
                    'R$ ' . number_format($venda['valor'], 2, ',', '.'),
                    'R$ ' . number_format($venda['lucro'], 2, ',', '.'),
                    $venda['data_venda'],
                    number_format($venda['margem'], 2, ',', '.') . '%'
                ];
            }
            break;

        case 'ranking':
            $dados_brutos = buscarDadosRanking($pdo);
            $cabecalhos = ['Posição', 'Cliente', 'CPF', 'Qtd Vendas', 'Total (R$)', 'Lucro (R$)', 'Status', 'Margem (%)'];
            $titulo = 'Ranking de Clientes - Sistema de Gestão';
            $filename = "ranking_clientes_" . date('Y-m-d_H-i') . ".{$formato}";

            $dados_processados = [];
            $posicao = 1;
            foreach ($dados_brutos as $cliente) {
                $dados_processados[] = [
                    $posicao++ . 'º',
                    $cliente['nome'],
                    $cliente['cpf'],
                    $cliente['quantidade_vendas'],
                    'R$ ' . number_format($cliente['total_vendas'], 2, ',', '.'),
                    'R$ ' . number_format($cliente['total_lucro'], 2, ',', '.'),
                    $cliente['status'],
                    number_format($cliente['margem_percentual'], 2, ',', '.') . '%'
                ];
            }
            break;

        case 'clientes':
        default:
            $dados_brutos = buscarDadosClientes($pdo);
            $cabecalhos = ['ID', 'Nome', 'CPF', 'Telefone', 'Email', 'Data Cadastro', 'Compras', 'Total (R$)', 'Lucro (R$)', 'Status'];
            $titulo = 'Relatório de Clientes - Sistema de Gestão';
            $filename = "relatorio_clientes_" . date('Y-m-d_H-i') . ".{$formato}";

            $dados_processados = [];
            foreach ($dados_brutos as $cliente) {
                $dados_processados[] = [
                    $cliente['id'],
                    $cliente['nome'],
                    $cliente['cpf'],
                    $cliente['telefone'],
                    $cliente['email'] ?: 'N/A',
                    $cliente['data_cadastro'],
                    $cliente['total_compras'],
                    'R$ ' . number_format($cliente['total_vendas'], 2, ',', '.'),
                    'R$ ' . number_format($cliente['total_lucro'], 2, ',', '.'),
                    $cliente['status']
                ];
            }
            break;
    }

    // ESCOLHER FORMATO CORRETO
    switch ($formato) {
        case 'csv':
            exportarCSV($dados_processados, $cabecalhos, $filename, $titulo);
            break;
        case 'excel':
            exportarExcel($dados_processados, $cabecalhos, $filename, $titulo);
            break;
        case 'pdf':
        default:
            exportarPDF($dados_processados, $cabecalhos, $filename, $titulo);
            break;
    }
} catch (PDOException $e) {
    $_SESSION['erro'] = "Erro ao exportar dados: " . $e->getMessage();
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit();
} catch (Exception $e) {
    $_SESSION['erro'] = "Erro no sistema de exportação: " . $e->getMessage();
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit();
}
