<?php
session_start();
$email = trim($_POST['email'] ?? '');
function isValidEmail($e){ return filter_var($e, FILTER_VALIDATE_EMAIL) !== false; }

$msg = 'Suscripción inválida';
if ($email && isValidEmail($email)) {
    $path = __DIR__ . '/data/newsletter.json';
    @mkdir(dirname($path), 0777, true);
    $items = [];
    if (file_exists($path)) { $items = json_decode(file_get_contents($path), true) ?: []; }
    $items[] = ['email' => $email, 'ts' => date('c')];
    file_put_contents($path, json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $msg = '¡Gracias por suscribirte!';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Newsletter | THN Zapas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-body text-center">
            <h1 class="h5 mb-3">Newsletter THN</h1>
            <p><?= htmlspecialchars($msg) ?></p>
            <a class="btn btn-thn" href="index.php">Volver al catálogo</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>