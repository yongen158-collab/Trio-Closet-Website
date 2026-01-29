<?php
// product.php - simple lecturer-style product detail page using producttbl
require_once 'config.php';
$conn = db_connect();

if (!function_exists('h')) {
    function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('formatRM')) {
    function formatRM($n){ return 'RM ' . number_format((float)$n, 2); }
}

// Accept ?prid= or ?id=
$pid = intval($_GET['prid'] ?? $_GET['id'] ?? 0);
if ($pid <= 0) {
    header('Location: shop.php'); exit;
}

// Try finding by product_id first, fallback to id column if needed
$product = null;
$stmt = $conn->prepare("SELECT * FROM producttbl WHERE product_id = ? LIMIT 1");
$stmt->bind_param('i', $pid);
$stmt->execute();
$res = $stmt->get_result();
$product = $res->fetch_assoc();
$stmt->close();

if (!$product) {
    // try alternative column name `id`
    $stmt = $conn->prepare("SELECT * FROM producttbl WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $pid);
    $stmt->execute();
    $res = $stmt->get_result();
    $product = $res->fetch_assoc();
    $stmt->close();
}

if (!$product) {
    include 'header.php';
    echo '<div class="container py-5"><h2>Product not found</h2><p><a href="shop.php" class="btn btn-primary">Back to Shop</a></p></div>';
    include 'footer.php';
    exit;
}

// image path handling (your DB stores "images/..." already)
$img = $product['image'] ?? '';
if ($img === '') $img = 'images/placeholder.png';
elseif (strpos($img, '/') === false) $img = 'images/' . $img;

// sizes list (comma separated in DB)
$sizes = array_filter(array_map('trim', explode(',', $product['sizes'] ?? '')));

// determine identifier for cart/add links (prefer product_id then id)
$useId = $product['product_id'] ?? $product['id'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo h($product['title'] ?? $product['product_name'] ?? 'Product'); ?> | TRIO CLOSET</title>

    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">

    <style>
        /* small page-specific styles kept from your HTML */
        .product-details-page{padding:50px 0}
        .product-gallery img{width:100%;border-radius:10px}
        .price-tag{font-size:2rem;color:#dc3545;font-weight:bold}
        .old-price{margin-left:10px;color:#999;text-decoration:line-through}
        .size-option{border:1px solid #ddd;padding:8px 15px;margin-right:10px;cursor:pointer;border-radius:5px}
        .size-option.selected{background:#000;color:#fff}
        .color-option{width:30px;height:30px;border-radius:50%;display:inline-block;border:1px solid #ddd;margin-right:10px;cursor:pointer}
        .product-meta p{margin-bottom:6px}
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<section class="product-details-page">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="shop.php">Shop</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo h($product['title'] ?? $product['product_name'] ?? ''); ?></li>
            </ol>
        </nav>

        <div class="row">
            <!-- Images -->
            <div class="col-md-6">
                <div class="product-gallery">
                    <img src="<?php echo h($img); ?>" alt="<?php echo h($product['title'] ?? $product['product_name']); ?>" class="img-fluid mb-3 main-image">
                </div>
            </div>

            <!-- Info -->
            <div class="col-md-6 product-info">
                <h1 class="mb-3"><?php echo h($product['title'] ?? $product['product_name'] ?? ''); ?></h1>

                <div class="d-flex align-items-center mb-3">
                    <div class="text-warning me-2">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                    </div>
                    <span class="text-muted">(4.5/5.0) • 128 Reviews</span>
                </div>

                <div class="mb-4">
                    <span class="price-tag"><?php echo formatRM($product['price'] ?? $product['product_price'] ?? 0); ?></span>
                    <?php if (!empty($product['old_price'])): ?>
                        <span class="old-price"><?php echo formatRM($product['old_price']); ?></span>
                    <?php endif; ?>
                </div>

                <p class="mb-4"><?php echo nl2br(h($product['description'] ?? $product['product_description'] ?? 'No description available.')); ?></p>

                <!-- Sizes (render from DB) -->
                <?php if (!empty($sizes)): ?>
                    <div class="mb-4">
                        <h5>Select Size:</h5>
                        <div class="d-flex mt-2">
                            <?php foreach ($sizes as $s): ?>
                                <div class="size-option"><?php echo h($s); ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Color: keep static (UI only) -->
                <div class="mb-4">
                    <h5>Select Color:</h5>
                    <div class="d-flex mt-2">
                        <span class="color-option" style="background:#ffffff"></span>
                        <span class="color-option" style="background:#007bff"></span>
                        <span class="color-option" style="background:#28a745"></span>
                        <span class="color-option" style="background:#343a40"></span>
                    </div>
                </div>

                <!-- Quantity & Add to Cart -->
                <div class="row align-items-center mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Quantity:</label>
                        <form method="get" action="cart.php" id="cartForm" class="d-flex">
                            <input type="hidden" name="add" value="<?php echo (int)$useId; ?>">
                            <input type="number" name="qty" class="form-control" value="1" min="1" style="max-width:100px">
                    </div>
                    <div class="col-md-9">
                            <button class="btn btn-dark btn-lg w-100 ms-3 mt-3" type="submit"><i class="fas fa-shopping-cart me-2"></i> Add to Cart</button>
                        </form>
                    </div>
                </div>

                <!-- Meta -->
                <div class="product-meta">
                    <p><strong>SKU:</strong> <?php echo h($product['sku'] ?? ($product['sku_code'] ?? '')); ?></p>
                    <p><strong>Category / Brand:</strong> <?php echo h($product['category'] ?? $product['brand'] ?? ''); ?></p>
                    <?php if (!empty($product['stock'] ?? $product['product_stock'])): ?>
                        <p><strong>Stock:</strong> <?php echo intval($product['stock'] ?? $product['product_stock']); ?> units</p>
                    <?php endif; ?>
                    <?php if (!empty($product['sizes'])): ?>
                        <p><strong>Sizes:</strong> <?php echo h($product['sizes']); ?></p>
                    <?php endif; ?>
                    <p><strong>Delivery:</strong> 2-5 business days</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>

<!-- small JS (size/color UI) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.size-option').forEach(opt=>{
        opt.addEventListener('click', function(){
            document.querySelectorAll('.size-option').forEach(o=>o.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
    document.querySelectorAll('.color-option').forEach(col=>{
        col.addEventListener('click', function(){
            document.querySelectorAll('.color-option').forEach(c=>c.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
});
</script>
</body>
</html>