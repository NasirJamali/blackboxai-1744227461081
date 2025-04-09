<?php
include 'includes/db_config.php';
session_start();

// Redirect if not logged in as buyer
if(!isset($_SESSION["loggedin"]) || $_SESSION["role"] != "buyer"){
    header("location: login.php");
    exit;
}

$error = '';
$success = '';

// Handle new tender submission
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_tender'])){
    $material_name = trim($_POST['material_name']);
    $quantity = trim($_POST['quantity']);
    $unit = trim($_POST['unit']);
    $notes = trim($_POST['notes']);

    $stmt = $conn->prepare("INSERT INTO tenders (buyer_id, material_name, quantity, unit, notes) VALUES (:buyer_id, :material_name, :quantity, :unit, :notes)");
    $stmt->bindValue(':buyer_id', $_SESSION["id"], SQLITE3_INTEGER);
    $stmt->bindValue(':material_name', $material_name, SQLITE3_TEXT);
    $stmt->bindValue(':quantity', $quantity, SQLITE3_FLOAT);
    $stmt->bindValue(':unit', $unit, SQLITE3_TEXT);
    $stmt->bindValue(':notes', $notes, SQLITE3_TEXT);
    
    if($stmt->execute()){
        $success = "Tender posted successfully!";
    } else {
        $error = "Error posting tender. Please try again.";
    }
}

// Fetch buyer's tenders
$tenders = [];
$stmt = $conn->prepare("SELECT * FROM tenders WHERE buyer_id = :buyer_id ORDER BY created_at DESC");
$stmt->bindValue(':buyer_id', $_SESSION["id"], SQLITE3_INTEGER);
$result = $stmt->execute();

while($row = $result->fetchArray(SQLITE3_ASSOC)){
    $tenders[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard - Building Materials Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { background-color: #28a745; }
        .card { margin-bottom: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .tender-card { border-left: 4px solid #28a745; }
        .status-pending { color: #ffc107; }
        .status-approved { color: #28a745; }
        .status-rejected { color: #dc3545; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Building Materials Marketplace</a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                <a href="logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Post New Tender</h5>
                    </div>
                    <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="mb-3">
                                <label for="material_name" class="form-label">Material Name</label>
                                <input type="text" name="material_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" step="0.01" name="quantity" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="unit" class="form-label">Unit</label>
                                <select name="unit" class="form-select" required>
                                    <option value="bags">Bags</option>
                                    <option value="tons">Tons</option>
                                    <option value="kg">Kilograms</option>
                                    <option value="pieces">Pieces</option>
                                    <option value="cubic meters">Cubic Meters</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="notes" class="form-label">Additional Notes</label>
                                <textarea name="notes" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="submit_tender" class="btn btn-success">Post Tender</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">My Tenders</h5>
                    </div>
                    <div class="card-body">
                        <?php if(empty($tenders)): ?>
                            <p class="text-muted">You haven't posted any tenders yet.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach($tenders as $tender): ?>
                                    <div class="list-group-item tender-card mb-2">
                                        <div class="d-flex justify-content-between">
                                            <h5><?php echo htmlspecialchars($tender['material_name']); ?></h5>
                                            <span class="badge bg-<?php 
                                                echo $tender['status'] == 'approved' ? 'success' : 
                                                     ($tender['status'] == 'rejected' ? 'danger' : 'warning'); ?>">
                                                <?php echo ucfirst($tender['status']); ?>
                                            </span>
                                        </div>
                                        <p class="mb-1">Quantity: <?php echo htmlspecialchars($tender['quantity']); ?> <?php echo htmlspecialchars($tender['unit']); ?></p>
                                        <?php if(!empty($tender['notes'])): ?>
                                            <p class="mb-1">Notes: <?php echo htmlspecialchars($tender['notes']); ?></p>
                                        <?php endif; ?>
                                        <small class="text-muted">Posted on <?php echo date('M d, Y h:i A', strtotime($tender['created_at'])); ?></small>
                                        <div class="mt-2">
                                            <a href="edit_tender.php?id=<?php echo $tender['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                            <a href="delete_tender.php?id=<?php echo $tender['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
