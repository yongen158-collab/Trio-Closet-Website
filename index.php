<?php
require_once 'config.php';
$conn = db_connect();
?>

<!DOCTYPE html>
<html>
<head>
    <title>TRIO CLOSET -Premium Fashion Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>

<!-- Hero Slider Section -->
        <section class="hero-slider">
  <div id="mainSlider" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
    <!-- Indicators -->
    <div class="carousel-indicators">
      <button type="button" data-bs-target="#mainSlider" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
      <button type="button" data-bs-target="#mainSlider" data-bs-slide-to="1" aria-label="Slide 2"></button>
    </div>

    <!-- Slides -->
    <div class="carousel-inner">
      <div class="carousel-item active">
        <img src="images/carousel1.jpeg" class="d-block w-100" alt="Slide 1">
        <div class="carousel-caption d-none d-md-block">
          <h5>Summer Collection 2026</h5>
          <p>Premium Fashion Collection</p>
        </div>
      </div>

      <div class="carousel-item">
        <img src="images/carousel2.jpeg" class="d-block w-100" alt="Slide 2">
        <div class="carousel-caption d-none d-md-block">
          <h5>Limited Time Offer</h5>
          <p>Up to 50% OFF</p>
        </div>
      </div>
    </div>

    <!-- Controls -->
    <button class="carousel-control-prev" type="button" data-bs-target="#mainSlider" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#mainSlider" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Next</span>
    </button>
  </div>
</section>

    <!-- Features Section -->
    <section class="features-section section-padding">
        <div class="container">
            <div class="section-header text-center mb-5">
            <br>
                <h4 class="section-subtitle">Why Choose Us</h4>
                <h2 class="section-title">Premium Services</h2>
                <p class="section-desc">Experience luxury shopping with our exclusive services</p>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <h3 class="feature-title">Free Shipping</h3>
                        <p class="feature-desc">Free delivery on all orders over $100. Fast and reliable shipping worldwide.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon">
                            <i class="fas fa-undo-alt"></i>
                        </div>
                        <h3 class="feature-title">Easy Returns</h3>
                        <p class="feature-desc">30-day return policy. Shop with confidence and return if not satisfied.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3 class="feature-title"> Affordable Price</h3>
                        <p class="feature-desc">Our store offers stylish and high-quality fashion items at affordable prices, 
                        with all products ranging from RM18 to RM88,.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Products Section -->
    <section class="products-section section-padding gray-bg">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h4 class="section-subtitle">Our Collection</h4>
                <h2 class="section-title">Featured Products</h2>
                <p class="section-desc">Carefully curated selection of premium fashion items</p>
            </div>

    <div class="row">

      <?php
      $res = $conn->query("SELECT * FROM producttbl ORDER BY product_created DESC LIMIT 4");
      while ($p = $res->fetch_assoc()):
      ?>
      <div class="col-lg-3 col-md-6 mb-4">
        <div class="product-card">
          <div class="product-image">
            <img src="<?php echo h($p['product_image']); ?>" alt="<?php echo h($p['product_name']); ?>" class="img-fluid">
            <div class="product-overlay">
              <a href="product.php?id=<?php echo h($p['product_id']); ?>" class="quick-view" title="View Details"><i class="fas fa-eye"></i></a>
              <a href="cart.php?add=<?php echo h($p['product_id']); ?>" class="add-to-cart" title="Add to Cart"><i class="fas fa-shopping-cart"></i></a>
            </div>
            <span class="product-badge new">New</span>
          </div>
          <div class="product-info">
            <h3 class="product-title"><?php echo h($p['product_name']); ?></h3>
            <div class="product-price"><span class="current-price"><?php echo formatRM($p['product_price']); ?></span></div>
            <div class="product-actions mt-2">
              <a href="product.php?id=<?php echo h($p['product_id']); ?>" class="btn btn-sm btn-outline-dark w-100"><i class="fas fa-info-circle me-1"></i> View Details</a>
            </div>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>

    <div class="text-center mt-5">
      <a href="shop.php" class="btn btn-view-all">View All Products</a>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>
</body>
</html>