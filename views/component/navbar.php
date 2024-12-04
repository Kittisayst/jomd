<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="">
            <i class="bi bi-file-earmark-text"></i> Jo Markdown
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="<?= Helper::url('home') ?>">
                        <i class="bi bi-house"></i> ໜ້າຫຼັກ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?=Helper::url('manage')?>">
                        <i class="bi bi-gear"></i> ຈັດການໄຟລ໌
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?=Helper::url('convert')?>">
                        <i class="bi bi-arrow-left-right"></i> ແປງຂໍ້ຄວາມ
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>