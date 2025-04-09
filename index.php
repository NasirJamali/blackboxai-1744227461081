<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Building Materials Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { 
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                      url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80');
            background-size: cover;
            background-position: center;
            height: 100vh;
            color: white;
        }
        .hero-content {
            height: 80vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .btn-action {
            padding: 12px 30px;
            font-size: 1.1rem;
            margin: 10px;
            border-radius: 50px;
            transition: all 0.3s;
        }
        .btn-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-transparent">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-building"></i> Building Materials Marketplace
            </a>
            <div class="d-flex">
                <a href="login.php" class="btn btn-outline-light me-2">Login</a>
                <a href="signup.php" class="btn btn-success">Sign Up</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row hero-content">
            <div class="col-md-8 mx-auto text-center">
                <h1 class="display-4 mb-4">Connecting Buyers and Sellers of Building Materials</h1>
                <p class="lead mb-5">Find the best deals on construction materials or sell your products to qualified buyers</p>
                <div class="d-flex justify-content-center">
                    <a href="signup.php?role=buyer" class="btn btn-success btn-action">
                        <i class="bi bi-cart-plus"></i> I'm a Buyer
                    </a>
                    <a href="signup.php?role=seller" class="btn btn-primary btn-action">
                        <i class="bi bi-shop"></i> I'm a Seller
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
