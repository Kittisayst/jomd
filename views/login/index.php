<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card login-container">
                <div class="card-header text-center py-3">
                    <h4 class="mb-0">ເຂົ້າສູ່ລະບົບ</h4>
                </div>
                <div class="card-body p-4">
                    <form id="loginForm" method="post" action="<?= Helper::url("login/auth") ?>">
                        <!-- ຊື່ຜູ້ໃຊ້ -->
                        <div class="mb-3">
                            <label for="username" class="form-label">ຊື່ຜູ້ໃຊ້</label>
                            <input type="text" class="form-control" id="username" required>
                        </div>

                        <!-- ລະຫັດຜ່ານ -->
                        <div class="mb-3">
                            <label for="password" class="form-label">ລະຫັດຜ່ານ</label>
                            <input type="password" class="form-control" id="password" required>
                        </div>

                        <!-- ຈື່ຂ້ອຍໄວ້ -->
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">ຈື່ຂ້ອຍໄວ້</label>
                        </div>

                        <!-- ປຸ່ມເຂົ້າສູ່ລະບົບ -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-login">ເຂົ້າສູ່ລະບົບ</button>
                        </div>
                    </form>

                    <!-- ລິ້ງລືມລະຫັດຜ່ານ -->
                    <div class="text-center mt-3">
                        <a href="#" class="text-decoration-none">ລືມລະຫັດຜ່ານ?</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>