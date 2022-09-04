<?php
require_once "header.php";
?>
<p>test123
<?php
$db = \Config\Database::connect();
var_dump($db);
echo "test23";
require_once "footer.php";