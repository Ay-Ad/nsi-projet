<?php
$password = "ayb2008";
$hash = password_hash($password, PASSWORD_DEFAULT);
echo $hash;
?>