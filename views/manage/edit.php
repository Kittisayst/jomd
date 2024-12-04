<!-- File Form -->
<div id="fileForm" class="page">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 id="formTitle"><i class="bi bi-file-earmark-plus"></i> ເພີ່ມໄຟລ໌ໃໝ່</h2>
        <a class="btn btn-outline-secondary" href="<?= Helper::url("manage") ?>">
            <i class="bi bi-x-circle"></i> ຍົກເລີກ
        </a>
    </div>
    <form class="card" method="post" action="<?= Helper::url("manage/update") ?>">
        <div class="card-body">
            <input type="hidden" id="fileId" name="id" value="<?= $file['id'] ?>">
            <div class="mb-3">
                <label class="form-label">ຫົວຂໍ້</label>
                <input type="text" class="form-control" id="fileTitle" name="title" value="<?= $file['title'] ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">ເລືອກໄຟລ໌</label>
                <input type="file" class="form-control" id="mdFile" name="file" accept=".md">
            </div>
            <div class="mb-3">
                <label class="form-label">ເນື້ອໃນ</label>
                <textarea class="form-control" id="fileContent" name="content" rows="15" required>
                    <?= $file['content'] ?>
                </textarea>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> ບັນທຶກ
                </button>
            </div>
        </div>
    </form>
</div>
<script>
    $('#mdFile').on('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            $('#fileContent').val(e.target.result);
        };

        reader.readAsText(file);
    });
</script>