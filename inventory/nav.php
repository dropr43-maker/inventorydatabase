<?php
$current = basename($_SERVER["PHP_SELF"]);

$links = [
    "index.php"      => "Home",
    "items.php"      => "Items",
    "categories.php" => "Categories",
    "statuses.php"   => "Status",
    "types.php"      => "Types",
    "suppliers.php"  => "Suppliers"
];
?>
<div class="navbar">
    <h1>Inventory Management</h1>
    <div>
        <?php foreach ($links as $file => $label) {
            $class = ($current == $file) ? "active" : "";
            echo "<a href='$file' class='$class'>$label</a>";
        } ?>
    </div>
</div>