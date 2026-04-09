<?php
include_once 'db_config.php';
$title = "About Us - JK Store";



// ==== CONTENT FETCHING ====
$storyRes = mysqli_query($con, "SELECT * FROM about_content WHERE section='story' ORDER BY display_order ASC");
$valueRes = mysqli_query($con, "SELECT * FROM about_content WHERE section='value' ORDER BY display_order ASC");
$teamRes  = mysqli_query($con, "SELECT * FROM about_content WHERE section='team' ORDER BY display_order ASC");

ob_start();
?>
<div class="container-fluid mb-5 fade-in-up">
    <!-- Header Section -->
    <div class="row mb-5 rounded-4 text-white">
        <div class="col-12 text-center py-5">
            <h1 class="display-4 fw-bold mb-3 text-white" style="letter-spacing: -1px;">
                About JK Store
            </h1>
            <p class="lead mb-0 opacity-75">Delivering excellence since 2020</p>
        </div>
    </div>

    <!-- Company Information Grid -->
    <div class="row px-md-3 mb-5 fade-in-up fade-delay-1 g-4">
        <!-- Story Container -->
        <div class="col-lg-6">
            <div class="card border-0 h-100 shadow-sm rounded-4">
                <div class="card-body p-5 bg-light h-100 rounded-4">
                    <div class="d-flex align-items-center mb-4">
                        <i class="fas fa-book-open fa-2x me-3" style="color: var(--theme-accent);"></i>
                        <h2 class="fw-bold mb-0">Our Story</h2>
                    </div>
                    <?php if (mysqli_num_rows($storyRes) > 0): ?>
                    <?php while ($story = mysqli_fetch_assoc($storyRes)): ?>
                    <p class="text-muted mb-3" style="line-height: 1.8; font-size: 1.05rem;">
                        <?= htmlspecialchars($story['content']) ?>
                    </p>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <p class="text-muted">Company story information is currently unavailable.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Values Container -->
        <div class="col-lg-6">
            <div class="card border-0 h-100 shadow-sm rounded-4">
                <div class="card-body p-5 bg-light h-100 rounded-4">
                    <div class="d-flex align-items-center mb-4">
                        <i class="fas fa-star fa-2x me-3" style="color: var(--theme-accent);"></i>
                        <h2 class="fw-bold mb-0">Our Values</h2>
                    </div>
                    <?php if (mysqli_num_rows($valueRes) > 0): ?>
                    <div class="row g-4">
                        <?php while ($val = mysqli_fetch_assoc($valueRes)): ?>
                        <div class="col-12">
                            <div
                                class="d-flex align-items-start bg-white p-4 rounded-4 shadow-sm border-0 position-relative overflow-hidden">
                                <div class="position-absolute start-0 top-0 bottom-0"
                                    style="width: 4px; background: var(--primary-gradient);"></div>
                                <div class="ms-3">
                                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($val['title']) ?></h5>
                                    <p class="text-muted mb-0"><?= htmlspecialchars($val['content']) ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-muted">Core values information is currently unavailable.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Members Layout -->
    <div class="row px-md-3 mb-5 m-4 fade-in-up fade-delay-2 bg-white rounded-4">
        <div class="col-12 text-center mb-5 mt-4">
            <h6 class="text-uppercase fw-bold mb-2">The Core
                Engine</h6>
            <h2 class="fw-bold display-6 mb-3 text-dark">Meet Our Leadership</h2>
            <div class="mx-auto"
                style="width: 60px; height: 3px; background: var(--primary-gradient); border-radius: 3px;"></div>
            <p class="lead text-muted mx-auto mt-4" style="max-width: 600px;">The talented individuals driving JK
                Store's innovation and excellence globally.</p>
        </div>

        <div class="col-12">
            <div class="row justify-content-center g-4 p-4 p-md-5">
                <?php if (mysqli_num_rows($teamRes) > 0): ?>
                <?php while ($member = mysqli_fetch_assoc($teamRes)): ?>
                <div class="col-11 col-md-6 col-lg-4" style="margin-top: 4.5rem;">
                    <div
                        class="card border-0 shadow-sm rounded-4 h-100 team-card bg-white text-center pb-4 px-4 position-relative" style="padding-top: 5.5rem !important;">

                        <!-- Floating Avatar -->
                        <div class="position-absolute top-0 start-50 translate-middle"
                            style="width: 150px; height: 150px; border-radius: 50%; padding: 5px; background: var(--primary-gradient); box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
                            <div class="bg-white rounded-circle w-100 h-100 overflow-hidden border-3 border-white">
                                <?php if (!empty($member['image_url'])): ?>
                                <img src="<?= htmlspecialchars($member['image_url']) ?>" class="w-100 h-100"
                                    style="object-fit: cover;" alt="<?= htmlspecialchars($member['title']) ?>">
                                <?php else: ?>
                                <div class="w-100 h-100 d-flex justify-content-center align-items-center bg-light">
                                    <span class="fs-2 fw-bold"
                                        style="color: var(--theme-primary);"><?= htmlspecialchars($member['metadata']) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Card Content -->
                        <div class="mt-4 pt-2">
                            <h4 class="fw-bold text-dark mb-1"><?= htmlspecialchars($member['title']) ?></h4>
                            <p class="text-uppercase small fw-bold mb-3"
                                style="color: var(--theme-primary); letter-spacing: 1.5px;">
                                <?= htmlspecialchars($member['content']) ?>
                            </p>
                            <p class="text-muted small mb-4 opacity-75" style="line-height: 1.6;">
                                Committed to bringing the best user experience and retail innovation to the global
                                market.
                            </p>

                            <!-- Minimalist Social Icons -->
                            <div class="d-flex justify-content-center gap-2 mt-auto">
                                <a href="#" class="btn btn-social-minimal rounded-circle"><i
                                        class="fab fa-linkedin-in"></i></a>
                                <a href="#" class="btn btn-social-minimal rounded-circle"><i
                                        class="fab fa-twitter"></i></a>
                                <a href="#" class="btn btn-social-minimal rounded-circle"><i
                                        class="fab fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php else: ?>
                <div class="col-12 text-center opacity-75">Team information is currently unavailable.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* Header banner edge */
.row.rounded-4.p-5 {
    position: relative;
    overflow: hidden;
}

/* Elite Floating Avatar Card Styling */
.team-card {
    transition: all 0.3s cubic-bezier(0.2, 0.5, 0.44, 1);
    border: 2px solid transparent !important;
}

.team-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08) !important;
    border-bottom-color: var(--theme-primary) !important;
    border-top-color: var(--theme-primary) !important;
    border-left-color: var(--theme-primary) !important;
    border-right-color: var(--theme-primary) !important;
}

/* Minimalist Hover Social Icons */
.btn-social-minimal {
    width: 38px;
    height: 38px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #eaeaea;
    color: #888;
    background: #fff;
    transition: all 0.3s ease;
}

.btn-social-minimal i {
    font-size: 1rem;
}

.btn-social-minimal:hover {
    background: var(--primary-gradient);
    border-color: transparent;
    color: #fff;
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}
</style>

<?php
$content = ob_get_clean();
include 'layout.php';
?>