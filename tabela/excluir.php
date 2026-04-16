<!-- excluir.php -->
<?php
require_once '../conexao/conexao.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "DELETE FROM clientes WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
}
header("Location: index.php");
exit;
