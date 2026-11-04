<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include "nav.php"; ?>

    <div class="container">
        <p class="subtitle">Track goods from the supplier to the moment they leave the warehouse.</p>

        <div class="cards">
            <a class="card main" href="items.php">
                <h3>Items</h3>
                <p>Create and track inventory items.</p>
            </a>
            <a class="card" href="categories.php">
                <h3>Categories</h3>
                <p>Specify groups of inventory items.</p>
            </a>
            <a class="card" href="statuses.php">
                <h3>Status</h3>
                <p>Manage an inventory status.</p>
            </a>
            <a class="card" href="types.php">
                <h3>Types</h3>
                <p>Manage inventory types.</p>
            </a>
            <a class="card" href="suppliers.php">
                <h3>Suppliers</h3>
                <p>Register and manage suppliers.</p>
            </a>
        </div>
    </div>
</body>
</html>