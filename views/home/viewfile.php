<div id="viewPage" class="container-fluid py-4">
    <!-- Header Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="file-icon">
                        <i class="bi bi-file-text display-6 text-primary"></i>
                    </div>
                    <h1 id="viewTitle" class="h2 mb-0">
                        <?= Helper::html($file['title']) ?>
                    </h1>
                </div>
                <div class="d-flex gap-2">
                    <button id="printBtn" class="btn btn-outline-primary">
                        <i class="bi bi-printer"></i>
                        <span class="d-none d-md-inline ms-1">ພິມ</span>
                    </button>
                    <button id="downloadBtn" class="btn btn-outline-primary">
                        <i class="bi bi-download"></i>
                        <span class="d-none d-md-inline ms-1">ດາວໂຫຼດ</span>
                    </button>
                    <a href="<?= Helper::url('home') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i>
                        <span class="d-none d-md-inline ms-1">ກັບຄືນ</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">ກຳລັງໂຫຼດ...</span>
                </div>
                <div class="mt-3 text-muted">ກຳລັງໂຫຼດເນື້ອຫາ...</div>
            </div>

            <!-- Error Message -->
            <div id="errorMessage" class="alert alert-danger d-none" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <span id="errorText"></span>
            </div>

            <!-- Content -->
            <div id="content" class="markdown-content"></div>
        </div>
    </div>
</div>

<style>
    @media print {

        .btn,
        .card {
            display: none !important;
        }

        .markdown-content {
            display: block !important;
        }
    }
</style>

<script>
    $(document).ready(function() {
        let markdownContent = '';

        async function loadMarkdown() {
            try {
                $('#loadingIndicator').show();
                $('#content').hide();
                $('#errorMessage').addClass('d-none');

                const response = await fetch("../../uploads/<?= Helper::html($file['file_name']) ?>");
                if (!response.ok) throw new Error('ບໍ່ສາມາດໂຫຼດໄຟລ໌ໄດ້');

                markdownContent = await response.text();

                // Configure marked options
                marked.setOptions({
                    breaks: true,
                    gfm: true,
                    headerIds: true,
                    sanitize: true
                });

                const htmlContent = marked.parse(markdownContent);
                $('#content').html(htmlContent).show();
            } catch (error) {
                console.error('Error loading markdown:', error);
                $('#errorText').text(error.message);
                $('#errorMessage').removeClass('d-none');
            } finally {
                $('#loadingIndicator').hide();
            }
        }

        // Print functionality
        $('#printBtn').on('click', function() {
            window.print();
        });

        // Download functionality
        $('#downloadBtn').on('click', function() {
            const blob = new Blob([markdownContent], {
                type: 'text/markdown;charset=utf-8'
            });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = '<?= Helper::html($file['title']) ?>.md';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        });

        // Load content when page is ready
        loadMarkdown();
    });
</script>