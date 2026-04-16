<?php
require_once '../conexao/conexao.php';
require_once '../crud_filmes/header.php';

if (!isset($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$id = $_GET['id'];
$sql = "SELECT * FROM clientes WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id        = $_POST['id'];
    $nome      = trim($_POST['nome']);
    $cpf       = trim($_POST['cpf']);
    $telefone  = trim($_POST['telefone']);
    $email     = trim($_POST['email']);
    $endereco  = trim($_POST['endereco']);

    try {
        $sql = "UPDATE clientes 
                SET nome = ?, cpf = ?, telefone = ?, email = ?, endereco = ? 
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$nome, $cpf, $telefone, $email, $endereco, $id])) {
            header("Location: ../index.php");
            exit;
        } else {
            $erro = "Erro ao atualizar o cliente.";
        }
    } catch (PDOException $e) {
        $erro = "Erro no banco de dados: " . $e->getMessage();
    }
}
?>

<div class="max-w-3xl mx-auto bg-white shadow-md rounded-xl p-8 mt-10">
  <h1 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-2">Editar Cliente</h1>

  <?php if (isset($erro)): ?>
    <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= htmlspecialchars($erro) ?></div>
  <?php endif; ?>

  <form action="editar.php?id=<?= htmlspecialchars($cliente['id']) ?>" method="POST" class="space-y-5">
    <input type="hidden" name="id" value="<?= htmlspecialchars($cliente['id']) ?>">

    <div>
      <label for="nome" class="block font-semibold text-gray-700">Nome Completo</label>
      <input type="text" id="nome" name="nome"
             class="w-full mt-1 p-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
             value="<?= htmlspecialchars($cliente['nome']) ?>" required>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label for="cpf" class="block font-semibold text-gray-700">CPF</label>
        <input type="text" id="cpf" name="cpf" maxlength="14"
               class="w-full mt-1 p-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
               value="<?= htmlspecialchars($cliente['cpf']) ?>" required>
      </div>

      <div>
        <label for="telefone" class="block font-semibold text-gray-700">Telefone</label>
        <input type="text" id="telefone" name="telefone"
               class="w-full mt-1 p-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
               value="<?= htmlspecialchars($cliente['telefone']) ?>" required>
      </div>
    </div>

    <div>
      <label for="email" class="block font-semibold text-gray-700">E-mail</label>
      <input type="email" id="email" name="email"
             class="w-full mt-1 p-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
             value="<?= htmlspecialchars($cliente['email']) ?>">
    </div>

    <div>
      <label for="endereco" class="block font-semibold text-gray-700">Endereço</label>
      <input type="text" id="endereco" name="endereco"
             class="w-full mt-1 p-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
             value="<?= htmlspecialchars($cliente['endereco']) ?>">
    </div>

    <div class="flex gap-3 justify-end pt-4">
      <a href="../index.php" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded-lg transition">Cancelar</a>
      <button type="submit"
              class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg transition">
        Salvar Alterações
      </button>
    </div>
  </form>
</div>

