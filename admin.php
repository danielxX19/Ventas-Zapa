<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/utils.php';
requireAdmin();

$message = null;
// manejo logout
if (isset($_GET['logout'])) { logoutAdmin(); header('Location: admin_login.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $products = getProducts();
    if ($action === 'update') {
        $id = $_POST['id'] ?? null;
        $fields = [
            'name' => trim($_POST['name'] ?? ''),
            'brand' => trim($_POST['brand'] ?? ''),
            'category' => trim($_POST['category'] ?? ''),
            'price_soles' => floatval($_POST['price_soles'] ?? 0),
            'stock' => intval($_POST['stock'] ?? 0),
            'status' => $_POST['status'] ?? 'disponible',
            'image' => trim($_POST['image'] ?? '')
        ];
        // subida de imagen
        if (!empty($_FILES['image_file']['name'])) {
            @mkdir(__DIR__ . '/assets/img', 0777, true);
            $safeName = preg_replace('/[^a-zA-Z0-9._-]/','_', basename($_FILES['image_file']['name']));
            $target = __DIR__ . '/assets/img/' . uniqid('img_') . '_' . $safeName;
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $target)) {
                $fields['image'] = 'assets/img/' . basename($target);
            }
        }
        updateProductFields($id, $fields);
        $message = 'Producto actualizado';
    } elseif ($action === 'create') {
        $new = [
            'id' => uniqid('p_'),
            'name' => trim($_POST['name'] ?? 'Nuevo producto'),
            'brand' => trim($_POST['brand'] ?? 'THN'),
            'category' => trim($_POST['category'] ?? 'Casual'),
            'price_soles' => floatval($_POST['price_soles'] ?? 0),
            'stock' => intval($_POST['stock'] ?? 0),
            'status' => $_POST['status'] ?? 'disponible',
            'image' => trim($_POST['image'] ?? '')
        ];
        if (!empty($_FILES['image_file']['name'])) {
            @mkdir(__DIR__ . '/assets/img', 0777, true);
            $safeName = preg_replace('/[^a-zA-Z0-9._-]/','_', basename($_FILES['image_file']['name']));
            $target = __DIR__ . '/assets/img/' . uniqid('img_') . '_' . $safeName;
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $target)) {
                $new['image'] = 'assets/img/' . basename($target);
            }
        }
        createProduct($new);
        $message = 'Producto creado';
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? null;
        deleteProductById($id);
        $message = 'Producto eliminado';
    }
}

$products = getProducts();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin | THN Zapas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-thn">
    <div class="container">
      <a class="navbar-brand fw-bold" href="index.php">THN Zapas</a>
      <span class="navbar-text">Panel Admin</span>
      <div class="ms-auto"><a href="admin.php?logout=1" class="btn btn-outline-light btn-sm">Salir</a></div>
    </div>
  </nav>

  <main class="container my-4">
    <h1 class="h4 mb-3">Gestionar productos</h1>
    <?php if ($message): ?>
      <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="card mb-4">
      <div class="card-body">
        <h2 class="h6">Crear nuevo producto</h2>
        <form action="admin.php" method="post" enctype="multipart/form-data" class="row g-2">
          <input type="hidden" name="action" value="create">
          <div class="col-md-3"><input name="name" class="form-control" placeholder="Nombre" required></div>
          <div class="col-md-2"><input name="brand" class="form-control" placeholder="Marca" value="THN" required></div>
          <div class="col-md-2"><input name="category" class="form-control" placeholder="Categoría" value="Casual" required></div>
          <div class="col-md-2"><input name="price_soles" type="number" step="0.01" class="form-control" placeholder="Precio (S/)" required></div>
          <div class="col-md-1"><input name="stock" type="number" class="form-control" placeholder="Stock" required></div>
          <div class="col-md-2">
            <select name="status" class="form-select">
              <option value="disponible">Disponible</option>
              <option value="agotado">Agotado</option>
            </select>
          </div>
          <div class="col-12"><input name="image" class="form-control" placeholder="URL de imagen (opcional)"></div>
          <div class="col-12"><input type="file" name="image_file" class="form-control" accept="image/*"></div>
          <div class="col-12"><button class="btn btn-thn" type="submit">Crear</button></div>
        </form>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th>Imagen</th><th>Nombre</th><th>Marca</th><th>Categoría</th><th>Precio (S/)</th><th>Stock</th><th>Estado</th><th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p): ?>
            <tr>
              <td><img src="<?= htmlspecialchars($p['image']) ?>" width="50" height="50" style="object-fit:cover" alt="" loading="lazy" onerror="this.onerror=null;this.src='assets/placeholder.svg'"></td>
              <td colspan="7">
                <form action="admin.php" method="post" enctype="multipart/form-data" class="row g-2">
                  <input type="hidden" name="action" value="update">
                  <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
                  <div class="col-md-3"><input name="name" class="form-control" value="<?= htmlspecialchars($p['name']) ?>"></div>
                  <div class="col-md-2"><input name="brand" class="form-control" value="<?= htmlspecialchars($p['brand']) ?>"></div>
                  <div class="col-md-2"><input name="category" class="form-control" value="<?= htmlspecialchars($p['category']) ?>"></div>
                  <div class="col-md-2"><input name="price_soles" type="number" step="0.01" class="form-control" value="<?= htmlspecialchars($p['price_soles']) ?>"></div>
                  <div class="col-md-1"><input name="stock" type="number" class="form-control" value="<?= htmlspecialchars($p['stock']) ?>"></div>
                  <div class="col-md-2">
                    <select name="status" class="form-select">
                      <option value="disponible" <?= $p['status']==='disponible'?'selected':'' ?>>Disponible</option>
                      <option value="agotado" <?= $p['status']==='agotado'?'selected':'' ?>>Agotado</option>
                    </select>
                  </div>
                  <div class="col-12"><input name="image" class="form-control" value="<?= htmlspecialchars($p['image']) ?>" placeholder="URL de imagen (opcional)"></div>
                  <div class="col-12"><input type="file" name="image_file" class="form-control" accept="image/*"></div>
                  <div class="col-md-3">
                    <button class="btn btn-sm btn-thn" type="submit">Guardar</button>
                    <form action="admin.php" method="post" class="d-inline">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
                      <button class="btn btn-sm btn-outline-danger" type="submit">Eliminar</button>
                    </form>
                  </div>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>