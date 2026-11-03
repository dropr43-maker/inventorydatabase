<?php
$title = "Types";
$singular = "type";
$table = "types";
$idcol = "type_id";
$fields = [
    ["name" => "type_name", "label" => "Type name", "type" => "text", "required" => true]
];
include "list_page.php";
?>