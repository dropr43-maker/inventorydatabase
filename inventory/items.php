<?php
include "database.php";

$message = "";

// messages shown after a redirect
$messages = [
    "created" => "Item created.",
    "updated" => "Item updated.",
    "deleted" => "Item deleted."
];
if (isset($_GET["msg"]) && isset($messages[$_GET["msg"]])) {
    $message = $messages[$_GET["msg"]];
}

// optional dates: an empty box is saved as NULL
function optional($value) {
    return $value === "" ? null : $value;
}

// safe output for the page
function e($value) {
    return htmlspecialchars($value ?? "");
}

// ---------- DELETE ----------
if (isset($_POST["delete"])) {
    $stmt = mysqli_prepare($conn, "DELETE FROM items WHERE item_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $_POST["item_id"]);
    mysqli_stmt_execute($stmt);
    header("Location: items.php?msg=deleted");
    exit;
}

// ---------- CREATE or UPDATE ----------
if (isset($_POST["save"])) {
    $id          = $_POST["item_id"];
    $description = $_POST["description"];
    $quantity    = $_POST["quantity"];
    $type        = $_POST["type_id"];
    $category    = $_POST["category_id"];
    $status      = $_POST["status_id"];
    $supplier    = $_POST["supplier_id"];
    $location    = $_POST["location"];
    $made        = optional($_POST["manufacture_date"]);
    $bought      = optional($_POST["purchase_date"]);
    $used        = optional($_POST["use_date"]);
    $warranty    = optional($_POST["warranty_date"]);
    $expiry      = optional($_POST["expiry_date"]);

    if ($id == "") {
        // new item
        $sql = "INSERT INTO items (description, quantity, type_id, category_id, status_id, supplier_id,
                location, manufacture_date, purchase_date, use_date, warranty_date, expiry_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "siiiiissssss", $description, $quantity, $type, $category,
            $status, $supplier, $location, $made, $bought, $used, $warranty, $expiry);
        $done = "created";
    } else {
        // existing item
        $sql = "UPDATE items SET description = ?, quantity = ?, type_id = ?, category_id = ?,
                status_id = ?, supplier_id = ?, location = ?, manufacture_date = ?, purchase_date = ?,
                use_date = ?, warranty_date = ?, expiry_date = ? WHERE item_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "siiiiissssssi", $description, $quantity, $type, $category,
            $status, $supplier, $location, $made, $bought, $used, $warranty, $expiry, $id);
        $done = "updated";
    }

    if (mysqli_stmt_execute($stmt)) {
        header("Location: items.php?msg=$done");
        exit;
    } else {
        $message = "Could not save the item: " . mysqli_error($conn);
    }
}

// ---------- DATA FOR THE PAGE ----------
$types      = mysqli_query($conn, "SELECT * FROM types ORDER BY type_name");
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name");
$statuses   = mysqli_query($conn, "SELECT * FROM statuses ORDER BY status_name");
$suppliers  = mysqli_query($conn, "SELECT * FROM suppliers ORDER BY supplier_name");

$sql = "SELECT items.*, types.type_name, categories.category_name,
               statuses.status_name, statuses.badge_color, suppliers.supplier_name
        FROM items
        JOIN types ON items.type_id = types.type_id
        JOIN categories ON items.category_id = categories.category_id
        JOIN statuses ON items.status_id = statuses.status_id
        JOIN suppliers ON items.supplier_id = suppliers.supplier_id
        ORDER BY items.date_created DESC, items.item_id DESC";
$items = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Items</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include "nav.php"; ?>

    <div class="container">
        <div class="page-head">
            <h2>Items</h2>
            <button onclick="openAdd()">+ Add Item</button>
        </div>

        <?php if ($message != "") { ?>
            <div class="message"><?php echo e($message); ?></div>
        <?php } ?>

        <div class="table-wrap">
            <table>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Supplier</th>
                    <th>Location</th>
                    <th>Actions</th>
                </tr>

                <?php if (mysqli_num_rows($items) == 0) { ?>
                    <tr><td colspan="9" class="empty">No items yet. Click "+ Add Item" to create.</td></tr>
                <?php } ?>

                <?php while ($row = mysqli_fetch_assoc($items)) { ?>
                <tr data-item="<?php echo e(json_encode($row)); ?>">
                    <td><?php echo date("d M Y", strtotime($row["date_created"])); ?></td>
                    <td><?php echo e($row["description"]); ?></td>
                    <td><?php echo $row["quantity"]; ?></td>
                    <td><?php echo e($row["type_name"]); ?></td>
                    <td><?php echo e($row["category_name"]); ?></td>
                    <td><span class="badge badge-<?php echo e($row["badge_color"]); ?>"><?php echo e($row["status_name"]); ?></span></td>
                    <td><?php echo e($row["supplier_name"]); ?></td>
                    <td><?php echo e($row["location"]); ?></td>
                    <td>
                        <div class="menu">
                            <button class="dots" onclick="toggleMenu(this)" aria-label="Item actions">&#8942;</button>
                            <div class="menu-list">
                                <button onclick="openView(this)">View</button>
                                <button onclick="openEdit(this)">Edit</button>
                                <form method="post" onsubmit="return confirm('Delete this item? This cannot be undone.');">
                                    <input type="hidden" name="item_id" value="<?php echo $row["item_id"]; ?>">
                                    <button type="submit" name="delete" class="danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>

    <!-- Add / Edit pop-up -->
    <div class="modal-bg" id="formModal">
        <div class="modal">
            <h3 id="formTitle">Add Item</h3>
            <form method="post">
                <input type="hidden" name="item_id" id="f_item_id" value="">

                <div class="form-grid">
                    <div class="full">
                        <label>Description of goods</label>
                        <input type="text" name="description" id="f_description" required>
                    </div>

                    <div>
                        <label>Quantity</label>
                        <input type="number" name="quantity" id="f_quantity" min="1" value="1" required>
                    </div>
                    <div>
                        <label>Location <small>(where it is now)</small></label>
                        <input type="text" name="location" id="f_location" placeholder="e.g. Main Warehouse">
                    </div>

                    <div>
                        <label>Item type</label>
                        <select name="type_id" id="f_type_id" required>
                            <option value="">Select a type</option>
                            <?php while ($r = mysqli_fetch_assoc($types)) { ?>
                                <option value="<?php echo $r["type_id"]; ?>"><?php echo e($r["type_name"]); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div>
                        <label>Item category</label>
                        <select name="category_id" id="f_category_id" required>
                            <option value="">Select a category</option>
                            <?php while ($r = mysqli_fetch_assoc($categories)) { ?>
                                <option value="<?php echo $r["category_id"]; ?>"><?php echo e($r["category_name"]); ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div>
                        <label>Item status</label>
                        <select name="status_id" id="f_status_id" required>
                            <option value="">Select a status</option>
                            <?php while ($r = mysqli_fetch_assoc($statuses)) { ?>
                                <option value="<?php echo $r["status_id"]; ?>"><?php echo e($r["status_name"]); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div>
                        <label>Supplier</label>
                        <select name="supplier_id" id="f_supplier_id" required>
                            <option value="">Select a supplier</option>
                            <?php while ($r = mysqli_fetch_assoc($suppliers)) { ?>
                                <option value="<?php echo $r["supplier_id"]; ?>"><?php echo e($r["supplier_name"]); ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div>
                        <label>Manufacture date <small>(optional)</small></label>
                        <input type="date" name="manufacture_date" id="f_manufacture_date">
                    </div>
                    <div>
                        <label>Purchase date <small>(optional)</small></label>
                        <input type="date" name="purchase_date" id="f_purchase_date">
                    </div>
                    <div>
                        <label>Use date <small>(optional)</small></label>
                        <input type="date" name="use_date" id="f_use_date">
                    </div>
                    <div>
                        <label>Warranty date <small>(optional)</small></label>
                        <input type="date" name="warranty_date" id="f_warranty_date">
                    </div>
                    <div>
                        <label>Expiry date <small>(optional)</small></label>
                        <input type="date" name="expiry_date" id="f_expiry_date">
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-light" onclick="hide('formModal')">Cancel</button>
                    <button type="submit" name="save">Create item</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View pop-up -->
    <div class="modal-bg" id="viewModal">
        <div class="modal">
            <h3>Item details</h3>
            <div id="viewBody"></div>
            <div class="modal-actions">
                <button type="button" class="btn-light" onclick="hide('viewModal')">Close</button>
            </div>
        </div>
    </div>

    <script>
        // the form fields we fill when editing
        var fields = ["item_id", "description", "quantity", "location", "type_id", "category_id",
                      "status_id", "supplier_id", "manufacture_date", "purchase_date",
                      "use_date", "warranty_date", "expiry_date"];

        function show(id) { document.getElementById(id).style.display = "flex"; }
        function hide(id) { document.getElementById(id).style.display = "none"; }

        // each table row carries its own data, so we read it back from the row
        function getItem(button) {
            return JSON.parse(button.closest("tr").dataset.item);
        }

        function closeMenus() {
            document.querySelectorAll(".menu-list").forEach(function (m) { m.classList.remove("open"); });
        }

        function toggleMenu(button) {
            var list = button.nextElementSibling;
            var wasOpen = list.classList.contains("open");
            closeMenus();
            if (!wasOpen) { list.classList.add("open"); }
        }

        // clicking anywhere else closes the 3-dot menus
        document.addEventListener("click", function (e) {
            if (!e.target.closest(".menu")) { closeMenus(); }
        });

        function openAdd() {
            fields.forEach(function (name) { document.getElementById("f_" + name).value = ""; });
            document.getElementById("f_quantity").value = 1;
            document.getElementById("formTitle").textContent = "Add Item";
            document.querySelector("#formModal button[name=save]").textContent = "Create item";
            show("formModal");
        }

        function openEdit(button) {
            var item = getItem(button);
            fields.forEach(function (name) {
                document.getElementById("f_" + name).value = item[name] || "";
            });
            document.getElementById("formTitle").textContent = "Edit Item";
            document.querySelector("#formModal button[name=save]").textContent = "Save changes";
            closeMenus();
            show("formModal");
        }

        function openView(button) {
            var item = getItem(button);
            var rows = [
                ["Description", item.description],
                ["Quantity", item.quantity],
                ["Type", item.type_name],
                ["Category", item.category_name],
                ["Status", item.status_name],
                ["Supplier", item.supplier_name],
                ["Location", item.location],
                ["Manufacture date", item.manufacture_date],
                ["Purchase date", item.purchase_date],
                ["Use date", item.use_date],
                ["Warranty date", item.warranty_date],
                ["Expiry date", item.expiry_date],
                ["Created", item.date_created]
            ];
            var box = document.getElementById("viewBody");
            box.innerHTML = "";
            rows.forEach(function (row) {
                var p = document.createElement("p");
                var label = document.createElement("b");
                label.textContent = row[0] + ": ";
                p.appendChild(label);
                p.appendChild(document.createTextNode(row[1] || "Not set"));
                box.appendChild(p);
            });
            closeMenus();
            show("viewModal");
        }
    </script>
</body>
</html>