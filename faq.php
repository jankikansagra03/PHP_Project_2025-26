<?php
$title = "FAQ - JK Store";
ob_start();
?>
<div class="container fade-in-up">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5">
                    <h1 class="fw-bold mb-4 text-center" style="color: #667eea;">Frequently Asked Questions</h1>
                    <p class="text-center text-muted mb-5">Have questions? We're here to help.</p>

                    <div class="accordion" id="faqAccordion">

                        <!-- Section: Orders & Shipping -->
                        <h4 class="fw-bold text-dark mt-4 mb-3"><i class="fas fa-shipping-fast me-2" style="color: #667eea;"></i>Orders & Shipping</h4>

                        <div class="accordion-item border-0 mb-3 rounded shadow-sm overflow-hidden">
                            <h2 class="accordion-header" id="headingOrder1">
                                <button class="accordion-button fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOrder1" aria-expanded="true" aria-controls="collapseOrder1">
                                    How do I place an order?
                                </button>
                            </h2>
                            <div id="collapseOrder1" class="accordion-collapse collapse show" aria-labelledby="headingOrder1" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-secondary">
                                    Browse our products, select the items you like, and add them to your cart. Once you're ready, proceed to checkout, enter your shipping and payment details, and confirm your order. You will receive a confirmation email shortly after.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 mb-3 rounded shadow-sm overflow-hidden">
                            <h2 class="accordion-header" id="headingOrder2">
                                <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOrder2" aria-expanded="false" aria-controls="collapseOrder2">
                                    How can I track my order?
                                </button>
                            </h2>
                            <div id="collapseOrder2" class="accordion-collapse collapse" aria-labelledby="headingOrder2" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-secondary">
                                    Once your order has shipped, you will receive an email with a tracking number and a link to track your package. You can also view your order status in your account dashboard.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 mb-3 rounded shadow-sm overflow-hidden">
                            <h2 class="accordion-header" id="headingOrder3">
                                <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOrder3" aria-expanded="false" aria-controls="collapseOrder3">
                                    What are your shipping rates and delivery times?
                                </button>
                            </h2>
                            <div id="collapseOrder3" class="accordion-collapse collapse" aria-labelledby="headingOrder3" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-secondary">
                                    Shipping rates vary based on your location and the shipping method selected. Standard shipping typically takes 3-5 business days, while expedited options are available at checkout.
                                </div>
                            </div>
                        </div>


                        <!-- Section: Returns & Refunds -->
                        <h4 class="fw-bold text-dark mt-5 mb-3"><i class="fas fa-undo me-2" style="color: #667eea;"></i>Returns & Refunds</h4>

                        <div class="accordion-item border-0 mb-3 rounded shadow-sm overflow-hidden">
                            <h2 class="accordion-header" id="headingReturn1">
                                <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReturn1" aria-expanded="false" aria-controls="collapseReturn1">
                                    What is your return policy?
                                </button>
                            </h2>
                            <div id="collapseReturn1" class="accordion-collapse collapse" aria-labelledby="headingReturn1" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-secondary">
                                    We offer a 30-day return policy for unused and undamaged items in their original packaging. Please visit our Returns center to initiate a return.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 mb-3 rounded shadow-sm overflow-hidden">
                            <h2 class="accordion-header" id="headingReturn2">
                                <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReturn2" aria-expanded="false" aria-controls="collapseReturn2">
                                    When will I receive my refund?
                                </button>
                            </h2>
                            <div id="collapseReturn2" class="accordion-collapse collapse" aria-labelledby="headingReturn2" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-secondary">
                                    Once we receive and inspect your returned item, we will process your refund within 5-7 business days. The refund will be issued to your original payment method.
                                </div>
                            </div>
                        </div>


                        <!-- Section: Account & Payments -->
                        <h4 class="fw-bold text-dark mt-5 mb-3"><i class="fas fa-user-shield me-2" style="color: #667eea;"></i>Account & Payments</h4>

                        <div class="accordion-item border-0 mb-3 rounded shadow-sm overflow-hidden">
                            <h2 class="accordion-header" id="headingAccount1">
                                <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAccount1" aria-expanded="false" aria-controls="collapseAccount1">
                                    What payment methods do you accept?
                                </button>
                            </h2>
                            <div id="collapseAccount1" class="accordion-collapse collapse" aria-labelledby="headingAccount1" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-secondary">
                                    We accept all major credit cards (Visa, MasterCard, American Express), PayPal, and Apple Pay. Ensure your billing information matches your payment method.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 mb-3 rounded shadow-sm overflow-hidden">
                            <h2 class="accordion-header" id="headingAccount2">
                                <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAccount2" aria-expanded="false" aria-controls="collapseAccount2">
                                    Is my personal information secure?
                                </button>
                            </h2>
                            <div id="collapseAccount2" class="accordion-collapse collapse" aria-labelledby="headingAccount2" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-secondary">
                                    Yes, we use industry-standard encryption to protect your personal and payment information. Your data is never shared with third parties without your consent. See our <a href="privacy_policy.php">Privacy Policy</a> for more details.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 mb-3 rounded shadow-sm overflow-hidden">
                            <h2 class="accordion-header" id="headingAccount3">
                                <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAccount3" aria-expanded="false" aria-controls="collapseAccount3">
                                    I forgot my password, what should I do?
                                </button>
                            </h2>
                            <div id="collapseAccount3" class="accordion-collapse collapse" aria-labelledby="headingAccount3" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-secondary">
                                    Go to the Login page and click on the "Forgot Password?" link. Enter your registered email address, and we will send you a link to reset your password.
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="text-center mt-5">
                        <p class="text-muted">Still have questions? <a href="contact.php" class="text-decoration-none fw-semibold" style="color: #667eea;">Contact Us</a></p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>