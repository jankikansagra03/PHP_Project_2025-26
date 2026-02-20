<?php
$title = "My Orders - JK Store";
$active_sidebar = 'orders';
ob_start();
?>
<style>
    .order-row {
        transition: background 0.3s ease;
    }

    .order-row:hover {
        background: rgba(102, 126, 234, 0.02);
    }

    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e0e0e0;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -26px;
        top: 0;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: white;
        border: 3px solid #667eea;
    }

    .timeline-item.completed::before {
        background: #667eea;
    }
</style>

<div class="card border-0 shadow-lg mb-4">
    <div class="card-body p-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0" style="color: #667eea;">My Orders</h2>
            <select class="form-select w-auto">
                <option>All Orders</option>
                <option>Delivered</option>
                <option>Processing</option>
                <option>Cancelled</option>
            </select>
        </div>

        <div class="list-group list-group-flush">
            <!-- Order 1 -->
            <div class="list-group-item border-0 p-4 order-row">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="fw-bold mb-1">Order #ORD-7829</h5>
                        <p class="text-muted small mb-0">Placed on Oct 24, 2023</p>
                    </div>
                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Delivered</span>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <img src="images/product-1.jpg" alt="" class="rounded me-3"
                        style="width: 60px; height: 60px; object-fit: cover; background: #f8f9fa;">
                    <div class="flex-grow-1">
                        <h6 class="fw-semibold mb-1">Wireless Headphones</h6>
                        <p class="text-muted small mb-0">Qty: 1</p>
                    </div>
                    <h5 class="fw-bold mb-0">$120.00</h5>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal"
                        data-bs-target="#orderDetailsModal">
                        <i class="fas fa-eye me-1"></i>View Details
                    </button>
                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fas fa-redo me-1"></i>Reorder
                    </button>
                    <button class="btn btn-sm btn-outline-success rounded-pill px-3">
                        <i class="fas fa-download me-1"></i>Invoice
                    </button>
                </div>
            </div>

            <!-- Order 2 -->
            <div class="list-group-item border-0 p-4 order-row">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="fw-bold mb-1">Order #ORD-7828</h5>
                        <p class="text-muted small mb-0">Placed on Oct 22, 2023</p>
                    </div>
                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">Processing</span>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <img src="images/product-2.jpg" alt="" class="rounded me-3"
                        style="width: 60px; height: 60px; object-fit: cover; background: #f8f9fa;">
                    <div class="flex-grow-1">
                        <h6 class="fw-semibold mb-1">Smart Fitness Watch</h6>
                        <p class="text-muted small mb-0">Qty: 1</p>
                    </div>
                    <h5 class="fw-bold mb-0">$180.00</h5>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal"
                        data-bs-target="#orderDetailsModal">
                        <i class="fas fa-eye me-1"></i>View Details
                    </button>
                    <button class="btn btn-sm btn-outline-danger rounded-pill px-3" data-bs-toggle="modal"
                        data-bs-target="#cancelOrderModal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                </div>
            </div>

            <!-- Order 3 -->
            <div class="list-group-item border-0 p-4 order-row">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="fw-bold mb-1">Order #ORD-7827</h5>
                        <p class="text-muted small mb-0">Placed on Oct 20, 2023</p>
                    </div>
                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">Cancelled</span>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <img src="images/product-3.jpg" alt="" class="rounded me-3"
                        style="width: 60px; height: 60px; object-fit: cover; background: #f8f9fa;">
                    <div class="flex-grow-1">
                        <h6 class="fw-semibold mb-1">Premium Backpack</h6>
                        <p class="text-muted small mb-0">Qty: 1</p>
                    </div>
                    <h5 class="fw-bold mb-0">$90.00</h5>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal"
                        data-bs-target="#orderDetailsModal">
                        <i class="fas fa-eye me-1"></i>View Details
                    </button>
                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fas fa-redo me-1"></i>Reorder
                    </button>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <nav class="mt-4">
            <ul class="pagination justify-content-end mb-0">
                <li class="page-item disabled">
                    <a class="page-link border-0 rounded-circle m-1" href="#"><i class="fas fa-chevron-left"></i></a>
                </li>
                <li class="page-item active"><a class="page-link border-0 rounded-circle m-1 shadow-sm" href="#"
                        style="background: var(--primary-gradient);">1</a></li>
                <li class="page-item"><a class="page-link border-0 rounded-circle m-1" href="#">2</a></li>
                <li class="page-item"><a class="page-link border-0 rounded-circle m-1" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link border-0 rounded-circle m-1" href="#"><i class="fas fa-chevron-right"></i></a>
                </li>
            </ul>
        </nav>

    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0">
                <div>
                    <h5 class="modal-title fw-bold">Order Details</h5>
                    <p class="text-muted small mb-0">Order #ORD-7829</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Order Status Timeline -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Order Status</h6>
                    <div class="timeline">
                        <div class="timeline-item completed">
                            <h6 class="fw-semibold mb-1">Order Placed</h6>
                            <p class="text-muted small mb-0">Oct 24, 2023 - 10:30 AM</p>
                        </div>
                        <div class="timeline-item completed">
                            <h6 class="fw-semibold mb-1">Order Confirmed</h6>
                            <p class="text-muted small mb-0">Oct 24, 2023 - 11:00 AM</p>
                        </div>
                        <div class="timeline-item completed">
                            <h6 class="fw-semibold mb-1">Shipped</h6>
                            <p class="text-muted small mb-0">Oct 25, 2023 - 2:00 PM</p>
                        </div>
                        <div class="timeline-item completed">
                            <h6 class="fw-semibold mb-1">Delivered</h6>
                            <p class="text-muted small mb-0">Oct 27, 2023 - 4:30 PM</p>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Order Items</h6>
                    <div class="card border">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <img src="images/product-1.jpg" alt="" class="rounded me-3"
                                    style="width: 60px; height: 60px; object-fit: cover; background: #f8f9fa;">
                                <div class="flex-grow-1">
                                    <h6 class="fw-semibold mb-1">Wireless Headphones</h6>
                                    <p class="text-muted small mb-0">Qty: 1 × $120.00</p>
                                </div>
                                <h6 class="fw-bold mb-0">$120.00</h6>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Address -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Delivery Address</h6>
                    <div class="card border">
                        <div class="card-body p-3">
                            <p class="fw-semibold mb-1">John Doe</p>
                            <p class="text-muted small mb-0">123 Street Name, Apt 4B<br>New York, NY 10001,
                                USA<br>Phone: +1 234 567 890</p>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div>
                    <h6 class="fw-bold mb-3">Order Summary</h6>
                    <div class="card border">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal</span>
                                <span class="fw-semibold">$120.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Shipping</span>
                                <span class="text-success fw-semibold">Free</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Tax</span>
                                <span class="fw-semibold">$0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">Total</span>
                                <span class="fw-bold text-primary">$120.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-cancel px-4"
                    data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-gradient rounded-pill px-4">
                    <i class="fas fa-download me-2"></i>Download Invoice
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Order Modal -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body p-5 text-center">
                <div class="mb-4">
                    <i class="fas fa-exclamation-circle fa-4x text-warning"></i>
                </div>
                <h4 class="fw-bold mb-3">Cancel Order?</h4>
                <p class="text-muted mb-4">Are you sure you want to cancel this order? This action cannot be undone.</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-danger py-3">Yes, Cancel Order</button>
                    <button type="button" class="btn btn-cancel py-3" data-bs-dismiss="modal">Keep
                        Order</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$dashboard_content = ob_get_clean();
include 'dashboard_layout.php';
?>