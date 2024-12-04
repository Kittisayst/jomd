<div id="homePage" class="container-fluid py-4">
    <!-- Header Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="h2 mb-0">
                        <i class="bi bi-files me-2 text-primary"></i>
                        ລາຍການໄຟລ໌
                    </h1>
                </div>
                <div class="col-md-6">
                    <div class="search-box">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text"
                                class="form-control border-start-0 ps-0"
                                id="searchInput"
                                placeholder="ຄົ້ນຫາໄຟລ໌..."
                                aria-label="ຄົ້ນຫາໄຟລ໌">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Files Grid -->
    <?php if (empty($files)): ?>
        <div class="text-center py-5">
            <div class="display-1 text-muted mb-4">
                <i class="bi bi-folder2-open"></i>
            </div>
            <h2 class="h4 text-muted">ບໍ່ມີໄຟລ໌ໃນລະບົບເທື່ອ</h2>
            <p class="text-muted">ກະລຸນາເພີ່ມໄຟລ໌ໃໝ່</p>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($files as $file): ?>
                <div class="col file-card" data-title="<?= Helper::html($file['title']) ?>">
                    <div class="card h-100 shadow-sm hover-shadow">
                        <div class="card-body p-4">
                            <!-- File Icon & Title -->
                            <div class="d-flex align-items-center mb-3">
                                <div class="file-icon me-3">
                                    <i class="bi bi-file-text display-6 text-primary"></i>
                                </div>
                                <h5 class="card-title mb-0 text-truncate">
                                    <?= Helper::html($file['title']) ?>
                                </h5>
                            </div>

                            <!-- File Content Preview -->
                            <div class="card-text mb-4">
                                <div class="text-muted preview-text">
                                    <?= Helper::truncate(Helper::html($file['content']), 150) ?>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-auto">
                                <div class="d-flex gap-2">
                                    <a href="<?= Helper::url("home/viewfile/" . Helper::int($file['id'])) ?>"
                                        class="btn btn-primary flex-grow-1">
                                        <i class="bi bi-eye me-1"></i>
                                        ເບິ່ງເພີ່ມເຕີມ
                                    </a>
                                    <button type="button"
                                        class="btn btn-outline-primary"
                                        onclick="shareFile('<?= Helper::html($file['title']) ?>')">
                                        <i class="bi bi-share"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .hover-shadow {
        transition: all 0.3s ease;
    }

    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    .preview-text {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.6;
    }

    .search-box {
        max-width: 400px;
        margin-left: auto;
    }

    .file-card {
        animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<script>
    $(document).ready(function() {
        // Search functionality
        $('#searchInput').on('input', function() {
            const searchText = $(this).val().toLowerCase();
            $('.file-card').each(function() {
                const title = $(this).data('title').toLowerCase();
                $(this).toggle(title.includes(searchText));
            });
        });
    });

    // Share functionality
    function shareFile(title) {
        if (navigator.share) {
            navigator.share({
                title: title,
                text: 'ເບິ່ງເອກະສານ: ' + title,
                url: window.location.href
            }).catch(console.error);
        } else {
            // Fallback: Copy URL to clipboard
            navigator.clipboard.writeText(window.location.href)
                .then(() => alert('ສຳເນົາລິ້ງແລ້ວ'))
                .catch(() => alert('ບໍ່ສາມາດສຳເນົາລິ້ງໄດ້'));
        }
    }
</script>