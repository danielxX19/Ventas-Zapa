<?php
function getDb(): ?mysqli {
    static $conn = null;
    if ($conn instanceof mysqli) { return $conn; }
    $host = 'localhost'; $user = 'root'; $pass = ''; $dbName = 'zapa';
    $conn = @new mysqli($host, $user, $pass);
    if ($conn->connect_errno) { return null; }
    // crear DB y tabla si no existen
    $conn->query('CREATE DATABASE IF NOT EXISTS `'.$dbName.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $conn->select_db($dbName);
    $conn->query('CREATE TABLE IF NOT EXISTS products (
        id VARCHAR(64) PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        brand VARCHAR(100) NOT NULL,
        category VARCHAR(100) NOT NULL,
        price_soles DECIMAL(10,2) NOT NULL DEFAULT 0,
        stock INT NOT NULL DEFAULT 0,
        status ENUM("disponible","agotado") NOT NULL DEFAULT "disponible",
        image TEXT
    )');
    // seed si vacío
    $res = $conn->query('SELECT COUNT(*) AS c FROM products');
    if ($res && ($row = $res->fetch_assoc()) && intval($row['c']) === 0) {
        $path = __DIR__ . '/../data/products.json';
        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true) ?: [];
            $stmt = $conn->prepare('INSERT INTO products (id,name,brand,category,price_soles,stock,status,image) VALUES (?,?,?,?,?,?,?,?)');
            foreach ($data as $p) {
                $stmt->bind_param('ssssdiis',$p['id'],$p['name'],$p['brand'],$p['category'],$p['price_soles'],$p['stock'],$p['status'],$p['image']);
                $stmt->execute();
            }
            $stmt->close();
        }
    }
    return $conn;
}

function dbGetProducts(): array {
    $db = getDb(); if (!$db) return [];
    $res = $db->query('SELECT * FROM products');
    $items = [];
    while ($row = $res->fetch_assoc()) { $items[] = $row; }
    return $items;
}

function dbFindProductById($id): ?array {
    $db = getDb(); if (!$db) return null;
    $stmt = $db->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function dbUpdateProductFields($id, array $fields): void {
    $db = getDb(); if (!$db) return;
    $current = dbFindProductById($id); if (!$current) return;
    $data = array_merge($current, array_filter($fields, fn($v) => $v !== null && $v !== ''));
    $stmt = $db->prepare('UPDATE products SET name=?, brand=?, category=?, price_soles=?, stock=?, status=?, image=? WHERE id=?');
    $stmt->bind_param('ssssdiss', $data['name'],$data['brand'],$data['category'],$data['price_soles'],$data['stock'],$data['status'],$data['image'],$id);
    $stmt->execute();
    $stmt->close();
}

function dbCreateProduct(array $p): void {
    $db = getDb(); if (!$db) return;
    $stmt = $db->prepare('INSERT INTO products (id,name,brand,category,price_soles,stock,status,image) VALUES (?,?,?,?,?,?,?,?)');
    $stmt->bind_param('ssssdiis', $p['id'],$p['name'],$p['brand'],$p['category'],$p['price_soles'],$p['stock'],$p['status'],$p['image']);
    $stmt->execute();
    $stmt->close();
}

function dbDeleteProductById($id): void {
    $db = getDb(); if (!$db) return;
    $stmt = $db->prepare('DELETE FROM products WHERE id = ?');
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $stmt->close();
}

function dbDecrementStockForCartItem($id, $qty): void {
    $db = getDb(); if (!$db) return;
    $prod = dbFindProductById($id); if (!$prod) return;
    $newStock = max(0, intval($prod['stock']) - intval($qty));
    $newStatus = $newStock === 0 ? 'agotado' : $prod['status'];
    $stmt = $db->prepare('UPDATE products SET stock=?, status=? WHERE id=?');
    $stmt->bind_param('iss', $newStock, $newStatus, $id);
    $stmt->execute();
    $stmt->close();
}