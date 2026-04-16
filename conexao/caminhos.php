<?php
// teste_estrutura.php - Coloque na mesma pasta do tabela.php

echo "<h3>🔍 Diagnosticando Estrutura de Pastas</h3>";
echo "Script executado em: <strong>" . __DIR__ . "</strong><br><br>";

// Testar diferentes caminhos possíveis
$caminhos = [
    '../config/database.php',
    '../../config/database.php',
    '../../../config/database.php',
    '../config/conexao.php',
    '../../config/conexao.php',
    '../../../config/conexao.php',
    './config/database.php',
    './config/conexao.php'
];

echo "<h4>📁 Testando Caminhos:</h4>";
foreach ($caminhos as $caminho) {
    $caminho_completo = __DIR__ . '/' . $caminho;
    if (file_exists($caminho_completo)) {
        echo "✅ <strong>ENCONTRADO:</strong> $caminho<br>";
        echo "&nbsp;&nbsp;&nbsp;Localização: $caminho_completo<br><br>";
    } else {
        echo "❌ Não encontrado: $caminho<br>";
    }
}

echo "<h4>📂 Conteúdo do Diretório Atual:</h4>";
$arquivos = scandir(__DIR__);
foreach ($arquivos as $arquivo) {
    if ($arquivo != '.' && $arquivo != '..') {
        echo "- $arquivo<br>";
    }
}

echo "<h4>📂 Conteúdo do Diretório Pai:</h4>";
$pai = dirname(__DIR__);
if (is_dir($pai)) {
    $arquivos_pai = scandir($pai);
    foreach ($arquivos_pai as $arquivo) {
        if ($arquivo != '.' && $arquivo != '..') {
            echo "- $arquivo<br>";
        }
    }
}
    