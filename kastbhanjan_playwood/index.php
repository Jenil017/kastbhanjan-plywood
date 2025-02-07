<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php');
    exit;
}

$dealInfo = "";
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
$productQuery = "SELECT name FROM products"; // Adjust as necessary
$productResult = $conn->query($productQuery);
if ($productResult) {
    while ($row = $productResult->fetch_assoc()) {
        $products[] = $row['name'];
    }
}

// Fetch clients from the database
$clients = [];
$clientQuery = "SELECT * FROM clients WHERE is_deleted = 0"; // Only active clients
$clientResult = $conn->query($clientQuery);
if ($clientResult) {
    while ($row = $clientResult->fetch_assoc()) {
        $clients[] = $row; // Store each active client
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submit_deal'])) {
        $product = $_POST['product'];
        $client = $_POST['client'];
        $quantity = $_POST['quantity'];
        $price = $_POST['price'];
        $total = $quantity * $price;

        // Prepare statement
        $stmt = $conn->prepare("INSERT INTO orders (product, client, quantity, price, total, order_date) VALUES (?, ?, ?, ?, ?, NOW())");
        
        if ($stmt) {
            $stmt->bind_param("ssids", $product, $client, $quantity, $price, $total);
            if ($stmt->execute()) {
                $successMessage = "Order placed successfully!";
                $dealInfo = "Product: $product<br>Client: $client<br>Quantity: $quantity<br>Price: $price<br>Total: $total";
            } else {
                $errorMessage = "Error placing order: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $errorMessage = "Prepare failed: " . $conn->error;
        }
    }
}

// Fetch last 5 orders from the database, ordered by date descending
$orderQuery = "SELECT * FROM orders ORDER BY order_date DESC LIMIT 5";
$orderResult = $conn->query($orderQuery);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/admin.css">
    <title>Admin Dashboard</title>
    <style>
        .deal-info {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Kastbhanjan Playwood Admin</h1>
        </header>
        
        <main>
            <h2>Select Product and Client for Deal</h2>
            <form method="POST" action="">
                <label for="product">Product:</label>
                <select name="product" id="product" required>
                    <option value="">Select Product</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo htmlspecialchars($product); ?>"><?php echo htmlspecialchars($product); ?></option>
                    <?php endforeach; ?>
                </select>
                
                <label for="client">Client:</label>
                <select name="client" id="client" required>
                    <option value="">Select Client</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?php echo htmlspecialchars($client['name']); ?>">
                            <?php echo htmlspecialchars($client['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="quantity">Quantity:</label>
                <input type="number" name="quantity" id="quantity" required min="1">

                <label for="price">Price:</label>
                <input type="number" name="price" id="price" required step="0.01">

                <input type="submit" name="submit_deal" value="Submit Deal">
            </form>

            <?php if ($successMessage): ?>
                <div class="success"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="error"><?php echo $errorMessage; ?></div>
            <?php endif; ?>

            <?php if ($dealInfo): ?>
                <div class="deal-info">
                    <h3>Latest Deal Information</h3>
                    <p><?php echo $dealInfo; ?></p>
                </div>
            <?php endif; ?>

            <h2>Historical Orders</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Client</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>Order Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orderResult && $orderResult->num_rows > 0): ?>
                        <?php while ($row = $orderResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['product']); ?></td>
                                <td><?php echo htmlspecialchars($row['client']); ?></td>
                                <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($row['price']); ?></td>
                                <td><?php echo htmlspecialchars($row['total']); ?></td>
                                <td>
                                    <?php 
                                    if ($row['order_date'] == '0000-00-00 00:00:00' || $row['order_date'] === null) {
                                        echo 'N/A';
                                    } else {
                                        echo htmlspecialchars(date('d-m-Y H:i', strtotime($row['order_date'])));
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="admin-buttons">
                <a href="add_client.php" class="button">Add Client</a>
                <a href="add_product.php" class="button">Add Product</a>
            </div>
        </main>

        <footer>
            <p>&copy; 2024 Kastbhanjan Playwood</p>
        </footer>
    </div>
</body>
</html>
