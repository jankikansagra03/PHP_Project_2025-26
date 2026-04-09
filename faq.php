<?php
$title = "FAQ - JK Store";
include 'db_config.php';

// Fetch FAQs grouped by category
$faq_res = mysqli_query($con, "SELECT * FROM faq ORDER BY category ASC, display_order ASC");
$faqs = [];
if ($faq_res && mysqli_num_rows($faq_res) > 0) {
    while ($row = mysqli_fetch_assoc($faq_res)) {
        $faqs[$row['category']][] = $row;
    }
} else {
    // Default if empty
    $faqs['General'][] = ['id' => 1, 'question' => 'How can we help you?', 'answer' => 'Please contact our support for assistance.'];
}

ob_start();
?>
<style>
.custom-pills .nav-link {
    color: var(--theme-primary) !important;
    background-color: transparent;
    border: 2px solid var(--theme-primary);
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.custom-pills .nav-link:hover {
    background: var(--primary-gradient);
    color: #fff !important;
    border-color: transparent;
    transform: translateY(-2px);
}

.custom-pills .nav-link.active {
    background: var(--primary-gradient);
    color: white !important;
    box-shadow: 0 4px 15px color-mix(in srgb, var(--theme-primary) 45%, transparent);
    border-color: transparent;
}

.custom-accordion .accordion-item {
    border: 1px solid rgba(31, 122, 140, 0.2) !important;
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
}

.custom-accordion .accordion-button {
    font-size: 1.05rem;
    color: var(--theme-text);
    background: transparent;
    box-shadow: none !important;
}

.custom-accordion .accordion-button:not(.collapsed) {
    color: var(--theme-primary);
    background: transparent;
    box-shadow: none;
}

.custom-accordion .accordion-body {
    background-color: rgba(255, 255, 255, 0.5);
    border-top: 1px solid rgba(31, 122, 140, 0.1);
}
</style>
<div class="container fade-in-up">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5">
                    <h1 class="fw-bold mb-4 text-center heading-primary">Frequently Asked Questions</h1>
                    <p class="text-center text-muted mb-5">Have questions? We're here to help.</p>

                    <!-- FAQ Tabs -->
                    <ul class="nav nav-pills custom-pills justify-content-center mb-5" id="faqTabs" role="tablist">
                        <?php
                        $isFirst = true;
                        foreach (array_keys($faqs) as $cat):
                            $tabId = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $cat));
                        ?>
                        <li class="nav-item m-1" role="presentation">
                            <button
                                class="nav-link fw-semibold rounded-pill px-4 py-2 shadow-sm <?php echo $isFirst ? 'active' : ''; ?>"
                                id="<?php echo $tabId; ?>-tab" data-bs-toggle="pill"
                                data-bs-target="#<?php echo $tabId; ?>" type="button" role="tab"
                                aria-controls="<?php echo $tabId; ?>"
                                aria-selected="<?php echo $isFirst ? 'true' : 'false'; ?>">
                                <?php echo htmlspecialchars($cat); ?>
                            </button>
                        </li>
                        <?php $isFirst = false;
                        endforeach; ?>
                    </ul>

                    <!-- FAQ Content -->
                    <div class="tab-content" id="faqTabContent">
                        <?php
                        $isFirst = true;
                        foreach ($faqs as $cat => $questions):
                            $tabId = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $cat));
                        ?>
                        <div class="tab-pane fade <?php echo $isFirst ? 'show active' : ''; ?>"
                            id="<?php echo $tabId; ?>" role="tabpanel" aria-labelledby="<?php echo $tabId; ?>-tab">
                            <div class="accordion accordion-flush custom-accordion"
                                id="accordion-<?php echo $tabId; ?>">
                                <?php foreach ($questions as $index => $q):
                                        $colId = 'collapse-' . $tabId . '-' . $index;
                                        $headId = 'heading-' . $tabId . '-' . $index;
                                    ?>
                                <div class="accordion-item mb-3 rounded-4 overflow-hidden">
                                    <h2 class="accordion-header" id="<?php echo $headId; ?>">
                                        <button class="accordion-button collapsed fw-bold px-4 py-3" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#<?php echo $colId; ?>"
                                            aria-expanded="false" aria-controls="<?php echo $colId; ?>">
                                            <?php echo htmlspecialchars($q['question']); ?>
                                        </button>
                                    </h2>
                                    <div id="<?php echo $colId; ?>" class="accordion-collapse collapse"
                                        aria-labelledby="<?php echo $headId; ?>"
                                        data-bs-parent="#accordion-<?php echo $tabId; ?>">
                                        <div class="accordion-body px-4 py-3 text-secondary">
                                            <?php echo nl2br(htmlspecialchars($q['answer'])); ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php $isFirst = false;
                        endforeach; ?>
                    </div>

                    <div class="text-center mt-5">
                        <p class="text-muted">Still have questions? <a href="contact.php"
                                class="text-decoration-none fw-semibold heading-primary">Contact Us</a></p>
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