<?php
// Shared engine for Categories, Types, Status and Suppliers.
// Each of those pages sets $title, $singular, $table, $idcol and $fields, then includes this file.
include "database.php";

$page = basename($_SERVER["PHP_SELF"]);
$message = "";
$isError = false;
$name_column = $fields[0]["name"];
$colours = ["grey", "blue", "green", "orange", "red"];

function e($value) {
    return htmlspecialchars($value ?? "");
}

if (isset($_GET["msg"])) {
    if ($_GET["msg"] == "added") { $message = ucfirst($singular) . " added."; }
    if ($_GET["msg"] == "deleted") { $message = ucfirst($singular) . " deleted."; }
}

// ---------- ADD ----------
if (isset($_POST["add"])) {
    $columns = [];
    $values = [];
    foreach ($fields as $field) {
        $columns[] = $field["name"];
        $values[] = trim($_POST[$field["name"]]);
    }
    $marks = implode(", ", array_fill(0, count($columns), "?"));
    $sql = "INSERT INTO $table (" . implode(", ", $columns) . ") VALUES ($marks)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, str_repeat("s", count($values)), ...$values);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: $page?msg=added");
        exit;
    }
    $isError = true;
    if (mysqli_stmt_errno($stmt) == 1062) {
        $message = "That $singular already exists. Names must be unique.";
    } else {
        $message = "Could not save: " . mysqli_stmt_error($stmt);
    }
}

// ---------- DELETE ----------
if (isset($_POST["delete"])) {
    $id = $_POST["id"];

    // block the delete if any item still uses this row
    $check = mysqli_prepare($conn, "SELECT COUNT(*) FROM items WHERE $idcol = ?");
    mysqli_stmt_bind_param($check, "i", $id);
    mysqli_stmt_execute($check);
    mysqli_stmt_bind_result($check, $count);
    mysqli_stmt_fetch($check);
    mysqli_stmt_close($check);

    if ($count > 0) {
        $isError = true;
        $message = "Can't delete this $singular because $count item(s) still use it. Change those items first.";
    } else {
        $stmt = mysqli_prepare($conn, "DELETE FROM $table WHERE $idcol = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        header("Location: $page?msg=deleted");
        exit;
    }
}

$rows = mysqli_query($conn, "SELECT * FROM $table ORDER BY $name_column");
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo e($title); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include "nav.php"; ?>

    <div class="container">
    <a class="back" href="index.php">&larr; Back to Home</a>
        <div class="page-head">
            <h2><?php echo e($title); ?></h2>
            <button onclick="openAdd()">+ Add <?php echo e($singular); ?></button>
        </div>

        <?php if ($message != "") { ?>
            <div class="message" <?php if ($isError) { echo "style='border-color:#b00020'"; } ?>><?php echo e($message); ?></div>
        <?php } ?>

        <div class="table-wrap">
            <table>
                <tr>
                    <?php foreach ($fields as $field) { ?>
                        <th><?php echo e($field["label"]); ?></th>
                    <?php } ?>
                    <th>Actions</th>
                </tr>

                <?php if (mysqli_num_rows($rows) == 0) { ?>
                    <tr><td colspan="<?php echo count($fields) + 1; ?>" class="empty">
                        No <?php echo e(strtolower($title)); ?> yet. Click "+ Add <?php echo e($singular); ?>" to create one.
                    </td></tr>
                <?php } ?>

                <?php while ($row = mysqli_fetch_assoc($rows)) { ?>
                <tr>
                    <?php foreach ($fields as $field) {
                        $value = $row[$field["name"]];
                        if ($field["type"] == "colour") {
                            echo "<td><span class='badge badge-" . e($value) . "'>" . e($value) . "</span></td>";
                        } else {
                            echo "<td>" . e($value) . "</td>";
                        }
                    } ?>
                    <td>
                        <form method="post" onsubmit="return confirm('Delete this <?php echo e($singular); ?>?');">
                            <input type="hidden" name="id" value="<?php echo $row[$idcol]; ?>">
                            <button type="submit" name="delete" class="row-delete">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>

    <!-- Add pop-up -->
    <div class="modal-bg" id="addModal">
        <div class="modal">
            <h3>Add <?php echo e($singular); ?></h3>
            <form method="post" id="addForm">
                <div class="form-grid">
                    <?php foreach ($fields as $field) { ?>
                    <div class="full">
                        <label>
                            <?php echo e($field["label"]); ?>
                            <?php if (!$field["required"]) { echo "<small>(optional)</small>"; } ?>
                        </label>
                        <?php if ($field["type"] == "colour") { ?>
                            <select name="<?php echo $field["name"]; ?>">
                                <?php foreach ($colours as $colour) { ?>
                                    <option value="<?php echo $colour; ?>"><?php echo ucfirst($colour); ?></option>
                                <?php } ?>
                            </select>
                        <?php } else { ?>
                            <input type="<?php echo $field["type"]; ?>" name="<?php echo $field["name"]; ?>"
                                <?php if ($field["required"]) { echo "required"; } ?>>
                        <?php } ?>
                    </div>
                    <?php } ?>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-light" onclick="closeAdd()">Cancel</button>
                    <button type="submit" name="add">Add <?php echo e($singular); ?></button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAdd() {
            document.getElementById("addForm").reset();
            document.getElementById("addModal").style.display = "flex";
        }
        function closeAdd() {
            document.getElementById("addModal").style.display = "none";
        }
    </script>
</body>
</html>