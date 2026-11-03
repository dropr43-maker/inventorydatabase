<?php
$title = "Categories";
$singular = "category";
$table = "categories";
$idcol = "category_id";
$fields = [
    ["name" => "category_name", "label" => "Category name", "type" => "text", "required" => true]
];
include "list_page.php";
?>