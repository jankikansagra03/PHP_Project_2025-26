<?php
//Step-1 : Create connection
$con = mysqli_connect("localhost", "root", "",);

if ($con) {
    // echo "Connection successful";
} else {
    echo "Error in connection";
}

//step-3: select database
try {
    mysqli_select_db($con, "PHP_Project_25_261");
} catch (Exception) {
    echo "Error in connecting with DB";
}

// *********************REGISTRATION TABLE*************** */
// $q = "create table registration (
//     id int auto_increment primary key,
//     fullname char(50),
//     email varchar(50) unique,
//     password varchar(25),
//     gender char(6),
//     mobile bigint(10),
//     profile_picture varchar(100),
//     address text,
//     status char(8) default 'Inactive',
//     role char(10) default 'User',
//     token text
//     )";

// *********************ADDRESS TABLE*************** */
// $q = "CREATE TABLE IF NOT EXISTS addresses (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     user_id VARCHAR(50) NOT NULL,
//     name VARCHAR(100) NOT NULL,
//     email VARCHAR(100) NOT NULL,
//     mobile VARCHAR(15) NOT NULL,
//     address TEXT NOT NULL,
//     FOREIGN KEY (user_id) REFERENCES registration(email) ON DELETE CASCADE
// )";


// *********************CONTACT US TABLE*************** */

// $q = "create table contact_us(
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     name VARCHAR(100) NOT NULL,
//     email VARCHAR(100) NOT NULL,
//     subject VARCHAR(150) NOT NULL,
//     message TEXT NOT NULL,
//     reply TEXT,
//     status VARCHAR(20) DEFAULT 'Pending',
//     reply_date TIMESTAMP NULL,
//     submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
// )";

// ********************PASSWORD TOKEN TABLE*************** */
// $q = "CREATE TABLE password_token (id INT AUTO_INCREMENT PRIMARY KEY, email VARCHAR(255) NOT NULL, otp INT(6),created_at DATETIME NOT NULL,expires_at DATETIME NOT NULL, otp_attempts INT NOT NULL,last_resend TIMESTAMP DEFAULT CURRENT_TIMESTAMP)";
// try {
//     if ($con->query($q)) {
//         echo "Table Created Successfully";
//     }
// } catch (Exception $e) {
//     echo "Error in Creating Table" . $e->getMessage();
// }

//*******************OFFERS*************** */
// $q= "CREATE TABLE offers (
//   id INT AUTO_INCREMENT PRIMARY KEY,
//   code VARCHAR(100) NOT NULL UNIQUE,                -- coupon / promo code
//   discount_type ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
//   discount_value DECIMAL(10,2) NOT NULL,            -- if percent => store percentage (e.g. 20.00). if fixed => fixed amount.
//   min_order_amount DECIMAL(10,2) DEFAULT NULL,      -- minimum order value to apply the offer (nullable)
//   max_applicable_amount DECIMAL(10,2) DEFAULT NULL, -- maximum amount on which discount is calculated (nullable)
//   max_discount_amount DECIMAL(10,2) DEFAULT NULL,   -- maximum discount payable (cap)
//   valid_from DATETIME DEFAULT NULL,
//   valid_to DATETIME DEFAULT NULL,
//   usage_limit INT DEFAULT NULL,                     -- total number of times offer can be used (nullable)
//   per_user_limit INT DEFAULT NULL,                  -- how many times a single user can use (nullable)
//   status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
//   description VARCHAR(500) DEFAULT NULL
// )";


//*******************CATEGORIES*************** */

// $q="CREATE TABLE categories (
//   id INT AUTO_INCREMENT PRIMARY KEY,
//   category_name VARCHAR(150) NOT NULL,
//   status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active'
// )";

//*******************PRODUCTS*************** */

// $q = "CREATE TABLE products (
//   id INT AUTO_INCREMENT PRIMARY KEY,
//   name VARCHAR(255) NOT NULL,
//   category_id INT NULL,
//   brand VARCHAR(100) DEFAULT NULL,
//   price DECIMAL(10,2) NOT NULL,
//   discount DECIMAL(10,2) DEFAULT 0.00,
//   final_price DECIMAL(10,2) AS (price - discount) STORED,
//   stock INT DEFAULT 0,
//   description TEXT DEFAULT NULL,
//   long_description LONGTEXT DEFAULT NULL,
//   image VARCHAR(255) DEFAULT NULL,
//   gallery_images JSON DEFAULT NULL,         -- JSON array of image filenames/URLs
//   status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
//   CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
// )";


// *********** CART ******************

// $q = "CREATE TABLE IF NOT EXISTS cart (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     user_email VARCHAR(255) NOT NULL,
//     product_id INT NOT NULL,
//     quantity INT NOT NULL DEFAULT 1,
//     added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

//     -- Link to your user table
//     FOREIGN KEY (user_email) REFERENCES registration(email) ON DELETE CASCADE,
//     FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE

//     -- Note: We assume you have a 'products' table. 
//     -- If so, you should also add: FOREIGN KEY (product_id) REFERENCES products(id)
// );";


// ***************** Wishlist *********************
// $q = "CREATE TABLE IF NOT EXISTS wishlist (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     user_email VARCHAR(255) NOT NULL,
//     product_id INT NOT NULL,
//     added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

//     -- Links to your user and product tables
//     FOREIGN KEY (user_email) REFERENCES registration(email) ON DELETE CASCADE,
//     FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,

//     -- ensure a user can't add the same product twice
//     UNIQUE KEY unique_item (user_email, product_id) 
// )";

// ***************** SITE PAGES *********************

// $q= "CREATE TABLE `site_pages` (
//   `id` int NOT NULL AUTO_INCREMENT,
//   `page_slug` varchar(50) NOT NULL UNIQUE,
//   `page_title` varchar(200) NOT NULL,
//   `page_content` longtext NOT NULL,
//   `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
//   `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//   `updated_by` varchar(100) DEFAULT NULL,
//   PRIMARY KEY (`id`)
// ) ";
// ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";

// ***************** CONTACT INFO *********************

// $q= "CREATE TABLE `contact_info` (
//   `id` int NOT NULL AUTO_INCREMENT,
//   `company_name` varchar(200) NOT NULL,
//   `tagline` varchar(255) DEFAULT NULL,
//   `email` varchar(100) NOT NULL,
//   `phone` varchar(20) NOT NULL,
//   `alternate_phone` varchar(20) DEFAULT NULL,
//   `whatsapp_number` varchar(20) DEFAULT NULL,
//   `address` text NOT NULL,
//   `city` varchar(100) DEFAULT NULL,
//   `state` varchar(100) DEFAULT NULL,
//   `country` varchar(100) DEFAULT NULL,
//   `postal_code` varchar(20) DEFAULT NULL,
//   `facebook_url` varchar(255) DEFAULT NULL,
//   `twitter_url` varchar(255) DEFAULT NULL,
//   `instagram_url` varchar(255) DEFAULT NULL,
//   `linkedin_url` varchar(255) DEFAULT NULL,
//   `youtube_url` varchar(255) DEFAULT NULL,
//   `map_embed_url` text DEFAULT NULL,
//   `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//   PRIMARY KEY (`id`)
// ) ";

// ***************** TEAM MEMBERS *********************
// $q= "CREATE TABLE `team_members` (
//   `id` int NOT NULL AUTO_INCREMENT,
//   `name` varchar(100) NOT NULL,
//   `designation` varchar(100) NOT NULL,
//   `photo` varchar(255) DEFAULT 'images/team/default.jpg',
//   `bio` text DEFAULT NULL,
//   `facebook_url` varchar(255) DEFAULT NULL,
//   `twitter_url` varchar(255) DEFAULT NULL,
//   `linkedin_url` varchar(255) DEFAULT NULL,
//   `display_order` int DEFAULT 0,
//   `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
//   PRIMARY KEY (`id`)
// )";

// ***************** FAQ *********************
// $q = "CREATE TABLE `faq` (
//   `id` int NOT NULL AUTO_INCREMENT,
//   `question` varchar(500) NOT NULL,
//   `answer` text NOT NULL,
//   `category` varchar(100) DEFAULT 'General',
//   `display_order` int DEFAULT 0,
//   `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
//   `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
//   PRIMARY KEY (`id`)
// )";

// ============================ Orders table ============================
// $q = " 
// CREATE TABLE `orders` (
//   `id` int NOT NULL AUTO_INCREMENT,
//   `order_number` varchar(50) NOT NULL,
//   `user_email` varchar(255) NOT NULL,

//   -- Address Information
//   `delivery_name` varchar(100) NOT NULL,
//   `delivery_email` varchar(100) NOT NULL,
//   `delivery_mobile` varchar(15) NOT NULL,
//   `delivery_address` text NOT NULL,

//   -- Order Amounts
//   `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
//   `discount` decimal(10,2) NOT NULL DEFAULT '0.00',
//   `shipping_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
//   `total_amount` decimal(10,2) NOT NULL,

//   -- Payment Information
//   `payment_method` enum('razorpay','cod') NOT NULL DEFAULT 'cod',
//   `payment_status` enum('Pending','Paid','Failed','Refunded') NOT NULL DEFAULT 'Pending',
//   `razorpay_order_id` varchar(100) DEFAULT NULL,
//   `razorpay_payment_id` varchar(100) DEFAULT NULL,
//   `razorpay_signature` varchar(255) DEFAULT NULL,

//   -- Order Status
//   `order_status` enum('Pending','Confirmed','Processing','Shipped','Delivered','Cancelled','Returned') NOT NULL DEFAULT 'Pending',

//   -- Timestamps
//   `order_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
//   `payment_date` timestamp NULL DEFAULT NULL,
//   `shipped_date` timestamp NULL DEFAULT NULL,
//   `delivered_date` timestamp NULL DEFAULT NULL,
//   `cancelled_date` timestamp NULL DEFAULT NULL,

//   -- Additional Info
//   `cancellation_reason` text,
//   `admin_notes` text,

//   PRIMARY KEY (`id`),
//   UNIQUE KEY `order_number` (`order_number`),
//   KEY `idx_user_email` (`user_email`),
//   KEY `idx_order_status` (`order_status`),
//   KEY `idx_payment_status` (`payment_status`)
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";

// ============================ Order Items table ============================
// $q = "
// CREATE TABLE `order_items` (
//   `id` int NOT NULL AUTO_INCREMENT,
//   `order_id` int NOT NULL,
//   `product_id` int NOT NULL,
//   `product_name` varchar(255) NOT NULL,
//   `product_image` varchar(255) DEFAULT NULL,
//   `price` decimal(10,2) NOT NULL,
//   `discount` decimal(10,2) NOT NULL DEFAULT '0.00',
//   `quantity` int NOT NULL,
//   `subtotal` decimal(10,2) NOT NULL,
//   PRIMARY KEY (`id`),
//   KEY `order_id` (`order_id`),
//   KEY `product_id` (`product_id`),
//   CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";

// ============================ Reviews table ============================
// $q= "CREATE TABLE `reviews` (
//   `id` int NOT NULL AUTO_INCREMENT,
//   `product_id` int NOT NULL,
//   `user_email` varchar(255) NOT NULL,
//   `user_name` varchar(100) NOT NULL,
//   `rating` int NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
//   `title` varchar(200) NOT NULL,
//   `review` text NOT NULL,
//   `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
//   `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
//   `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//   PRIMARY KEY (`id`),
//   KEY `idx_product_id` (`product_id`),
//   KEY `idx_user_email` (`user_email`),
//   KEY `idx_status` (`status`),
//   CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";


// if (mysqli_query($con, $q)) {
//     echo "Table Created";
// } else {
//     echo "Error in creating table";
// }
// Step-2: create database this is onetime process so we can comment it after creating database

// $create_db = "create database PHP_Project_25_261";

// if (mysqli_query($con, $create_db)) {
//     echo "Database created";
// } else {
//     echo "error in creating database";
// }