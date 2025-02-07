<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php');
    exit;
}

$successMessage = "";
$errorMessage = "";

// Database connection
$host = 'localhost';
$db = 'playwood_db';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch products from the database
$products = [];
$productQuery = "SELECT * FROM products"; // Fetch all products
$productResult = $conn->query($productQuery);
if ($productResult) {
    while ($row = $productResult->fetch_assoc()) {
        $products[] = $row; // Store each product
    }
}

// Handle product addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_name'])) {
    $productName = trim($_POST['product_name']);
    if (empty($productName)) {
        $errorMessage = "Product name cannot be empty.";
    } else {
        // Prepare statement to insert the product
        $stmt = $conn->prepare("INSERT INTO products (name) VALUES (?)");
        if ($stmt) {
            $stmt->bind_param("s", $productName);
            if ($stmt->execute()) {
                $successMessage = "Product added successfully!";
            } else {
                $errorMessage = "Error adding product: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $errorMessage = "Prepare failed: " . $conn->error;
        }
    }
}

// Handle product deletion
if (isset($_POST['delete_product'])) {
    $productId = $_POST['product_id'];
    $deleteStmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    if ($deleteStmt) {
        $deleteStmt->bind_param("i", $productId);
        if ($deleteStmt->execute()) {
            $successMessage = "Product deleted successfully!";
        } else {
            $errorMessage = "Error deleting product: " . $deleteStmt->error;
        }
        $deleteStmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/add-product.css">
    <title>Add Product</title>
</head>
<body>
    <div class="container">
        <header>
            <h1>Add Product</h1>
        </header>
        
        <main>
            <form method="POST" action="">
                <label for="product_name">Product Name:</label>
                <input type="text" name="product_name" id="product_name" required value="<?php echo isset($_POST['product_name']) ? htmlspecialchars($_POST['product_name']) : ''; ?>">

                <input type="submit" value="Add Product"><br>
                <a href="admin.php" class="button back-button">Back</a>
            </form>

            <?php if ($successMessage): ?>
                <div class="success"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="error"><?php echo $errorMessage; ?></div>
            <?php endif; ?>

            <h2>Products</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td>
                                <form method="POST" action="">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="submit" name="delete_product" value="Delete">
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
