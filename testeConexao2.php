<?php
echo"<h2>Teste de Conexão com o Banco de Dados</h2>";

require_once 'conexao/conexao.php';

if(isset($pdo)) {
    echo "<p style='color: green;'>Conexão bem-sucedida!</p>";
} else {
    echo"<p style='color: red;'>Falha na conexão.</p>";
}

//acessar: http://localhost/info20252/CrudFilmes/testeConexao.php