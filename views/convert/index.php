<div id="convertPage" class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary bg-gradient text-white py-3">
            <h1 class="h3 mb-0">
                <i class="bi bi-markdown-fill me-2"></i>
                ແປງຂໍ້ຄວາມເປັນ Markdown
            </h1>
        </div>

        <div class="card-body p-4">
            <div class="row g-4">
                <!-- Input Section -->
                <div class="col-12">
                    <div id="convertPage" class="container-fluid py-4">
                        <form id="convertForm">
                            <div class="form-group mb-3">
                                <label for="markdownInput" class="form-label d-flex justify-content-between align-items-center">
                                    <span class="fw-bold fs-5">
                                        <i class="bi bi-pencil-square me-2"></i>ຂໍ້ຄວາມຕົ້ນສະບັບ
                                    </span>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary" id="copyInput">
                                            <i class="bi bi-clipboard me-1"></i>ສຳເນົາ
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" id="clearInput">
                                            <i class="bi bi-trash me-1"></i>ລຶບ
                                        </button>
                                    </div>
                                </label>
                                <textarea
                                    class="form-control font-monospace border-2"
                                    id="markdownInput"
                                    name="markdown_input"
                                    rows="15"
                                    placeholder="ພິມຫຼືວາງຂໍ້ຄວາມທີ່ນີ້..."
                                    required
                                    style="resize: vertical;"></textarea>
                                <div class="form-text mt-2">
                                    <div class="d-flex gap-3 text-muted small">
                                        <span><code>**ໂຕໜາ**</code> ສຳລັບໂຕໜັງສືໜາ</span>
                                        <span><code>*ໂຕອ່ຽງ*</code> ສຳລັບໂຕອ່ຽງ</span>
                                        <span><code># ຫົວຂໍ້</code> ສຳລັບຫົວຂໍ້</span>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success btn-lg w-100">
                                <i class="bi bi-download me-2"></i>
                                ດາວໂຫຼດເປັນໄຟລ໌ Markdown
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Preview Section -->
                <div class="col-12">
                    <div class="form-group h-100">
                        <label class="form-label d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-5">
                                <i class="bi bi-eye me-2"></i>ຕົວຢ່າງຜົນລັບ
                            </span>
                            <span class="badge bg-secondary" id="previewStatus">
                                ຕົວຢ່າງແບບ real-time
                            </span>
                        </label>
                        <div id="markdownPreview"
                            class="preview-box border-2 border rounded p-4 bg-light h-100 overflow-auto"
                            style="min-height: 400px;"
                            aria-live="polite">
                            <div class="text-center text-muted my-5">
                                <i class="bi bi-markdown display-1 d-block mb-3"></i>
                                ພິມຂໍ້ຄວາມເພື່ອເບິ່ງຕົວຢ່າງ
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="position-fixed top-50 start-50 translate-middle d-none">
        <div class="card shadow-sm p-3 bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">ກຳລັງປະມວນຜົນ...</span>
                </div>
                <span class="text-primary fw-bold">ກຳລັງປະມວນຜົນ...</span>
            </div>
        </div>
    </div>
</div>

<script>
    // ລໍຖ້າໃຫ້ marked.js ໂຫຼດສຳເລັດ
    function initializeConverter() {
        if (typeof marked === 'undefined') {
            setTimeout(initializeConverter, 100);
            return;
        }

        $(document).ready(function() {
            const $form = $('#convertForm');
            const $input = $('#markdownInput');
            const $preview = $('#markdownPreview');
            const $loading = $('#loadingIndicator');
            const $clearBtn = $('#clearInput');
            const $copyBtn = $('#copyInput');

            // Configure marked options
            marked.setOptions({
                breaks: true,
                gfm: true,
                headerIds: false,
                sanitize: true
            });

            // Real-time preview with debounce
            let previewTimeout;
            $input.on('input', function() {
                clearTimeout(previewTimeout);
                previewTimeout = setTimeout(updatePreview, 300);
            });

            function updatePreview() {
                try {
                    const markdown = $input.val();
                    if (!markdown.trim()) {
                        $preview.empty();
                        return;
                    }
                    const html = marked.parse(markdown); // ໃຊ້ marked.parse() ແທນ marked()
                    $preview.html(html);
                } catch (error) {
                    console.error('Markdown parsing error:', error);
                    $preview.html(`<div class="text-danger">ການແປງຜິດພາດ: ${error.message}</div>`);
                }
            }

            // Clear button
            $clearBtn.on('click', function() {
                $input.val('').trigger('input').focus();
            });

            // Copy button
            $copyBtn.on('click', function() {
                const $btn = $(this);
                try {
                    $input.select();
                    document.execCommand('copy');
                    $btn.html('<i class="bi bi-check2"></i> ສຳເນົາແລ້ວ');
                    setTimeout(() => {
                        $btn.html('<i class="bi bi-clipboard"></i> ສຳເນົາ');
                    }, 2000);
                } catch (err) {
                    alert('ບໍ່ສາມາດສຳເນົາໄດ້');
                }
            });

            // Form submission
            $form.on('submit', function(e) {
                e.preventDefault();
                const markdown = $('#markdownInput').val().trim();
                if (!markdown) return;

                // Create blob and download
                const blob = new Blob([markdown], {
                    type: 'text/markdown;charset=utf-8'
                });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);

                // Get current date for filename
                const now = new Date();
                const timestamp = now.toISOString().slice(0, 10); // YYYY-MM-DD format
                link.download = `markdown_${timestamp}.md`;

                // Trigger download
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(link.href);
            });

            // Initialize tooltips
            $('[title]').tooltip();
        });
    }

    // ເລີ່ມຕົ້ນການກວດສອບ marked.js
    initializeConverter();
</script>