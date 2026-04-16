<?php
// Conexão com o banco de dados
require_once '../conexao/conexao.php'; // ajuste o caminho se necessário
function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);

    if (strlen($cpf) != 11) {
        return false;
    }

    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }

    for ($t = 9; $t < 11; $t++) {
        $soma = 0;

        for ($c = 0; $c < $t; $c++) {
            $soma += $cpf[$c] * (($t + 1) - $c);
        }

        $digito = ((10 * $soma) % 11) % 10;
        
        if ($cpf[$t] != $digito) {
            return false;
        }
    }

    return true;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Receber e limpar os dados do formulário
$nome = trim($_POST['nome']);
$cpf  = preg_replace('/[^0-9]/', '', $_POST['cpf']); // corrigido
$telefone = trim($_POST['telefone']);
$email = trim($_POST['email']);
$endereco = trim($_POST['endereco']);

if (!validarCPF($cpf)) {
    echo "<script>
    alert('CPF inválido!');
        window.location.href = 'index(1).php';
    </script>";
    exit;
}

    try {
        
        $check = $pdo->prepare("SELECT id FROM clientes WHERE cpf = ?");
        $check->execute([$cpf]);

        if ($check->rowCount() > 0) {
            echo "<script>
                alert('CPF já cadastrado!');
                window.location.href = 'index(1).php';
            </script>";
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO clientes (nome, cpf, telefone, email, endereco, data_cadastro)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$nome, $cpf, $telefone, $email, $endereco]);

        echo "<script>
            alert('Cliente cadastrado com sucesso!');
            window.location.href = 'CadastraIndex.php';
        </script>";

    } catch (PDOException $e) {
        echo "Erro ao cadastrar cliente: " . $e->getMessage();
    }
} else {
    echo "Método inválido.";
}
?>