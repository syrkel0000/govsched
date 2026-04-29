<?php
session_start();
session_destroy();
header('Location: /govsched/index.php');
exit();
?>