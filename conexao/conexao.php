<?php
//definição das constantes de conexão com o banco de dados
define('DB_HOST','Localhost'); //endereço do servidor do banco de dados
define('DB_NAME','ranking_clientes'); //Nome do banco de dados
define('DB_USER','root'); //Usuário do banco de dados
define('DB_PASS',''); //senha do banco de dados (em branco no XAMPP padrão)

try{
    //string de conexão (DSN - data source name)
    $dsn = "mysql:host=". DB_HOST .";dbname=". DB_NAME .";charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, //Lança exceções em caso de erros
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //Retorna os resultados como arrays associativos
        PDO::ATTR_EMULATE_PREPARES => false, //Usa prepared  statments nativos do banco de dados
    ];

//criação de instancias do PDO

$pdo = new PDO($dsn, DB_USER,DB_PASS,$options);
}catch (PDOEXception $e) {

    die("Erro na conexão do banco de dados: " . $e->getMessage());
}
?>