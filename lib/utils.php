<?php
require_once __DIR__ . '/db.php';

function productsPath(): string { return __DIR__ . '/../data/products.json'; }

function getProducts(): array {
    $db = getDb();
    if ($db) { return dbGetProducts(); }
    $path = productsPath();
    if (!file_exists($path)) { return []; }
    $raw = file_get_contents($path);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function saveProducts(array $products): void {
    // Solo para fallback JSON
    $path = productsPath();
    @mkdir(dirname($path), 0777, true);
    file_put_contents($path, json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function findProductById($productsOrId, $id = null): ?array {
    // Soporta llamada findProductById($products, $id) para compatibilidad
    if (is_array($productsOrId) && $id !== null) {
        foreach ($productsOrId as $p) { if ($p['id'] == $id) return $p; }
        return null;
    }
    // Modo DB: findProductById($id)
    return dbFindProductById($productsOrId);
}

function updateProductFields($id, array $fields): void {
    $db = getDb();
    if ($db) { dbUpdateProductFields($id, $fields); return; }
    $products = getProducts();
    foreach ($products as &$p) {
        if ($p['id'] == $id) { $p = array_merge($p, $fields); break; }
    }
    unset($p);
    saveProducts($products);
}

function createProduct(array $p): void {
    $db = getDb();
    if ($db) { dbCreateProduct($p); return; }
    $products = getProducts();
    $products[] = $p;
    saveProducts($products);
}

function deleteProductById($id): void {
    $db = getDb();
    if ($db) { dbDeleteProductById($id); return; }
    $products = array_values(array_filter(getProducts(), fn($p) => $p['id'] != $id));
    saveProducts($products);
}

function decrementStockForCartItem($id, $qty): void {
    $db = getDb();
    if ($db) { dbDecrementStockForCartItem($id, $qty); return; }
    $products = getProducts();
    foreach ($products as &$p) {
        if ($p['id'] == $id) {
            $p['stock'] = max(0, intval($p['stock']) - intval($qty));
            if (intval($p['stock']) === 0) { $p['status'] = 'agotado'; }
            break;
        }
    }
    unset($p);
    saveProducts($products);
}