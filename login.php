<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #e3f2fd, #ffffff);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .login-card {
      position: relative;
      width: 100%;
      max-width: 400px;
      padding: 2rem;
      border-radius: 1rem;
      background-color: #fff;
      box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.1);
      animation: fadeIn 0.6s ease-in-out;
    }
    .close-btn {
      position: absolute;
      top: 10px;
      right: 15px;
      font-size: 1.2rem;
      color: #aaa;
      cursor: pointer;
      transition: color 0.3s ease;
    }
    .close-btn:hover {
      color: #000;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

  <div class="login-card text-center">
    <span class="close-btn" onclick="window.location.href='index.php'">&times;</span>

    <h4 class="mb-3">Welcome Back!!</h4>
    <form action="login.php" method="POST" class="text-start">
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" name="username" id="username" class="form-control" required />
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" name="password" id="password" class="form-control" required />
      </div>
      <div class="d-grid mb-3">
        <button type="submit" class="btn btn-primary">Login</button>
      </div>
    </form>

    <p class="text-center text-muted small mt-4">Forgot your password? <a href="#">Reset it</a></p>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
