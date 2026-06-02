-- ============================================================
-- NAPS PHOTOGRAPHERS - Complete Database Schema
-- Import this file into phpMyAdmin
-- ============================================================

CREATE DATABASE IF NOT EXISTS naps_photographers CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE naps_photographers;

-- ============================================================
-- USERS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') DEFAULT 'customer',
    is_blocked TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- SERVICES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    short_desc VARCHAR(255),
    price DECIMAL(10,2) NOT NULL,
    duration VARCHAR(50),
    image_url VARCHAR(255),
    icon VARCHAR(50) DEFAULT 'camera',
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- PACKAGES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    features TEXT,
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- BOOKINGS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_ref VARCHAR(20) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(150) NOT NULL,
    customer_phone VARCHAR(20),
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    message TEXT,
    status ENUM('pending','approved','rejected','rescheduled','completed','cancelled') DEFAULT 'pending',
    payment_status ENUM('unpaid','paid','failed','refunded') DEFAULT 'unpaid',
    total_price DECIMAL(10,2),
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- PAYMENTS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    transaction_ref VARCHAR(50) UNIQUE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    card_last_four VARCHAR(4),
    card_holder VARCHAR(100),
    payment_method VARCHAR(50) DEFAULT 'card',
    status ENUM('pending','success','failed','refunded') DEFAULT 'pending',
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- GALLERY TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150),
    caption TEXT,
    image_url VARCHAR(255) NOT NULL,
    category VARCHAR(50) DEFAULT 'general',
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- REVIEWS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    reviewer_name VARCHAR(100) NOT NULL,
    reviewer_email VARCHAR(150),
    rating INT DEFAULT 5,
    review_text TEXT NOT NULL,
    is_approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- NOTIFICATIONS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('booking','payment','system','alert') DEFAULT 'system',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- HOMEPAGE CONTENT TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS homepage_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_key VARCHAR(50) UNIQUE NOT NULL,
    section_title VARCHAR(200),
    section_content TEXT,
    section_image VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Admin user  (password: Admin@1234  — bcrypt hash of 'password' for demo)
INSERT INTO users (full_name, email, phone, password, role) VALUES
('Studio Admin', 'admin@napsphotographers.com', '+1-800-627-7746', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Demo customer  (password: password)
INSERT INTO users (full_name, email, phone, password, role) VALUES
('Alex Johnson', 'alex@example.com', '+1-555-0100', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer');

-- Services
INSERT INTO services (name, description, short_desc, price, duration, image_url, icon, sort_order) VALUES
('Wedding Photography', 'Capture your most precious moments with our award-winning wedding photography. We tell your unique love story through breathtaking imagery, from intimate ceremonies to grand celebrations.', 'Complete wedding day coverage', 2500.00, '8-12 hours', 'https://images.unsplash.com/photo-1519741497674-611481863552?w=600', 'heart', 1),
('Portrait Sessions', 'Professional portrait photography tailored to reveal your authentic self. Our studio is equipped with state-of-the-art lighting to create stunning, magazine-quality portraits.', 'Individual & family portraits', 350.00, '2 hours', 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=600', 'user', 2),
('Corporate Events', 'Elevate your brand with professional corporate event photography. We cover conferences, product launches, galas, and everything in between with precision and discretion.', 'Professional event coverage', 800.00, '4-6 hours', 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=600', 'briefcase', 3),
('Newborn & Family', 'Tender, timeless imagery of life\'s most precious first moments. Our gentle approach ensures comfort for babies and parents while creating heirloom-quality photographs.', 'Newborn & family sessions', 450.00, '3 hours', 'https://images.unsplash.com/photo-1544928147-79a2dbc1f389?w=600', 'users', 4),
('Commercial Photography', 'High-impact commercial and product photography that drives results. From e-commerce catalogs to advertising campaigns, we deliver images that sell.', 'Product & brand photography', 600.00, '4 hours', 'https://images.unsplash.com/photo-1492691527719-9d1e07e534b4?w=600', 'package', 5),
('Aerial Photography', 'Stunning drone photography and videography that offers a breathtaking perspective. Licensed operators, fully insured, and FAA compliant.', 'Drone & aerial imagery', 750.00, '3 hours', 'https://images.unsplash.com/photo-1473968512647-3e447244af8f?w=600', 'send', 6);

-- Packages
INSERT INTO packages (name, description, price, features, is_featured) VALUES
('Essentials', 'Perfect for intimate occasions and solo sessions', 299.00, '2-hour session|50 edited digital images|Online gallery|1 photographer|Basic retouching', 0),
('Signature', 'Our most popular package for weddings and events', 1499.00, '6-hour session|300 edited digital images|Online gallery|2 photographers|Premium retouching|Engagement shoot|USB drive delivery', 1),
('Premium Elite', 'The ultimate photography experience, no compromises', 2999.00, 'Full-day coverage (12 hrs)|Unlimited edited images|Luxury photo album|2 lead photographers|Advanced retouching|Drone footage|Same-week preview|Priority booking', 0);

-- Gallery
INSERT INTO gallery (title, caption, image_url, category, sort_order) VALUES
('Golden Hour Wedding', 'Sofia & James | Malibu Estate', 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=600', 'wedding', 1),
('Studio Portrait', 'Fashion editorial collection', 'https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?w=600', 'portrait', 2),
('Corporate Gala', 'TechVision Annual Awards 2024', 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=600', 'corporate', 3),
('Newborn Magic', 'Baby Harper | Day 7', 'https://images.unsplash.com/photo-1544928147-79a2dbc1f389?w=600', 'family', 4),
('Aerial Vista', 'Pacific Coast Highway shoot', 'https://images.unsplash.com/photo-1473968512647-3e447244af8f?w=600', 'aerial', 5),
('Bridal Prep', 'Emma morning preparation', 'https://images.unsplash.com/photo-1519741497674-611481863552?w=600', 'wedding', 6),
('Model Portfolio', 'High fashion outdoor collection', 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=600', 'portrait', 7),
('Product Launch', 'NovaTech X1 unveiling', 'https://images.unsplash.com/photo-1492691527719-9d1e07e534b4?w=600', 'commercial', 8),
('Family Session', 'The Williams family reunion', 'https://images.unsplash.com/photo-1609220136736-443140cffec6?w=600', 'family', 9);

-- Reviews
INSERT INTO reviews (reviewer_name, reviewer_email, rating, review_text, is_approved) VALUES
('Sarah M.', 'sarah@example.com', 5, 'Naps Photographers captured our wedding day beyond our wildest expectations. Every emotion, every detail preserved forever. We cry tears of joy every time we look at our album.', 1),
('David Chen', 'david@example.com', 5, 'The corporate headshots they produced elevated our entire team professional image. The attention to detail and lighting mastery is unmatched. Worth every penny.', 1),
('Emma Rodriguez', 'emma@example.com', 5, 'Our newborn session was magical. They were so patient and gentle with our baby girl. The photos are absolute treasures we will have forever.', 1),
('Marcus Williams', 'marcus@example.com', 5, 'Booked the Signature package for our anniversary shoot. The results were magazine-worthy. Professional, creative, and incredibly talented team.', 1);

-- Homepage Content
INSERT INTO homepage_content (section_key, section_title, section_content, section_image) VALUES
('hero_tagline', 'Where Every Frame Tells Your Story', 'Award-winning photography studio crafting timeless imagery for life most meaningful moments.', ''),
('about_title', 'About Naps Photographers', 'Founded in 2015, Naps Photographers has established itself as one of the premier photography studios in the region. Our team of award-winning photographers brings over 50 years of combined experience to every session. We believe photography is more than capturing images — it is about preserving emotions, telling stories, and creating heirlooms that transcend generations.', 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=800'),
('contact_address', 'Studio Address', '142 Artisan Boulevard, Creative District, Los Angeles, CA 90210', ''),
('contact_phone', 'Phone', '+1 (800) NAPS-PHO', ''),
('contact_email', 'Email', 'hello@napsphotographers.com', '');

-- Demo booking
INSERT INTO bookings (booking_ref, user_id, service_id, customer_name, customer_email, customer_phone, booking_date, booking_time, message, status, payment_status, total_price)
VALUES ('NAPS-2024-0001', 2, 1, 'Alex Johnson', 'alex@example.com', '+1-555-0100', DATE_ADD(CURDATE(), INTERVAL 14 DAY), '10:00:00', 'Looking forward to our big day coverage!', 'approved', 'unpaid', 2500.00);

-- Demo notification
INSERT INTO notifications (user_id, title, message, type)
VALUES (2, 'Booking Approved!', 'Your Wedding Photography booking has been approved. We look forward to working with you!', 'booking');
