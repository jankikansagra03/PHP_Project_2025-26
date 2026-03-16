<?php
// ============================================================
// mail_content.php — PHP_Project_2025-26
// HTML email body templates for all notification types.
// Include this file wherever you need to send emails.
// Usage: $body = getOtpEmailBody($otp);
//        sendEmail($to, 'Password Reset OTP', $body);
// ============================================================

// ---- Shared inline CSS for all email templates ----
function _emailStyles()
{
    return '
        body { font-family: "Segoe UI", Arial, sans-serif; background:#f4f7f7; margin:0; padding:0; }
        .email-wrapper { max-width:620px; margin:40px auto; background:#ffffff;
            border-radius:10px; border:1px solid #e5e7eb;
            box-shadow:0 4px 12px rgba(0,0,0,0.08); overflow:hidden; }
        .header { background:#0d9488; padding:18px; text-align:center;
            color:white; font-size:22px; font-weight:700; }
        .content { padding:25px 30px; color:#0f3d3a; font-size:16px; }
        .highlight-box { margin:20px auto; padding:14px 0; width:60%;
            text-align:center; background:#d4af37; border-radius:8px;
            color:white; font-size:26px; font-weight:bold; letter-spacing:5px; }
        .info-table { width:100%; border-collapse:collapse; margin:16px 0; }
        .info-table td { padding:8px 12px; border-bottom:1px solid #e5e7eb; font-size:15px; }
        .info-table td:first-child { font-weight:600; color:#0d9488; width:40%; }
        .status-badge { display:inline-block; padding:4px 14px; border-radius:20px;
            font-size:13px; font-weight:600; background:#0d9488; color:white; }
        .note { font-size:14px; color:#d97706; margin-top:10px; }
        .footer { margin-top:24px; padding:16px; text-align:center;
            font-size:12px; color:#6b7280; background:#f1f5f9; }
    ';
}

// ---- Helper: wrap content in the standard email shell ----
function _wrapEmail($headerTitle, $bodyContent)
{
    $styles = _emailStyles();
    return "
    <html>
    <head><style>{$styles}</style></head>
    <body>
        <div class='email-wrapper'>
            <div class='header'>{$headerTitle}</div>
            <div class='content'>{$bodyContent}</div>
            <div class='footer'>This is an automated email from JK Store. Please do not reply.</div>
        </div>
    </body>
    </html>";
}

// ============================================================
// 1. OTP Email — Password Reset
// ============================================================
/**
 * @param int    $otp  The 6-digit OTP
 * @param string $name User's full name (optional)
 */
function getOtpEmailBody($otp, $name = 'User')
{
    return getForgotPasswordOtpEmailBody($otp, $name);
}

function getForgotPasswordOtpEmailBody($otp, $name = 'User')
{
    $safeName = htmlspecialchars($name ?: 'User');

    return "
    <html>
    <head>
        <style>
            body {
                margin: 0;
                padding: 24px 0;
                background: #f4f7ff;
                font-family: 'Segoe UI', Arial, sans-serif;
                color: #24324a;
            }

            .email-shell {
                max-width: 640px;
                margin: 0 auto;
                padding: 0 16px;
            }

            .email-card {
                background: #ffffff;
                border-radius: 24px;
                overflow: hidden;
                border: 1px solid #e3e8f8;
                box-shadow: 0 20px 45px rgba(102, 126, 234, 0.16);
            }

            .hero {
                padding: 36px 32px 28px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: #ffffff;
            }

            .eyebrow {
                display: inline-block;
                padding: 6px 12px;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.16);
                font-size: 12px;
                font-weight: 700;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .hero h1 {
                margin: 18px 0 10px;
                font-size: 30px;
                line-height: 1.2;
            }

            .hero p {
                margin: 0;
                font-size: 15px;
                line-height: 1.7;
                color: rgba(255, 255, 255, 0.9);
            }

            .content {
                padding: 32px;
            }

            .content p {
                margin: 0 0 16px;
                font-size: 15px;
                line-height: 1.7;
                color: #475569;
            }

            .otp-panel {
                margin: 28px 0;
                padding: 24px;
                border-radius: 20px;
                background: linear-gradient(180deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.12) 100%);
                border: 1px solid rgba(102, 126, 234, 0.16);
                text-align: center;
            }

            .otp-label {
                margin-bottom: 10px;
                font-size: 12px;
                font-weight: 700;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                color: #667eea;
            }

            .otp-box {
                display: inline-block;
                padding: 14px 26px;
                border-radius: 16px;
                background: #ffffff;
                color: #3b4ec9;
                font-size: 32px;
                font-weight: 800;
                letter-spacing: 0.34em;
                box-shadow: inset 0 0 0 1px rgba(102, 126, 234, 0.15);
            }

            .otp-meta {
                margin-top: 14px;
                font-size: 14px;
                color: #5b6477;
            }

            .info-card {
                margin-top: 24px;
                padding: 18px 20px;
                border-radius: 16px;
                background: #f8faff;
                border-left: 4px solid #667eea;
            }

            .info-card strong {
                color: #283b84;
            }

            .footer {
                padding: 0 32px 28px;
                font-size: 13px;
                line-height: 1.7;
                color: #7c879f;
            }
        </style>
    </head>
    <body>
        <div class='email-shell'>
            <div class='email-card'>
                <div class='hero'>
                    <span class='eyebrow'>JK Store Security</span>
                    <h1>Password Reset OTP</h1>
                    <p>Your account protection matters. Use the one-time code below to continue your password reset securely.</p>
                </div>

                <div class='content'>
                    <p>Hello, <strong>{$safeName}</strong></p>
                    <p>We received a request to reset the password for your JK Store account. Enter this OTP on the verification screen to continue.</p>

                    <div class='otp-panel'>
                        <div class='otp-label'>One-Time Password</div>
                        <div class='otp-box'>{$otp}</div>
                        <div class='otp-meta'>Valid for 2 minutes only</div>
                    </div>

                    <div class='info-card'>
                        <strong>Security note:</strong> If you did not request this reset, you can ignore this email. Do not share this OTP with anyone.
                    </div>
                </div>

                <div class='footer'>This is an automated email from JK Store. Please do not reply to this message.</div>
            </div>
        </div>
    </body>
    </html>";
}

// ============================================================
// 2. Welcome Email — After Registration
// ============================================================
/**
 * @param string $name  User's full name
 * @param string $email User's email address
 */
function getWelcomeEmailBody($name, $email)
{
    $body = "
        <p>Hello, <strong>" . htmlspecialchars($name) . "</strong>!</p>
        <p>Welcome to <strong>JK Store</strong>! Your account has been created successfully.</p>
        <table class='info-table'>
            <tr><td>Name</td><td>" . htmlspecialchars($name) . "</td></tr>
            <tr><td>Email</td><td>" . htmlspecialchars($email) . "</td></tr>
        </table>
        <p>You can now log in and start shopping. Explore our latest products, deals, and offers!</p>
        <p class='note'><strong>Tip:</strong> Verify your email to unlock all features.</p>
    ";
    return _wrapEmail('Welcome to JK Store! 🎉', $body);
}

// ============================================================
// 3. Order Confirmation Email — After Placing an Order
// ============================================================
/**
 * @param array $order  Associative array with order details
 *                      (order_number, delivery_name, delivery_address,
 *                       subtotal, discount, shipping_fee, total_amount,
 *                       payment_method, order_status)
 * @param array $items  Array of order items
 *                      (product_name, quantity, price, subtotal)
 */
function getOrderConfirmationBody($order, $items)
{
    $itemRows = '';
    foreach ($items as $item) {
        $itemRows .= "
            <tr>
                <td>" . htmlspecialchars($item['product_name']) . "</td>
                <td style='text-align:center'>" . (int)$item['quantity'] . "</td>
                <td style='text-align:right'>₹" . number_format($item['price'], 2) . "</td>
                <td style='text-align:right'>₹" . number_format($item['subtotal'], 2) . "</td>
            </tr>";
    }

    $body = "
        <p>Hello, <strong>" . htmlspecialchars($order['delivery_name']) . "</strong>!</p>
        <p>Thank you for your order. Here are your order details:</p>

        <table class='info-table'>
            <tr><td>Order Number</td><td><strong>" . htmlspecialchars($order['order_number']) . "</strong></td></tr>
            <tr><td>Delivery Address</td><td>" . htmlspecialchars($order['delivery_address']) . "</td></tr>
            <tr><td>Payment Method</td><td>" . htmlspecialchars(strtoupper($order['payment_method'])) . "</td></tr>
            <tr><td>Order Status</td><td><span class='status-badge'>" . htmlspecialchars($order['order_status']) . "</span></td></tr>
        </table>

        <p><strong>Items Ordered:</strong></p>
        <table style='width:100%;border-collapse:collapse;font-size:14px;'>
            <thead>
                <tr style='background:#f1f5f9;'>
                    <th style='padding:8px;text-align:left;'>Product</th>
                    <th style='padding:8px;text-align:center;'>Qty</th>
                    <th style='padding:8px;text-align:right;'>Price</th>
                    <th style='padding:8px;text-align:right;'>Subtotal</th>
                </tr>
            </thead>
            <tbody>{$itemRows}</tbody>
        </table>

        <table class='info-table' style='margin-top:16px;'>
            <tr><td>Subtotal</td><td style='text-align:right;'>₹" . number_format($order['subtotal'], 2) . "</td></tr>
            <tr><td>Discount</td><td style='text-align:right;color:#16a34a;'>- ₹" . number_format($order['discount'], 2) . "</td></tr>
            <tr><td>Shipping</td><td style='text-align:right;'>₹" . number_format($order['shipping_fee'], 2) . "</td></tr>
            <tr><td><strong>Total</strong></td><td style='text-align:right;'><strong>₹" . number_format($order['total_amount'], 2) . "</strong></td></tr>
        </table>

        <p class='note'>We will notify you when your order is shipped.</p>
    ";
    return _wrapEmail('Order Confirmed — #' . htmlspecialchars($order['order_number']), $body);
}

// ============================================================
// 4. Order Status Update Email
// ============================================================
/**
 * @param array $order  Associative array with order details
 *                      (order_number, delivery_name, order_status,
 *                       total_amount, admin_notes)
 */
function getOrderStatusBody($order)
{
    $notes = !empty($order['admin_notes'])
        ? "<p><strong>Note from JK Store:</strong> " . htmlspecialchars($order['admin_notes']) . "</p>"
        : '';

    $body = "
        <p>Hello, <strong>" . htmlspecialchars($order['delivery_name']) . "</strong>!</p>
        <p>Your order status has been updated. Here are the details:</p>

        <table class='info-table'>
            <tr><td>Order Number</td><td><strong>" . htmlspecialchars($order['order_number']) . "</strong></td></tr>
            <tr><td>New Status</td><td><span class='status-badge'>" . htmlspecialchars($order['order_status']) . "</span></td></tr>
            <tr><td>Order Total</td><td>₹" . number_format($order['total_amount'], 2) . "</td></tr>
        </table>

        {$notes}
        <p class='note'>If you have any questions, please contact our support team.</p>
    ";
    return _wrapEmail('Order Update — #' . htmlspecialchars($order['order_number']), $body);
}
