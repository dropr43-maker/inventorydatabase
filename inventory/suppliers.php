<?php
$title = "Suppliers";
$singular = "supplier";
$table = "suppliers";
$idcol = "supplier_id";
$fields = [
    ["name" => "supplier_name", "label" => "Supplier name", "type" => "text", "required" => true],
    ["name" => "phone", "label" => "Phone", "type" => "text", "required" => false],
    ["name" => "email", "label" => "Email", "type" => "email", "required" => false]
];
include "list_page.php";
?>