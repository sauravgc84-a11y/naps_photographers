<?php
// ============================================================
// index.php — Naps Photographers Homepage
// ============================================================
session_start();
require_once 'db.php';

// Fetch dynamic data
$services = $conn->query("SELECT * FROM services WHERE is_active=1 ORDER BY sort_order")->fetch_all(MYSQLI_ASSOC);
$gallery  = $conn->query("SELECT * FROM gallery WHERE is_active=1 ORDER BY sort_order LIMIT 9")->fetch_all(MYSQLI_ASSOC);
$reviews  = $conn->query("SELECT * FROM reviews WHERE is_approved=1 ORDER BY created_at DESC LIMIT 4")->fetch_all(MYSQLI_ASSOC);
$packages = $conn->query("SELECT * FROM packages WHERE is_active=1 ORDER BY price")->fetch_all(MYSQLI_ASSOC);
$about    = $conn->query("SELECT * FROM homepage_content WHERE section_key='about_title'")->fetch_assoc();

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Naps Photographers — Award-Winning Photography Studio</title>
<meta name="description" content="Premium photography studio in Sydney. Wedding, portrait, commercial and aerial photography.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- ===================== TOAST ===================== -->
<?php if ($flash): ?>
<div class="toast toast-<?= $flash['type'] ?>" id="toast">
  <?= htmlspecialchars($flash['message']) ?>
</div>
<script>setTimeout(()=>{document.getElementById('toast').classList.add('show');setTimeout(()=>document.getElementById('toast').classList.remove('show'),4000)},100);</script>
<?php endif; ?>

<!-- ===================== LOADER ===================== -->
<div class="loader" id="loader">
  <div class="loader-inner">
    <span class="loader-brand">NAPS</span>
    <div class="loader-bar"><div class="loader-fill"></div></div>
  </div>
</div>

<!-- ===================== NAV ===================== -->
<nav class="navbar" id="navbar">
  <div class="nav-container">
    <a href="#home" class="nav-logo">
      <span class="logo-text">NAPS</span>
      <span class="logo-sub">PHOTOGRAPHERS</span>
    </a>
    <ul class="nav-links" id="navLinks">
      <li><a href="#home" class="nav-link active">Home</a></li>
      <li><a href="#about" class="nav-link">About</a></li>
      <li><a href="#services" class="nav-link">Services</a></li>
      <li><a href="#gallery" class="nav-link">Gallery</a></li>
      <li><a href="#packages" class="nav-link">Packages</a></li>
      <li><a href="#booking" class="nav-link">Book Now</a></li>
      <li><a href="#contact" class="nav-link">Contact</a></li>
    </ul>
    <div class="nav-actions">
      <?php if (isLoggedIn()): ?>
        <a href="<?= isAdmin() ? 'admin/dashboard.php' : 'customer/customer_dashboard.php' ?>" class="btn-nav-outline">Dashboard</a>
        <a href="auth/logout.php" class="btn-nav">Logout</a>
      <?php else: ?>
        <a href="auth/login.php" class="btn-nav-outline">Login</a>
        <a href="auth/signup.php" class="btn-nav">Get Started</a>
      <?php endif; ?>
    </div>
    <button class="hamburger" id="hamburger" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

<!-- ===================== HERO ===================== -->
<section class="hero" id="home">
  <div class="hero-slider" id="heroSlider">
    <div class="slide active" style="background-image:url('https://images.unsplash.com/photo-1519741497674-611481863552?w=1600')"></div>
    <div class="slide" style="background-image:url('https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=1600')"></div>
    <div class="slide" style="background-image:url('https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=1600')"></div>
    <div class="slide" style="background-image:url('https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1600')"></div>
  </div>
  <div class="hero-overlay"></div>
  <div class="hero-content">
    <div class="hero-badge">EST. 2015 · Sydney</div>
    <h1 class="hero-title">
      <span class="line">Where Every</span>
      <span class="line italic">Frames Captures</span>
      <span class="line">Your Story</span>
    </h1>
    <p class="hero-desc">"You will never get old in pictures." <br>Award-winning photography studio crafting timeless imagery<br>for life's most meaningful moments.</p>
    <div class="hero-cta">
      <a href="#booking" class="btn-gold">Book a Session</a>
      <a href="#gallery" class="btn-ghost">View Portfolio</a>
    </div>
    <div class="hero-stats">
      <div class="stat"><span class="num">1,200+</span><span class="lbl">Sessions</span></div>
      <div class="stat-divider"></div>
      <div class="stat"><span class="num">98%</span><span class="lbl">Satisfaction</span></div>
      <div class="stat-divider"></div>
      <div class="stat"><span class="num">12</span><span class="lbl">Awards</span></div>
    </div>
  </div>
  <div class="slider-dots" id="sliderDots"></div>
  <button class="slider-arrow prev" id="prevSlide">&#8249;</button>
  <button class="slider-arrow next" id="nextSlide">&#8250;</button>
</section>

<!-- ===================== ABOUT ===================== -->
<section class="about section" id="about">
  <div class="container">
    <div class="about-grid">
      <div class="about-images reveal">
        <div class="about-img-main">
          <img src="https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=700" alt="Studio">
        </div>
        <div class="about-img-accent">
          <img src="https://images.unsplash.com/photo-1452587925148-ce544e77e70d?w=400" alt="Camera">
          <div class="about-exp-badge">
            <span class="num">9+</span>
            <span class="lbl">Years of<br>Excellence</span>
          </div>
        </div>
      </div>
      <div class="about-content reveal">
        <div class="section-label">Our Story</div>
        <h2 class="section-title"><?= htmlspecialchars($about['section_title'] ?? 'About Naps Photographers') ?></h2>
        <p class="about-text"><?= nl2br(htmlspecialchars($about['section_content'] ?? '')) ?></p>
        <div class="about-features">
          <div class="feature">
            <div class="feature-icon">✦</div>
            <div>
              <strong>Award-Winning Team</strong>
              <p>Recognized internationally for photographic excellence</p>
            </div>
          </div>
          <div class="feature">
            <div class="feature-icon">✦</div>
            <div>
              <strong>State-of-the-Art Studio</strong>
              <p>4,000 sq ft professional space with every lighting scenario</p>
            </div>
          </div>
          <div class="feature">
            <div class="feature-icon">✦</div>
            <div>
              <strong>Tailored Experience</strong>
              <p>Every session customized to your unique vision</p>
            </div>
          </div>
        </div>
        <a href="#booking" class="btn-gold">Work With Us</a>
      </div>
    </div>
  </div>
</section>

<!-- ===================== SERVICES ===================== -->
<section class="services section section-dark" id="services">
  <div class="container">
    <div class="section-header reveal">
      <div class="section-label">What We Offer</div>
      <h2 class="section-title">Our Services</h2>
      <p class="section-desc">From intimate portraits to grand celebrations, we bring artistry and technical mastery to every commission.</p>
    </div>
    <div class="services-grid">
      <?php foreach ($services as $s): ?>
      <div class="service-card reveal">
        <div class="service-img">
          <img src="<?= htmlspecialchars($s['image_url']) ?>" alt="<?= htmlspecialchars($s['name']) ?>" loading="lazy">
          <div class="service-overlay">
            <a href="#booking" class="btn-ghost-sm">Book This Service</a>
          </div>
        </div>
        <div class="service-body">
          <div class="service-price"><?= formatPrice($s['price']) ?><span>/session</span></div>
          <h3><?= htmlspecialchars($s['name']) ?></h3>
          <p><?= htmlspecialchars($s['short_desc']) ?></p>
          <div class="service-duration">⏱ <?= htmlspecialchars($s['duration']) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===================== GALLERY ===================== -->
<section class="gallery-section section" id="gallery">
  <div class="container">
    <div class="section-header reveal">
      <div class="section-label">Our Work</div>
      <h2 class="section-title">Portfolio</h2>
      <p class="section-desc">A curated selection from our most celebrated commissions</p>
    </div>
    <div class="gallery-filter reveal">
      <button class="filter-btn active" data-filter="all">All</button>
      <button class="filter-btn" data-filter="wedding">Wedding</button>
      <button class="filter-btn" data-filter="portrait">Portrait</button>
      <button class="filter-btn" data-filter="corporate">Corporate</button>
      <button class="filter-btn" data-filter="family">Family</button>
    </div>
    <div class="gallery-grid" id="galleryGrid">
      <?php foreach ($gallery as $img): ?>
      <div class="gallery-item reveal" data-cat="<?= htmlspecialchars($img['category']) ?>">
        <img src="<?= htmlspecialchars($img['image_url']) ?>" alt="<?= htmlspecialchars($img['title']) ?>" loading="lazy">
        <div class="gallery-overlay">
          <div class="gallery-info">
            <h4><?= htmlspecialchars($img['title']) ?></h4>
            <p><?= htmlspecialchars($img['caption']) ?></p>
          </div>
          <button class="gallery-zoom" onclick="openLightbox('<?= htmlspecialchars($img['image_url']) ?>','<?= htmlspecialchars($img['title']) ?>')">&#x2316;</button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox" onclick="closeLightbox()">
  <button class="lightbox-close" onclick="closeLightbox()">✕</button>
  <img id="lightboxImg" src="" alt="">
  <div id="lightboxCaption" class="lightbox-caption"></div>
</div>

<!-- ===================== PACKAGES ===================== -->
<section class="packages section section-dark" id="packages">
  <div class="container">
    <div class="section-header reveal">
      <div class="section-label">Pricing</div>
      <h2 class="section-title">Packages &amp; Pricing</h2>
      <p class="section-desc">Transparent pricing. Exceptional value. No hidden fees.</p>
    </div>
    <div class="packages-grid">
      <?php foreach ($packages as $pkg): ?>
      <?php $features = explode('|', $pkg['features']); ?>
      <div class="package-card reveal <?= $pkg['is_featured'] ? 'featured' : '' ?>">
        <?php if ($pkg['is_featured']): ?>
          <div class="featured-badge">Most Popular</div>
        <?php endif; ?>
        <div class="pkg-header">
          <h3><?= htmlspecialchars($pkg['name']) ?></h3>
          <p><?= htmlspecialchars($pkg['description']) ?></p>
        </div>
        <div class="pkg-price">
          <span class="currency">$</span>
          <span class="amount"><?= number_format($pkg['price']) ?></span>
        </div>
        <ul class="pkg-features">
          <?php foreach ($features as $feat): ?>
            <li><span class="check">✓</span> <?= htmlspecialchars(trim($feat)) ?></li>
          <?php endforeach; ?>
        </ul>
        <a href="#booking" class="<?= $pkg['is_featured'] ? 'btn-gold' : 'btn-ghost' ?> pkg-btn">Book This Package</a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===================== BOOKING ===================== -->
<section class="booking section" id="booking">
  <div class="container">
    <div class="booking-grid">
      <div class="booking-info reveal">
        <div class="section-label">Get In Touch</div>
        <h2 class="section-title">Book Your Session</h2>
        <p>Every great photograph begins with a conversation. Tell us about your vision and we'll make it a reality.</p>
        <div class="booking-steps">
          <div class="step"><span class="step-n">01</span><div><strong>Submit Request</strong><p>Fill in the form with your requirements</p></div></div>
          <div class="step"><span class="step-n">02</span><div><strong>Confirmation</strong><p>We'll confirm within 24 hours</p></div></div>
          <div class="step"><span class="step-n">03</span><div><strong>Session Day</strong><p>We arrive prepared to deliver excellence</p></div></div>
        </div>
      </div>
      <div class="booking-form-wrap reveal">
        <?php if (!isLoggedIn()): ?>
        <div class="booking-login-notice">
          <div class="notice-icon">
          <i class="ri-user-3-line"></i>
          </div>
          <h3>Sign in to Book</h3>
          <p>Please create an account or log in to submit a booking request.</p>
          <div style="display:flex;gap:12px;justify-content:center;margin-top:20px;">
            <a href="auth/login.php" class="btn-gold">Login</a>
            <a href="auth/signup.php" class="btn-ghost">Sign Up Free</a>
          </div>
        </div>
        <?php else: ?>
        <form class="booking-form" id="bookingForm" method="POST" action="booking/book.php">
          <div class="form-row-2">
            <div class="form-group">
              <label>Full Name</label>
              <input type="text" name="customer_name" value="<?= htmlspecialchars($_SESSION['full_name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" name="customer_email" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>" required>
            </div>
          </div>
          <div class="form-row-2">
            <div class="form-group">
              <label>Phone</label>
              <input type="tel" name="customer_phone" placeholder="+1 555 0100">
            </div>
            <div class="form-group">
              <label>Service</label>
              <select name="service_id" required>
                <option value="">Select a service</option>
                <?php foreach ($services as $s): ?>
                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?> — <?= formatPrice($s['price']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="form-row-2">
            <div class="form-group">
              <label>Preferred Date</label>
              <input type="date" name="booking_date" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
            </div>
            <div class="form-group">
              <label>Time Slot</label>
              <select name="booking_time" required>
                <option value="">Select time</option>
                <option value="09:00">9:00 AM</option>
                <option value="10:00">10:00 AM</option>
                <option value="11:00">11:00 AM</option>
                <option value="12:00">12:00 PM</option>
                <option value="13:00">1:00 PM</option>
                <option value="14:00">2:00 PM</option>
                <option value="15:00">3:00 PM</option>
                <option value="16:00">4:00 PM</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Message / Special Requests</label>
            <textarea name="message" rows="4" placeholder="Tell us about your vision, location preferences, or any special requirements..."></textarea>
          </div>
          <button type="submit" class="btn-gold btn-full">Submit Booking Request</button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- ===================== TESTIMONIALS ===================== -->
<section class="testimonials section section-dark" id="reviews">
  <div class="container">
    <div class="section-header reveal">
      <div class="section-label">Testimonials</div>
      <h2 class="section-title">What Our Clients Say</h2>
    </div>
    <div class="reviews-grid">
      <?php foreach ($reviews as $r): ?>
      <div class="review-card reveal">
        <div class="review-stars"><?= str_repeat('★', $r['rating']) ?></div>
        <p class="review-text">"<?= htmlspecialchars($r['review_text']) ?>"</p>
        <div class="reviewer">
          <div class="reviewer-avatar"><?= strtoupper(substr($r['reviewer_name'], 0, 1)) ?></div>
          <div><strong><?= htmlspecialchars($r['reviewer_name']) ?></strong></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <!-- Review submission -->
    <?php if (isLoggedIn() && !isAdmin()): ?>
    <div class="review-submit reveal">
      <h3>Share Your Experience</h3>
      <form method="POST" action="booking/submit_review.php" class="inline-review-form">
        <div class="form-row-2">
          <div class="form-group">
            <label>Your Rating</label>
            <select name="rating">
              <option value="5">★★★★★ Excellent</option>
              <option value="4">★★★★ Good</option>
              <option value="3">★★★ Average</option>
            </select>
          </div>
          <div class="form-group">
            <label>Your Name</label>
            <input type="text" name="reviewer_name" value="<?= htmlspecialchars($_SESSION['full_name'] ?? '') ?>" required>
          </div>
        </div>
        <div class="form-group">
          <label>Your Review</label>
          <textarea name="review_text" rows="3" placeholder="Share your experience with Naps Photographers..." required></textarea>
        </div>
        <button type="submit" class="btn-gold">Submit Review</button>
      </form>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ===================== CONTACT ===================== -->
<section class="contact section" id="contact">
  <div class="container">
    <div class="contact-grid">
      <div class="contact-info reveal">
        <div class="section-label">Find Us</div>
        <h2 class="section-title">Get In Touch</h2>
        <div class="contact-details">
          <div class="contact-item">
            <div class="ci-icon">📍</div>
            <div>
              <strong>Studio</strong>
              <p>70-74 The Boulevard, Strathfield<br>Sydney, NSW 2135</p>
            </div>
          </div>
          <div class="contact-item">
            <div class="ci-icon">📞</div>
            <div>
              <strong>Phone</strong>
              <p>+61 (042036394) NAPS-PHO<br>+61 627-7746</p>
            </div>
          </div>
          <div class="contact-item">
            <div class="ci-icon">✉️</div>
            <div>
              <strong>Email</strong>
              <p>enquiry@napsphotographers.com<br>bookings@napsphotographers.com</p>
            </div>
          </div>
          <div class="contact-item">
            <div class="ci-icon">🕐</div>
            <div>
              <strong>Hours</strong>
              <p>Mon–Fri: 9am – 7pm<br>Sat–Sun: 10am – 5pm</p>
            </div>
          </div>
        </div>
        <div class="social-links">
          <a href="#" class="social-link">Instagram</a>
          <a href="#" class="social-link">Facebook</a>
          <a href="#" class="social-link">Pinterest</a>
        </div>
      </div>
      <div class="contact-map reveal">
    <iframe
        src="https://www.google.com/maps?q=70-74%20The%20Boulevarde%20Strathfield%20NSW%202135&output=embed"
        width="100%"
        height="100%"
        style="border:0;"
        allowfullscreen=""
        loading="lazy">
    </iframe>
</div>
            <p>70-74 The Boulevarde<br>Strathfield, Sydney, NSW</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===================== FOOTER ===================== -->
<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <div class="footer-logo">NAPS<span>PHOTOGRAPHERS</span></div>
        <p>Creating timeless imagery that preserves your most precious moments for generations to come.</p>
        <div class="footer-social">
          <a href="#">Ig</a><a href="#">Fb</a><a href="#">Pt</a><a href="#">Li</a>
        </div>
      </div>
      <div class="footer-col">
        <h4>Services</h4>
        <ul>
          <?php foreach (array_slice($services, 0, 4) as $s): ?>
          <li><a href="#services"><?= htmlspecialchars($s['name']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Quick Links</h4>
        <ul>
          <li><a href="#about">About Studio</a></li>
          <li><a href="#gallery">Portfolio</a></li>
          <li><a href="#packages">Pricing</a></li>
          <li><a href="#booking">Book Session</a></li>
          <li><a href="auth/login.php">Client Login</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Contact</h4>
        <ul>
          <li>70-74 The Boulevarde,Strathfield NSW</li>
          <li>+61(800) 627-7746</li>
          <li>enquiry@napsphotographers.com</li>
          <li>Mon–Sun: 9am–7pm</li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> Naps Photographers. All rights reserved.</p>
      <p>Crafted with Love for memorable moments</p>
    </div>
  </div>
</footer>

<script src="assets/js/script.js"></script>
</body>
</html>
