<?php
$title = "Status";
$singular = "status";
$table = "statuses";
$idcol = "status_id";
$fields = [
    ["name" => "status_name", "label" => "Status name", "type" => "text", "required" => true],
    ["name" => "badge_color", "label" => "Badge colour", "type" => "colour", "required" => true]
];
include "list_page.php";
?>