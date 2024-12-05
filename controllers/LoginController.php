<?php
class LoginController extends Controller
{
    private AuthDatabase $auth;
    public function __construct()
    {
        $this->auth = AuthDatabase::getInstance();
    }
    public function index()
    {
        $this->render('login/index');
    }

    public function auth()
    {
        // ເຂົ້າສູ່ລະບົບ
        try {
            $user = $this->auth->verifyLogin($_POST['username'], $_POST['password']);
            if ($user) {
                $token = $this->auth->createSession(
                    $user['id'],
                    $_SERVER['REMOTE_ADDR'],
                    $_SERVER['HTTP_USER_AGENT']
                );

                $_SESSION['user'] = $token;

                $this->redirect('manage');
                // ບັນທຶກ token ໃສ່ cookie ຫຼື session
            } else {
                $this->redirectWith('login', [], 'ຊື່ຜູ້ໃຊ້ງານຫຼືລະຫັດຜ່ານບໍ່ຖືກຕ້ອງ!', 'warning');
            }
        } catch (DatabaseException $e) {
            // ຈັດການກໍລະນີບັນຊີຖືກລ໋ອກຫຼືປິດການໃຊ້ງານ
            echo $e->getMessage();
            $this->redirectWith('login', [], 'ເກີດຂໍ້ຜິດພາດ', 'error');
        }
    }
}
