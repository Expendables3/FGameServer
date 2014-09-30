<?php
$params = $_SERVER["QUERY_STRING"];
$href = 'idsite/index.php?'.$params;
header("location:{$href}");
?>