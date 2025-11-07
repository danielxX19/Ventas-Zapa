<?php
session_start();
require_once __DIR__ . '/lib/utils.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = []; // each item: ['id' => string|int, 'qty' => int]
}

function addToCart($id, $qty = 1) {
    $products = getProducts();
    $product = findProductById($products, $id);
    if (!$product) { return 'Producto no encontrado.'; }
    if ($product['status'] === 'agotado' || intval($product['stock']) <= 0) { return 'Producto agotado.'; }

    // Current qty in cart
    $currentQty = 0;
    foreach ($_SESSION['cart'] as $item) {
        if ($item['id'] == $id) { $currentQty = $item['qty']; break; }
    }

    if ($currentQty + $qty > intval($product['stock'])) { return 'Stock insuficiente.'; }

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $id) { $item['qty'] += $qty; $found = true; break; }
    }
    unset($item);
    if (!$found) { $_SESSION['cart'][] = ['id' => $id, 'qty' => $qty]; }
    return null;
}

function updateCartQty($id, $qty) {
    $qty = max(1, intval($qty));
    $products = getProducts();
    $product = findProductById($products, $id);
    if (!$product) { return 'Producto no encontrado.'; }
    if ($qty > intval($product['stock'])) { return 'Stock insuficiente.'; }

    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $id) { $item['qty'] = $qty; break; }
    }
    unset($item);
    return null;
}

function removeFromCart($id) {
    $_SESSION['cart'] = array_values(array_filter($_SESSION['cart'], fn($i) => $i['id'] != $id));
}

function finalizePurchase() {
    foreach ($_SESSION['cart'] as $item) {
        decrementStockForCartItem($item['id'], $item['qty']);
    }
    $_SESSION['cart'] = [];
}

$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    $id = $_POST['id'] ?? null;

    if ($action === 'add' && $id) {
        $message = addToCart($id, 1);
        if (!$message) { header('Location: cart.php'); exit; }
    } elseif ($action === 'update' && $id) {
        $qty = intval($_POST['qty'] ?? 1);
        $message = updateCartQty($id, $qty);
    } elseif ($action === 'remove' && $id) {
        removeFromCart($id);
        header('Location: cart.php'); exit;
    } elseif ($action === 'finalize') {
        finalizePurchase();
        $message = 'Compra realizada. ¡Gracias! El stock fue actualizado.';
    }
}

$products = getProducts();
function cartTotal($products) {
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $p = findProductById($products, $item['id']);
        if ($p) { $total += floatval($p['price_soles']) * $item['qty']; }
    }
    return $total;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Carrito | THN Zapas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-thn">
    <div class="container">
      <a class="navbar-brand fw-bold" href="index.php">THN Zapas</a>
    </div>
  </nav>

  <main class="container my-4">
    <h1 class="h4 mb-3">Tu carrito</h1>
    <?php if ($message): ?>
      <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (empty($_SESSION['cart'])): ?>
      <p>No tienes productos en el carrito.</p>
      <a class="btn btn-thn" href="index.php">Volver al catálogo</a>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Producto</th>
              <th>Precio</th>
              <th>Cantidad</th>
              <th>Subtotal</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($_SESSION['cart'] as $item): $p = findProductById($products, $item['id']); if (!$p) continue; ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <img src="<?= htmlspecialchars($p['image']) ?>" width="50" height="50" style="object-fit:cover" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy" onerror="this.onerror=null;this.src='assets/placeholder.svg'">
                    <div>
                      <div class="fw-semibold"><?= htmlspecialchars($p['name']) ?></div>
                      <div class="text-muted small">Stock: <?= intval($p['stock']) ?></div>
                    </div>
                  </div>
                </td>
                <td>S/ <?= number_format($p['price_soles'], 2) ?></td>
                <td>
                  <form action="cart.php" method="post" class="d-flex align-items-center gap-2">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
                    <input type="number" name="qty" min="1" max="<?= intval($p['stock']) ?>" value="<?= intval($item['qty']) ?>" class="form-control form-control-sm" style="width:90px" />
                    <button class="btn btn-sm btn-outline-secondary" type="submit">Actualizar</button>
                  </form>
                </td>
                <td>S/ <?= number_format(floatval($p['price_soles']) * $item['qty'], 2) ?></td>
                <td>
                  <form action="cart.php" method="post">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
                    <button class="btn btn-sm btn-outline-danger" type="submit">Eliminar</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3" class="text-end fw-bold">Total</td>
              <td colspan="2" class="fw-bold">S/ <?= number_format(cartTotal($products), 2) ?></td>
            </tr>
          </tfoot>
        </table>
      </div>
      <form action="cart.php" method="post" class="d-flex justify-content-end gap-2">
        <a class="btn btn-outline-secondary" href="index.php">Seguir comprando</a>
        <input type="hidden" name="action" value="finalize">
        <button class="btn btn-thn" type="submit">Finalizar compra</button>
      </form>
    <?php endif; ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>