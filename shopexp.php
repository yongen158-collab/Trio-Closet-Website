<?php

require_once 'config.php';
$conn = db_connect();
if (session_status() === PHP_SESSION_NONE) session_start();

$q = trim($_GET['q'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 6;
$offset = ($page - 1) * $perPage;

$where = '';
if ($q !== '') {
    $kw = mysqli_real_escape_string($conn, $q);
    $where = "WHERE title LIKE '%$kw%' OR description LIKE '%$kw%'";
}

$countRes = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM producttbl $where");
$total = intval(mysqli_fetch_assoc($countRes)['cnt'] ?? 0);
$totalPages = max(1, (int)ceil($total / $perPage));

$res = mysqli_query($conn, "SELECT * FROM producttbl $where ORDER BY id ASC LIMIT $offset, $perPage");
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Shop | TRIO CLOSET</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'header.php'; ?>

<div class="container py-4">
  <div class="row mb-3">
    <div class="col-md-8">
      <h2>Shop</h2>
    </div>
    <div class="col-md-4 text-end">
      <form class="d-flex" method="get" action="shop.php">
        <input name="q" class="form-control me-2" placeholder="Search products..." value="<?php echo h($q); ?>">
        <button class="btn btn-primary">Search</button>
      </form>
    </div>
  </div>

  <div class="row g-4">
    <?php if ($res && mysqli_num_rows($res)): while($p = mysqli_fetch_assoc($res)): 
        $img = $p['image'] ?: 'images/placeholder.png';
        if (strpos($img, '/') === false) $img = 'images/' . $img;
    ?>
      <div class="col-md-4">
        <div class="product-card">
          <img src="<?php echo h($img); ?>" alt="<?php echo h($p['title']); ?>" class="product-img">
          <div class="p-3">
            <h5><?php echo h($p['title']); ?></h5>
            <p class="price"><?php echo 'RM ' . number_format((float)$p['price'],2); ?></p>
            <div class="d-grid gap-2">
              <a href="product.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-outline-dark btn-sm">View Details</a>
              <a href="cart.php?add=<?php echo (int)$p['id']; ?>" class="btn btn-primary btn-sm">Add to Cart</a>
            </div>
          </div>
        </div>
      </div>
    <?php endwhile; else: ?>
      <div class="col-12"><p class="text-muted">No products found.</p></div>
    <?php endif; ?>
  </div>

  <!-- Pagination -->
  <nav class="mt-4">
    <ul class="pagination justify-content-center">
      <?php for ($i=1; $i <= $totalPages; $i++): 
         $params = $_GET; $params['page'] = $i; $url = 'shop.php?' . http_build_query($params);
      ?>
        <li class="page-item <?php echo $i== $page ? 'active' : ''; ?>">
          <a class="page-link" href="<?php echo h($url); ?>"><?php echo $i; ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>
</div>

<?php include 'footer.php'; ?>
</body>
</html>