<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['userId'])) {
  echo "<script type=\"text/javascript\">\n  window.location = (\"../index.php\");\n  </script>";
}
?>