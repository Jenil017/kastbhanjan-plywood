<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php');
    exit;
}

$successMessage = "";
$errorMessage = "";

// Database connection
$host = 'localhost'; // Change if needed
$db = 'playwood_db'; // Your database name
$user = 'root'; // Your database username
$pass = ''; // Your database password

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission for adding a client
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_client'])) {
    $clientName = $_POST['client_name'];
    $clientNumber = $_POST['client_number'];
    
    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO clients (name, number, is_deleted) VALUES (?, ?, 0)");
    $stmt->bind_param("ss", $clientName, $clientNumber);

    if ($stmt->execute()) {
        $successMessage = "Client added successfully!";
        $_SESSION['clients'][] = ['name' => $clientName, 'number' => $clientNumber]; // Add to session
    } else {
        $errorMessage = "Error adding client: " . $stmt->error;
    }

    $stmt->close();
}

// Handle client deletion
if (isset($_POST['delete_client'])) {
    $clientId = $_POST['client_id'];

    // Prepare and bind for soft delete
    $stmt = $conn->prepare("UPDATE clients SET is_deleted = 1 WHERE id = ?");
    $stmt->bind_param("i", $clientId);

    if ($stmt->execute()) {
        $successMessage = "Client deleted successfully!";
        
        // Optionally remove from session clients array
        // You may choose to keep this for historical purposes
        foreach ($_SESSION['clients'] as $key => $client) {
            if ($client['id'] == $clientId) {
                unset($_SESSION['clients'][$key]);
                break;
            }
        }
    } else {
        $errorMessage = "Error deleting client: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch clients for dropdown (including deleted ones)
$clients = [];
$result = $conn->query("SELECT id, name, number FROM clients WHERE is_deleted = 0");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $clients[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/add-client.css">
    <title>Add Client</title>
</head>
<body>
    <div class="container">
        <header>
            <h1>Add Client</h1>
        </header>
        
        <main>
            <form method="POST" action="">
                <label for="client_name">Client Name:</label>
                <input type="text" name="client_name" id="client_name" required>
                
                <label for="client_number">Client Number:</label>
                <input type="text" name="client_number" id="client_number" required>

                <input type="submit" name="add_client" value="Add Client"><br>
                <a href="admin.php" class="button back-button">Back</a> <!-- Back button -->
            </form>

            <?php if ($successMessage): ?>
                <div class="success"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="error"><?php echo $errorMessage; ?></div>
            <?php endif; ?>

            <h2>Clients</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Number</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($client['name']); ?></td>
                            <td><?php echo htmlspecialchars($client['number']); ?></td>
                            <td>
                                <form method="POST" action="">
                                    <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                                    <input type="submit" name="delete_client" value="Delete">
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
