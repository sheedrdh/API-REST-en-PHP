<?php
declare(strict_types=1);

class ProductController {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /** GET /products   → liste de tous les produits */
    public function list(): void {
        $stmt = $this->db->query('SELECT * FROM products');
        $products = $stmt->fetchAll();
        echo json_encode($products);
    }

    /** GET /products/{id}   → un produit */
    public function get(int $id): void {
        $stmt = $this->db->prepare('SELECT * FROM products WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch();

        if ($product) {
            echo json_encode($product);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Produit non trouvé']);
        }
    }

    /** POST /products   → création */
    public function create(): void {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validation basique (exemple du guide)【5†L242-L252】
        $name = filter_var($data['name'] ?? '', FILTER_SANITIZE_STRING);
        $price = filter_var($data['price'] ?? 0, FILTER_VALIDATE_FLOAT);
        $desc = $data['description'] ?? '';

        if (!$name || $price === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Paramètres invalides']);
            return;
        }

        $sql = "INSERT INTO products (name, price, description) 
                VALUES (:name, :price, :desc)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'price'=> $price,
            'desc'=> $desc
        ]);

        http_response_code(201);
        echo json_encode(['message' => 'Produit créé', 'id' => $this->db->lastInsertId()]);
    }

    /** PUT /products/{id}   → mise à jour */
    public function update(int $id): void {
        $data = json_decode(file_get_contents('php://input'), true);

        $name = filter_var($data['name'] ?? '', FILTER_SANITIZE_STRING);
        $price = filter_var($data['price'] ?? 0, FILTER_VALIDATE_FLOAT);
        $desc = $data['description'] ?? '';

        if (!$name || $price === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Paramètres invalides']);
            return;
        }

        $sql = "UPDATE products SET name=:name, price=:price, description=:desc WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'price'=> $price,
            'desc'=> $desc,
            'id'  => $id
        ]);

        if ($stmt->rowCount()) {
            echo json_encode(['message' => 'Produit mis à jour']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Produit non trouvé']);
        }
    }

    /** DELETE /products/{id}   → suppression */
    public function delete(int $id): void {
        $stmt = $this->db->prepare('DELETE FROM products WHERE id = :id');
        $stmt->execute(['id' => $id]);

        if ($stmt->rowCount()) {
            echo json_encode(['message' => 'Produit supprimé']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Produit non trouvé']);
        }
    }
}
