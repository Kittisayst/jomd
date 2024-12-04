<!-- Manage Page -->
<div id="managePage" class="page">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">
            <i class="bi bi-gear" aria-hidden="true"></i>
            <span>ຈັດການໄຟລ໌</span>
        </h1>
        <a href="<?= Helper::url('manage/add') ?>" 
           class="btn btn-primary"
           role="button">
            <i class="bi bi-plus-circle" aria-hidden="true"></i>
            <span>ເພີ່ມໄຟລ໌ໃໝ່</span>
        </a>
    </div>

    <!-- Table Section -->
    <?php if (empty($files)): ?>
        <div class="alert alert-info" role="alert">
            <i class="bi bi-info-circle"></i> ບໍ່ມີໄຟລ໌ໃນລະບົບເທື່ອ
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover" role="grid">
                <thead class="table-light">
                    <tr>
                        <th scope="col">ຫົວຂໍ້</th>
                        <th scope="col">ຊື່ໄຟລ໌</th>
                        <th scope="col">ວັນທີສ້າງ</th>
                        <th scope="col">ການດຳເນີນການ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($files as $file): ?>
                        <tr>
                            <td><?= Helper::html($file['title']) ?></td>
                            <td><?= Helper::html($file['file_name']) ?></td>
                            <td><?= Helper::formatDate($file['created_at'], 'd/m/Y H:i') ?></td>
                            <td>
                                <div class="btn-group" role="group" aria-label="ການດຳເນີນການສຳລັບໄຟລ໌">
                                    <!-- View Button -->
                                    <a href="<?= Helper::url('manage/view/' . Helper::int($file['id'])) ?>"
                                       class="btn btn-success btn-sm"
                                       aria-label="ເບິ່ງ <?= Helper::html($file['title']) ?>">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                        <span class="visually-hidden">ເບິ່ງ</span>
                                    </a>

                                    <!-- Edit Button -->
                                    <a href="<?= Helper::url('manage/edit/' . Helper::int($file['id'])) ?>"
                                       class="btn btn-primary btn-sm"
                                       aria-label="ແກ້ໄຂ <?= Helper::html($file['title']) ?>">
                                        <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                        <span class="visually-hidden">ແກ້ໄຂ</span>
                                    </a>

                                    <!-- Delete Form -->
                                    <form action="<?= Helper::url('manage/delete/' . Helper::int($file['id'])) ?>"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການລົບ <?= Helper::html($file['title']) ?>?');">
                                        <?= Helper::csrf() ?>
                                        <button type="submit"
                                                class="btn btn-danger btn-sm"
                                                aria-label="ລົບ <?= Helper::html($file['title']) ?>">
                                            <i class="bi bi-trash" aria-hidden="true"></i>
                                            <span class="visually-hidden">ລົບ</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>