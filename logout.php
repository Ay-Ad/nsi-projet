<?php
// ========================================
// FICHIER 4: logout.php
// ========================================
session_start();
session_destroy();
header('Location: index.php');
exit();
?>