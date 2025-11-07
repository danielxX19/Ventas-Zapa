<?php
session_start();
require_once __DIR__ . '/lib/utils.php';

$products = getProducts();
$categories = array_values(array_unique(array_map(fn($p) => $p['category'], $products)));
$brands = array_values(array_unique(array_map(fn($p) => $p['brand'], $products)));

// Cart count
$cartCount = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) { $cartCount += $item['qty']; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>THN Zapas | Catálogo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-thn">
    <div class="container">
      <a class="navbar-brand fw-bold" href="#">THN Zapas</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="nav">
        <ul class="navbar-nav ms-auto align-items-center">
          <li class="nav-item me-3"><a class="nav-link" href="cart.php">Carrito <span class="badge bg-light text-dark"><?= $cartCount ?></span></a></li>
          <li class="nav-item"><a class="btn btn-outline-light" href="admin.php?key=thn-admin">Admin</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="bg-thn-soft border-bottom">
    <div class="container py-2 d-flex flex-wrap gap-3 justify-content-between align-items-center">
      <span class="small text-dark">Envíos rápidos y cambios fáciles • Soporte 24/7</span>
      <div class="d-flex align-items-center gap-2">
        <span class="small">Síguenos:</span>
        <a class="small text-decoration-none" href="#">Instagram</a>
        <a class="small text-decoration-none" href="#">Facebook</a>
        <a class="small text-decoration-none" href="#">WhatsApp</a>
      </div>
    </div>
  </div>

  <header class="bg-thn-soft text-dark py-4">
    <div class="container">
      <h1 class="h3 mb-1">Catálogo de Zapatillas</h1>
      <p class="mb-3">Filtra por categorías, marcas, texto y precio. Precios en soles (S/).</p>
      <div class="row g-2 align-items-end">
        <div class="col-md-4">
          <input id="searchInput" class="form-control" placeholder="Buscar por nombre o marca...">
        </div>
        <div class="col-md-3">
          <input id="minPrice" type="number" step="0.01" class="form-control" placeholder="Precio mínimo (S/)">
        </div>
        <div class="col-md-3">
          <input id="maxPrice" type="number" step="0.01" class="form-control" placeholder="Precio máximo (S/)">
        </div>
        <div class="col-md-2">
          <button id="applyPrice" class="btn btn-thn w-100">Aplicar</button>
        </div>
        <div class="col-12 col-md-4 mt-2">
          <select id="sortSelect" class="form-select">
            <option value="default">Ordenar: Predeterminado</option>
            <option value="price_asc">Precio: Menor a mayor</option>
            <option value="price_desc">Precio: Mayor a menor</option>
            <option value="name_asc">Nombre: A → Z</option>
            <option value="name_desc">Nombre: Z → A</option>
            <option value="brand_asc">Marca: A → Z</option>
            <option value="brand_desc">Marca: Z → A</option>
          </select>
        </div>
      </div>
    </div>
  </header>

  <main class="container my-4">
    <div class="row g-4">
      <aside class="col-lg-3">
        <div class="d-lg-none mb-2">
          <button class="btn btn-outline-secondary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse" aria-expanded="false" aria-controls="filtersCollapse">Mostrar filtros</button>
        </div>
        <div id="filtersCollapse" class="collapse d-lg-block">
          <div class="card shadow-sm">
            <div class="card-body">
              <h2 class="h6">Categorías</h2>
              <?php foreach ($categories as $cat): ?>
                <div class="form-check">
                  <input class="form-check-input filter-category" type="checkbox" value="<?= htmlspecialchars($cat) ?>" id="cat-<?= htmlspecialchars($cat) ?>">
                  <label class="form-check-label" for="cat-<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></label>
                </div>
              <?php endforeach; ?>
              <hr>
              <h2 class="h6">Marcas</h2>
              <?php foreach ($brands as $brand): ?>
                <div class="form-check">
                  <input class="form-check-input filter-brand" type="checkbox" value="<?= htmlspecialchars($brand) ?>" id="brand-<?= htmlspecialchars($brand) ?>">
                  <label class="form-check-label" for="brand-<?= htmlspecialchars($brand) ?>"><?= htmlspecialchars($brand) ?></label>
                </div>
              <?php endforeach; ?>
              <hr>
              <button id="clearFilters" class="btn btn-sm btn-outline-secondary w-100">Limpiar filtros</button>
            </div>
          </div>
        </div>
      </aside>

      <section class="col-lg-9">
        <div class="row" id="productGrid">
          <?php foreach ($products as $p): ?>
            <div class="col-md-6 col-lg-4 product-card" data-category="<?= htmlspecialchars($p['category']) ?>" data-brand="<?= htmlspecialchars($p['brand']) ?>" data-status="<?= htmlspecialchars($p['status']) ?>" data-price="<?= htmlspecialchars($p['price_soles']) ?>" data-name="<?= htmlspecialchars($p['name']) ?>" data-image="<?= htmlspecialchars($p['image']) ?>" data-stock="<?= intval($p['stock']) ?>" data-id="<?= htmlspecialchars($p['id']) ?>">
              <div class="card h-100 shadow-sm">
                <div class="position-relative">
                  <img src="<?= htmlspecialchars($p['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy" onerror="this.onerror=null;this.src='assets/placeholder.svg'">
                  <?php if ($p['status'] === 'agotado' || intval($p['stock']) <= 0): ?>
                    <span class="badge bg-danger position-absolute top-0 start-0 m-2">Agotado</span>
                  <?php endif; ?>
                </div>
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title mb-1"><?= htmlspecialchars($p['name']) ?></h5>
                  <p class="text-muted mb-1">Marca: <?= htmlspecialchars($p['brand']) ?> • Cat: <?= htmlspecialchars($p['category']) ?></p>
                  <p class="fw-bold mb-2">S/ <?= number_format($p['price_soles'], 2) ?></p>
                  <p class="small mb-3">Stock: <?= intval($p['stock']) ?></p>
                  <div class="mt-auto d-flex gap-2">
                    <button class="btn btn-outline-secondary w-50 btn-quickview" type="button">Vista rápida</button>
                    <form action="cart.php" method="post" class="w-50">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
                    <button class="btn btn-thn w-100" type="submit" <?= ($p['status'] === 'agotado' || intval($p['stock']) <= 0) ? 'disabled' : '' ?>>Agregar</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="d-grid mt-3">
          <button id="loadMore" class="btn btn-outline-secondary">Cargar más</button>
        </div>
      </section>
    </div>
  </main>

  <footer class="py-4 border-top">
    <div class="container d-flex justify-content-between align-items-center">
      <span class="text-muted">© <?= date('Y') ?> THN Zapas</span>
      <div class="d-flex align-items-center gap-3">
        <form action="newsletter.php" method="post" class="d-flex gap-2">
          <input type="email" name="email" class="form-control form-control-sm" placeholder="Tu email" required>
          <button class="btn btn-sm btn-thn" type="submit">Suscribirme</button>
        </form>
        <a class="text-decoration-none" href="admin_login.php">Admin</a>
      </div>
    </div>
  </footer>

  <!-- Quick View Modal -->
  <div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="qvTitle">Producto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <img id="qvImage" src="assets/placeholder.svg" class="img-fluid rounded" alt="Vista rápida" onerror="this.onerror=null;this.src='assets/placeholder.svg'">
            </div>
            <div class="col-12">
              <p class="mb-1 text-muted" id="qvMeta">Marca • Categoría</p>
              <p class="fw-bold mb-2" id="qvPrice">S/ 0.00</p>
              <p class="small" id="qvStock">Stock: 0</p>
              <form id="qvForm" action="cart.php" method="post" class="d-flex gap-2">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="id" id="qvId" value="">
                <button class="btn btn-thn" type="submit" id="qvAddBtn">Agregar al carrito</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/app.js"></script>
</body>
</html>