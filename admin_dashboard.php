<?php
include 'includes/db_config.php';
session_start();

// Redirect if not logged in as admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["role"] != "admin"){
    header("location: login.php");
    exit;
}

$error = '';
$success = '';

// Handle tender approval/rejection
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_tender'])){
    $tender_id = trim($_POST['tender_id']);
    $status = trim($_POST['status']);

    $stmt = $conn->prepare("UPDATE tenders SET status = :status WHERE id = :tender_id");
    $stmt->bindValue(':status', $status, SQLITE3_TEXT);
    $stmt->bindValue(':tender_id', $tender_id, SQLITE3_INTEGER);
    
    if($stmt->execute()){
        $success = "Tender status updated successfully!";
    } else {
        $error = "Error updating tender status. Please try again.";
    }
}

// Handle offer approval/rejection
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_offer'])){
    $offer_id = trim($_POST['offer_id']);
    $status = trim($_POST['status']);

    $stmt = $conn->prepare("UPDATE offers SET status = :status WHERE id = :offer_id");
    $stmt->bindValue(':status', $status, SQLITE3_TEXT);
    $stmt->bindValue(':offer_id', $offer_id, SQLITE3_INTEGER);
    
    if($stmt->execute()){
        $success = "Offer status updated successfully!";
    } else {
        $error = "Error updating offer status. Please try again.";
    }
}

// Fetch pending tenders
$pending_tenders = [];
$stmt = $conn->prepare("SELECT t.*, u.username as buyer_name 
        FROM tenders t 
        JOIN users u ON t.buyer_id = u.id 
        WHERE t.status = 'pending'
        ORDER BY t.created_at DESC");
$result = $stmt->execute();

while($row = $result->fetchArray(SQLITE3_ASSOC)){
    $pending_tenders[] = $row;
}

// Fetch all offers
$offers = [];
$stmt = $conn->prepare("SELECT o.*, t.material_name, t.quantity, t.unit, u1.username as seller_name, u2.username as buyer_name
        FROM offers o 
        JOIN tenders t ON o.tender_id = t.id 
        JOIN users u1 ON o.seller_id = u1.id
        JOIN users u2 ON t.buyer_id = u2.id
        ORDER BY o.created_at DESC");
$result = $stmt->execute();

while($row = $result->fetchArray(SQLITE3_ASSOC)){
    $offers[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Building Materials Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { background-color: #6f42c1; }
        .card { margin-bottom: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .tender-card { border-left: 4px solid #6f42c1; }
        .offer-card { border-left: 4px solid #20c997; }
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
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-purple text-white">
                        <h5 class="mb-0">Pending Tenders</h5>
                    </div>
                    <div class="card-body">
                        <?php if(empty($pending_tenders)): ?>
                            <p class="text-muted">No pending tenders at the moment.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach($pending_tenders as $tender): ?>
                                    <div class="list-group-item tender-card mb-3">
                                        <div class="d-flex justify-content-between">
                                            <h5><?php echo htmlspecialchars($tender['material_name']); ?></h5>
                                            <small class="text-muted">Posted by <?php echo htmlspecialchars($tender['buyer_name']); ?></small>
                                        </div>
                                        <p class="mb-1">Quantity: <?php echo htmlspecialchars($tender['quantity']); ?> <?php echo htmlspecialchars($tender['unit']); ?></p>
                                        <?php if(!empty($tender['notes'])): ?>
                                            <p class="mb-1">Notes: <?php echo htmlspecialchars($tender['notes']); ?></p>
                                        <?php endif; ?>
                                        <small class="text-muted">Posted on <?php echo date('M d, Y h:i A', strtotime($tender['created_at'])); ?></small>
                                        
                                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="mt-2">
                                            <input type="hidden" name="tender_id" value="<?php echo $tender['id']; ?>">
                                            <div class="btn-group btn-group-sm">
                                                <button type="submit" name="update_tender" value="approved" class="btn btn-success">Approve</button>
                                                <button type="submit" name="update_tender" value="rejected" class="btn btn-danger">Reject</button>
                                            </div>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-teal text-white">
                        <h5 class="mb-0">All Offers</h5>
                    </div>
                    <div class="card-body">
                        <?php if(empty($offers)): ?>
                            <p class="text-muted">No offers submitted yet.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach($offers as $offer): ?>
                                    <div class="list-group-item offer-card mb-2">
                                        <div class="d-flex justify-content-between">
                                            <h6><?php echo htmlspecialchars($offer['material_name']); ?></h6>
                                            <span class="badge bg-<?php 
                                                echo $offer['status'] == 'approved' ? 'success' : 
                                                     ($offer['status'] == 'rejected' ? 'danger' : 'warning'); ?>">
                                                <?php echo ucfirst($offer['status']); ?>
                                            </span>
                                        </div>
                                        <p class="mb-1 small">From: <?php echo htmlspecialchars($offer['seller_name']); ?></p>
                                        <p class="mb-1 small">To: <?php echo htmlspecialchars($offer['buyer_name']); ?></p>
                                        <p class="mb-1 small">Price: $<?php echo htmlspecialchars($offer['price_per_unit']); ?> per <?php echo htmlspecialchars($offer['unit']); ?></p>
                                        <p class="mb-1 small">Delivery: <?php echo htmlspecialchars($offer['delivery_time']); ?></p>
                                        <?php if(!empty($offer['message'])): ?>
                                            <p class="mb-1 small">Message: <?php echo htmlspecialchars($offer['message']); ?></p>
                                        <?php endif; ?>
                                        <small class="text-muted">Submitted on <?php echo date('M d, Y h:i A', strtotime($offer['created_at'])); ?></small>
                                        
                                        <?php if($offer['status'] == 'pending'): ?>
                                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="mt-1">
                                                <input type="hidden" name="offer_id" value="<?php echo $offer['id']; ?>">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="submit" name="update_offer" value="approved" class="btn btn-success">Approve</button>
                                                    <button type="submit" name="update_offer" value="rejected" class="btn btn-danger">Reject</button>
                                                </div>
                                            </form>
                                        <?php endif; ?>
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
