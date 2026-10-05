<?php
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        if (isset($username) && isset($password)) {
            $userRepository = new UserRepository();
            $user = $userRepository->getUserByUsername($username);
            if ($user && password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                header("Location: index.php");
                exit();
            } else {
                echo "Invalid username or password.";
            }
        }
    }
?>