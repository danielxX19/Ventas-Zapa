<?php
require_once __DIR__ . '/lib/auth.php';

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['user'] ?? '');
    $p = trim($_POST['pass'] ?? '');
    if (loginAdmin($u, $p)) {
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Credenciales inválidas';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Acceso Admin | THN Zapas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
  <style>body{background:#f0f4f8}</style>
  </head>
<body>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-5">
        <div class="card shadow-sm">
          <div class="card-body">
            <h1 class="h5 mb-3">Acceso administrador</h1>
            <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <form method="post" class="vstack gap-3">
              <div>
                <label class="form-label">Usuario</label>
                <input name="user" class="form-control" required placeholder="admin">
              </div>
              <div>
                <label class="form-label">Contraseña</label>
                <input name="pass" type="password" class="form-control" required placeholder="thn123">
              </div>
              <button class="btn btn-thn" type="submit">Entrar</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>