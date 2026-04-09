<?php
$title = "Terms of Service - JK Store";
include 'db_config.php';

$res = mysqli_query($con, "SELECT * FROM terms_of_service ORDER BY display_order ASC");
$sections = [];
if ($res && mysqli_num_rows($res) > 0) {
    while($row = mysqli_fetch_assoc($res)) {
        $sections[] = $row;
    }
} else {
    // Fallback if table is empty
    $sections[] = ['section_title' => 'Acceptance of Terms', 'content' => '<p>By accessing and using this website, you accept and agree to be bound by these terms.</p>'];
}

ob_start();
?>
<div class="container fade-in-up">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5">
                    <h1 class="fw-bold mb-4 heading-primary">Terms of Service</h1>
                    <p class="text-muted">Last Updated: <?php echo date('F d, Y'); ?></p>
                    <hr class="mb-5">

                    <?php $i = 1; foreach($sections as $sec): ?>
                    <div class="mb-5 fade-in-up fade-delay-<?php echo min($i, 5); ?>">
                        <h3 class="fw-bold mb-3"><?php echo $i . '. ' . htmlspecialchars($sec['section_title']); ?></h3>
                        <div class="text-secondary"><?php echo $sec['content']; ?></div>
                    </div>
                    <?php $i++; endforeach; ?>

                    <div class="text-center mt-5">
                        <a href="index.php" class="btn btn-primary btn-gradient">Back to Home</a>
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