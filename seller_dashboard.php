<?php
include 'includes/db_config.php';
session_start();

// Redirect if not logged in as seller
if(!isset($_SESSION["loggedin"]) || $_SESSION["role"] != "seller"){
    header("location: login.php");
    exit;
}

$error = '';
$success = '';

// Handle offer submission
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_offer'])){
    $tender_id = trim($_POST['tender_id']);
    $price_per_unit = trim($_POST['price_per_unit']);
    $delivery_time = trim($_POST['delivery_time']);
    $contact_info = trim($_POST['contact_info']);
    $message = trim($_POST['message']);

    $stmt = $conn->prepare("INSERT INTO offers (tender_id, seller_id, price_per_unit, delivery_time, contact_info, message) VALUES (:tender_id, :seller_id, :price_per_unit, :delivery_time, :contact_info, :message)");
    $stmt->bindValue(':tender_id', $tender_id, SQLITE3_INTEGER);
    $stmt->bindValue(':seller_id', $_SESSION["id"], SQLITE3_INTEGER);
    $stmt->bindValue(':price_per_unit', $price_per_unit, SQLITE3_FLOAT);
    $stmt->bindValue(':delivery_time', $delivery_time, SQLITE3_TEXT);
    $stmt->bindValue(':contact_info', $contact_info, SQLITE3_TEXT);
    $stmt->bindValue(':message', $message, SQLITE3_TEXT);
    
    if($stmt->execute()){
        $success = "Offer submitted successfully!";
    } else {
        $error = "Error submitting offer. Please try again.";
    }
}

// Fetch approved tenders
$tenders = [];
$stmt = $conn->prepare("SELECT t.*, u.username as buyer_name 
        FROM tenders t 
        JOIN users u ON t.buyer_id = u.id 
        WHERE t.status = 'approved'
        ORDER BY t.created_at DESC");
$result = $stmt->execute();

while($row = $result->fetchArray(SQLITE3_ASSOC)){
    $tenders[] = $row;
}

// Fetch seller's offers
$my_offers = [];
$stmt = $conn->prepare("SELECT o.*, t.material_name, t.quantity, t.unit 
        FROM offers o 
        JOIN tenders t ON o.tender_id = t.id 
        WHERE o.seller_id = :seller_id
        ORDER BY o.created_at DESC");
$stmt->bindValue(':seller_id', $_SESSION["id"], SQLITE3_INTEGER);
$result = $stmt->execute();

while($row = $result->fetchArray(SQLITE3_ASSOC)){
    $my_offers[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard - Building Materials Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { background-color: #28a745; }
        .card { margin-bottom: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .tender-card { border-left: 4px solid #007bff; }
        .offer-card { border-left: 4px solid #6c757d; }
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
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Available Tenders</h5>
                    </div>
                    <div class="card-body">
                        <?php if(empty($tenders)): ?>
                            <p class="text-muted">No available tenders at the moment.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach($tenders as $tender): ?>
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
                                        
                                        <!-- Offer Form (Collapsible) -->
                                        <button class="btn btn-sm btn-outline-primary mt-2" type="button" data-bs-toggle="collapse" data-bs-target="#offerForm<?php echo $tender['id']; ?>">
                                            Make Offer
                                        </button>
                                        <div class="collapse mt-2" id="offerForm<?php echo $tender['id']; ?>">
                                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                                <input type="hidden" name="tender_id" value="<?php echo $tender['id']; ?>">
                                                <div class="row g-2">
                                                    <div class="col-md-4">
                                                        <label class="form-label">Price per Unit</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">$</span>
                                                            <input type="number" step="0.01" name="price_per_unit" class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Delivery Time</label>
                                                        <input type="text" name="delivery_time" class="form-control" placeholder="e.g., 3 days" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Contact Info</label>
                                                        <input type="text" name="contact_info" class="form-control" placeholder="WhatsApp/Phone" required>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Message (Optional)</label>
                                                        <textarea name="message" class="form-control" rows="2"></textarea>
                                                    </div>
                                                    <div class="col-12">
                                                        <button type="submit" name="submit_offer" class="btn btn-primary btn-sm">Submit Offer</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">My Offers</h5>
                    </div>
                    <div class="card-body">
                        <?php if(empty($my_offers)): ?>
                            <p class="text-muted">You haven't made any offers yet.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach($my_offers as $offer): ?>
                                    <div class="list-group-item offer-card mb-2">
                                        <div class="d-flex justify-content-between">
                                            <h6><?php echo htmlspecialchars($offer['material_name']); ?></h6>
                                            <span class="badge bg-<?php 
                                                echo $offer['status'] == 'approved' ? 'success' : 
                                                     ($offer['status'] == 'rejected' ? 'danger' : 'warning'); ?>">
                                                <?php echo ucfirst($offer['status']); ?>
                                            </span>
                                        </div>
                                        <p class="mb-1 small">Price: $<?php echo htmlspecialchars($offer['price_per_unit']); ?> per <?php echo htmlspecialchars($offer['unit']); ?></p>
                                        <p class="mb-1 small">Delivery: <?php echo htmlspecialchars($offer['delivery_time']); ?></p>
                                        <?php if($offer['status'] == 'approved'): ?>
                                            <p class="mb-1 small text-success">Buyer has approved your offer!</p>
                                        <?php endif; ?>
                                        <small class="text-muted">Submitted on <?php echo date('M d, Y h:i A', strtotime($offer['created_at'])); ?></small>
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
