<?php try { $pdo = new PDO("mysql:host=127.0.0.1;port=3306", "root", "root"); echo "Password is root"; } catch (PDOException $e) { echo $e->getMessage(); }
