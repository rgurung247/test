<?php
// Simple User Registration and Login System
session_start();

class SimpleAuth {
    private $usersFile = 'users.json';
    
    public function __construct() {
        if (!file_exists($this->usersFile)) {
            file_put_contents($this->usersFile, json_encode([]));
        }
    }
    
    private function getUsers() {
        return json_decode(file_get_contents($this->usersFile), true) ?: [];
    }
    
    private function saveUsers($users) {
        file_put_contents($this->usersFile, json_encode($users, JSON_PRETTY_PRINT));
    }
    
    public function register($username, $password, $email) {
        $users = $this->getUsers();
        
        if (isset($users[$username])) {
            return "Username already exists!";
        }
        
        $users[$username] = [
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'email' => $email,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->saveUsers($users);
        return "Registration successful!";
    }
    
    public function login($username, $password) {
        $users = $this->getUsers();
        
        if (!isset($users[$username]) || !password_verify($password, $users[$username]['password'])) {
            return "Invalid username or password!";
        }
        
        $_SESSION['user'] = $username;
        $_SESSION['email'] = $users[$username]['email'];
        return "Login successful! Welcome, $username!";
    }
    
    public function logout() {
        session_destroy();
        return "Logged out successfully!";
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user']);
    }
}

// Usage example
$auth = new SimpleAuth();

if ($_POST) {
    if (isset($_POST['register'])) {
        $result = $auth->register($_POST['username'], $_POST['password'], $_POST['email']);
    } elseif (isset($_POST['login'])) {
        $result = $auth->login($_POST['username'], $_POST['password']);
    } elseif (isset($_POST['logout'])) {
        $result = $auth->logout();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Simple PHP Auth System</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 500px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="password"], input[type="email"] { 
            width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; 
        }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .message { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <h1>Simple PHP Auth System</h1>
    
    <?php if (isset($result)): ?>
        <div class="message <?php echo strpos($result, 'successful') !== false ? 'success' : 'error'; ?>">
            <?php echo $result; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!$auth->isLoggedIn()): ?>
        <h2>Register</h2>
        <form method="POST">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="register">Register</button>
        </form>
        
        <h2>Login</h2>
        <form method="POST">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="login">Login</button>
        </form>
    <?php else: ?>
        <h2>Welcome, <?php echo $_SESSION['user']; ?>!</h2>
        <p>Email: <?php echo $_SESSION['email']; ?></p>
        <form method="POST">
            <button type="submit" name="logout">Logout</button>
        </form>
    <?php endif; ?>
</body>
</html>