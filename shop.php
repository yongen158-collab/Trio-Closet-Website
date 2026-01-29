<?php
 include 'config.php';
$conn = db_connect();

if (!function_exists('h')) { function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }

// Get search keyword (sidebar POST or header GET)
$keyword = trim($_POST['keyword'] ?? $_GET['q'] ?? '');

// Pagination (simple)
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 6;
$offset = ($page - 1) * $perPage;

// Build WHERE clause (very simple lecturer-style escaping)
$where = '';
if ($keyword !== '') {
    $kw = mysqli_real_escape_string($conn, $keyword);
    $where = "WHERE title LIKE '%$kw%' OR description LIKE '%$kw%' OR category LIKE '%$kw%'";
}

// Count total products
$countRes = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM producttbl $where");
$total = intval(mysqli_fetch_assoc($countRes)['cnt'] ?? 0);
$totalPages = max(1, (int)ceil($total / $perPage));

// Fetch products for current page
$res = mysqli_query($conn, "SELECT * FROM producttbl $where ORDER BY id ASC LIMIT $offset, $perPage");

?>

<!DOCTYPE html>
<html>
<head>
    <title>TRIO CLOSET - Shop</title>

    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">

    <style>
        .product-img{width:100%;height:260px;object-fit:cover;border-radius:6px}
        .product-card{background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 6px 18px rgba(0,0,0,0.04)}
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container py-4">
    <div class="row">
        <!-- Filter Sidebar - Left Column -->
        <div class="col-lg-3">
            <div class="filter-sidebar">
                <h3 class="filter-title">FILTERS</h3>

                <!-- Category Filter -->
                <div class="filter-section">
                    <h5 class="filter-heading">CATEGORIES</h5>
                    <ul class="category-list list-unstyled">
                        <li><a href="shop.php"<?php if($keyword==='') echo ' class="fw-bold"'; ?>>All</a></li>
                        <li><a href="shop.php?<?php echo h(http_build_query(['q'=>'Women'])); ?>">Women</a></li>
                        <li><a href="shop.php?<?php echo h(http_build_query(['q'=>'Men'])); ?>">Men</a></li>
                        <li><a href="shop.php?<?php echo h(http_build_query(['q'=>'Kids'])); ?>">Kids</a></li>
                    </ul>
                </div>

                <hr>

                <!--search form (POST) -->
                <form method="post" action="shop.php">
                    <div class="mb-2">
                        <input type="text" name="keyword" class="form-control" placeholder="Search by name..." value="<?php echo h($keyword); ?>">
                    </div>
                    <button type="submit" name="searchbtn" class="btn btn-primary w-100">Search</button>
                </form>

                <button id="clearFiltersBtn" class="btn btn-outline-dark w-100 mt-3"><i class="fas fa-times me-2"></i> Clear Filters</button>
            </div>
        </div>

        <!-- Product Grid - -->
        <div class="col-lg-9">
            <!-- Shop Controls -->
            <div class="shop-controls mb-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p id="resultsInfo" class="mb-0">
                            <?php
                                if ($total === 0) echo 'Showing 0 products';
                                else {
                                    $start = $offset + 1;
                                    $end = min($offset + $perPage, $total);
                                    echo "Showing {$start}-{$end} of {$total} products";
                                }
                            ?>
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <select id="sortSelect" class="form-select w-auto d-inline-block">
                            <option>Sort by: Popular</option>
                            <option>Price: Low to High</option>
                            <option>Price: High to Low</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Product Grid -->
            <div id="productsGrid" class="row g-4">
                <?php
                if ($res && mysqli_num_rows($res) > 0):
                    while ($p = mysqli_fetch_assoc($res)):
                        $img = img_src($p['image'] ?? '');
                ?>
                <div class="col-md-4 col-sm-6">
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo h($img); ?>" alt="<?php echo h($p['title']); ?>" class="product-img">
                        </div>
                        <div class="product-info p-3">
                            <div class="product-category text-muted"><?php echo h($p['category'] ?? ''); ?></div>
                            <h3 class="product-title"><?php echo h($p['title']); ?></h3>
                            <div class="product-price text-danger"><?php echo 'RM ' . number_format((float)($p['price'] ?? 0), 2); ?></div>
                            <div class="mt-2"><small>Sizes: <?php echo h($p['sizes'] ?? ''); ?></small></div>
                            <div class="mt-2 d-grid">
                                <a href="product.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-outline-dark btn-sm">View Details</a>
                                <a href="cart.php?add=<?php echo (int)$p['id']; ?>" class="btn btn-primary btn-sm mt-2">Add to Cart</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                    endwhile;
                else:
                    echo '<div class="col-12"><p class="text-muted">No products found.</p></div>';
                endif;
                ?>
            </div>

            <!-- Pagination -->
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php
                   
                    $baseParams = [];
                    if ($keyword !== '') $baseParams['q'] = $keyword;
                    for ($i = 1; $i <= $totalPages; $i++) {
                        $params = $baseParams;
                        $params['page'] = $i;
                        $url = 'shop.php?' . http_build_query($params);
                        $active = $i === $page ? ' active' : '';
                        echo '<li class="page-item' . $active . '"><a class="page-link" href="' . h($url) . '">' . $i . '</a></li>';
                    }
                    ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>

function handleSearch(e) {
    e.preventDefault();
    const q = document.getElementById('searchInput')?.value || '';
    const url = 'shop.php' + (q ? ('?q=' + encodeURIComponent(q)) : '');
    window.location.href = url;
    return false;
}

document.getElementById('clearFiltersBtn')?.addEventListener('click', function(){
    window.location.href = 'shop.php';
});
</script>
</body>
</html>