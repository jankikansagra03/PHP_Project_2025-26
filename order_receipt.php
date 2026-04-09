<?php
session_start();
include_once 'db_config.php';

// Ensure user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_email = $_SESSION['user'];
$order_id = (int)($_GET['order_id'] ?? 0);

if ($order_id <= 0) {
    die("Invalid Order ID.");
}

// Fetch order details
$stmt = mysqli_prepare($con, "SELECT * FROM orders WHERE id = ? AND user_email = ?");
mysqli_stmt_bind_param($stmt, 'is', $order_id, $user_email);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if (!$res || mysqli_num_rows($res) === 0) {
    die("Order not found or access denied.");
}

$order = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

// Fetch order items
$item_stmt = mysqli_prepare($con, "SELECT * FROM order_items WHERE order_id = ?");
mysqli_stmt_bind_param($item_stmt, 'i', $order_id);
mysqli_stmt_execute($item_stmt);
$item_res = mysqli_stmt_get_result($item_stmt);
$items = [];
while ($row = mysqli_fetch_assoc($item_res)) {
    $items[] = $row;
}
mysqli_stmt_close($item_stmt);

// Format dates
$order_date = date('d M Y, h:i A', strtotime($order['order_date'] ?? $order['created_at']));
$delivered_date = $order['delivered_date'] ? date('d M Y, h:i A', strtotime($order['delivered_date'])) : 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - Order #<?= htmlspecialchars($order['order_number']) ?></title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1f7a8c;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border-light: #e2e8f0;
        }
        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            background: #f1f5f9;
            margin: 0;
            padding: 2rem;
            line-height: 1.6;
        }
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 3rem;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid var(--border-light);
            padding-bottom: 1.5rem;
            margin-bottom: 2rem;
        }
        .company-info {
            text-align: right;
        }
        .company-name {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary);
            margin: 0;
            letter-spacing: -0.5px;
        }
        .receipt-title {
            font-size: 2.2rem;
            font-weight: 800;
            color: #e2e8f0;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0 0 10px 0;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .info-box {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid var(--border-light);
        }
        .info-box h6 {
            margin: 0 0 10px 0;
            font-size: 0.85rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
        }
        .info-box p {
            margin: 0 0 5px 0;
            font-size: 0.95rem;
        }
        .info-box strong {
            color: var(--text-dark);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }
        th {
            background: #f8fafc;
            color: var(--text-muted);
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem;
            text-align: left;
            border-bottom: 2px solid var(--border-light);
        }
        td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-light);
            font-size: 0.95rem;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-container {
            width: 50%;
            margin-left: auto;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            font-size: 0.95rem;
            color: var(--text-muted);
        }
        .total-row.final {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text-dark);
            border-top: 2px solid var(--border-light);
            padding-top: 1rem;
            margin-top: 0.5rem;
        }
        .total-row.amount-paid {
            color: #16a34a;
            font-weight: 700;
        }
        .footer {
            margin-top: 3rem;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.9rem;
            border-top: 1px solid var(--border-light);
            padding-top: 1.5rem;
        }
        .print-btn-container {
            text-align: center;
            margin-bottom: 2rem;
        }
        .btn-print {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 24px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: inherit;
            transition: opacity 0.2s;
        }
        .btn-print:hover {
            opacity: 0.9;
        }

        /* Print Specific Styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .receipt-container {
                box-shadow: none;
                border-radius: 0;
                padding: 0;
                width: 100%;
                max-width: 100%;
            }
            .print-btn-container {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="print-btn-container">
        <button class="btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Print Receipt
        </button>
    </div>

    <div class="receipt-container">
        
        <!-- Header -->
        <div class="header">
            <div>
                <h1 class="receipt-title">Receipt</h1>
                <p style="margin:0; font-size:1.1rem; font-weight:600; color:var(--text-dark);">
                    Order #<?= htmlspecialchars($order['order_number']) ?>
                </p>
                <p style="margin:5px 0 0 0; color:var(--text-muted); font-size:0.9rem;">
                    Date: <?= $order_date ?>
                </p>
            </div>
            <div class="company-info">
                <h2 class="company-name">JK Store</h2>
                <p style="margin:5px 0 0 0; color:var(--text-muted); font-size:0.9rem;">
                    123 Commerce Avenue, Tech Park<br>
                    Mumbai, Maharashtra, 400001<br>
                    support@jkstore.com | +91 98765 43210
                </p>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="info-grid">
            <div class="info-box">
                <h6>Billed To / Delivered To:</h6>
                <p><strong><?= htmlspecialchars($order['delivery_name']) ?></strong></p>
                <p><?= nl2br(htmlspecialchars($order['delivery_address'])) ?></p>
                <p style="margin-top: 10px;">
                    <i class="fas fa-phone fa-sm"></i> <?= htmlspecialchars($order['delivery_mobile']) ?><br>
                    <i class="fas fa-envelope fa-sm"></i> <?= htmlspecialchars($order['delivery_email']) ?>
                </p>
            </div>
            <div class="info-box">
                <h6>Order Details:</h6>
                <table style="margin:0; border:none; width:100%;">
                    <tr>
                        <td style="padding:4px 0; border:none; color:var(--text-muted);">Payment Method:</td>
                        <td style="padding:4px 0; border:none; text-align:right;"><strong><?= strtoupper(htmlspecialchars($order['payment_method'])) ?></strong></td>
                    </tr>
                    <tr>
                        <td style="padding:4px 0; border:none; color:var(--text-muted);">Payment Status:</td>
                        <td style="padding:4px 0; border:none; text-align:right;"><strong><?= htmlspecialchars($order['payment_status']) ?></strong></td>
                    </tr>
                    <?php if(!empty($order['payment_txn_id'])): ?>
                    <tr>
                        <td style="padding:4px 0; border:none; color:var(--text-muted);">Transaction ID:</td>
                        <td style="padding:4px 0; border:none; text-align:right;"><strong><?= htmlspecialchars($order['payment_txn_id']) ?></strong></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td style="padding:4px 0; border:none; color:var(--text-muted);">Order Status:</td>
                        <td style="padding:4px 0; border:none; text-align:right;"><strong><?= htmlspecialchars($order['order_status']) ?></strong></td>
                    </tr>
                    <?php if($order['order_status'] === 'Delivered'): ?>
                    <tr>
                        <td style="padding:4px 0; border:none; color:var(--text-muted);">Delivered On:</td>
                        <td style="padding:4px 0; border:none; text-align:right;"><strong><?= $delivered_date ?></strong></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item Description</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $i = 1; 
                foreach ($items as $item): 
                    $item_total = $item['quantity'] * $item['price'];
                ?>
                <tr>
                    <td class="text-center" style="color:var(--text-muted);"><?= $i++ ?></td>
                    <td>
                        <strong style="display:block; margin-bottom:4px;"><?= htmlspecialchars($item['product_name']) ?></strong>
                    </td>
                    <td class="text-center"><?= (int)$item['quantity'] ?></td>
                    <td class="text-right">₹<?= number_format($item['price'], 2) ?></td>
                    <td class="text-right"><strong>₹<?= number_format($item_total, 2) ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-container">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>₹<?= number_format($order['subtotal'], 2) ?></span>
            </div>
            
            <?php if ($order['discount'] > 0): ?>
            <div class="total-row" style="color:#ef4444;">
                <span>Discount / Coupon Info:</span>
                <span>- ₹<?= number_format($order['discount'], 2) ?></span>
            </div>
            <?php endif; ?>

            <div class="total-row">
                <span>Shipping Fee:</span>
                <span><?= $order['shipping_fee'] > 0 ? '₹' . number_format($order['shipping_fee'], 2) : 'Free' ?></span>
            </div>

            <div class="total-row final">
                <span>Grand Total:</span>
                <span>₹<?= number_format($order['total_amount'], 2) ?></span>
            </div>
            
            <?php if ($order['payment_status'] === 'Paid'): ?>
            <div class="total-row amount-paid">
                <span>Amount Paid:</span>
                <span>₹<?= number_format($order['total_amount'], 2) ?></span>
            </div>
            <div class="total-row">
                <span>Balance Due:</span>
                <span>₹0.00</span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="font-weight:700; color:var(--text-dark); margin-bottom:5px;">Thank you for shopping with JK Store!</p>
            <p style="margin:0;">If you have any questions concerning this receipt, please contact our customer support.</p>
            <p style="margin-top:15px; font-size:0.8rem; color:#94a3b8;">This is a computer-generated receipt.</p>
        </div>

    </div>

    <script>
        // Optional: auto trigger print when opened
        window.addEventListener('load', function() {
            // setTimeout(() => { window.print(); }, 500); 
        });
    </script>
</body>
</html>
