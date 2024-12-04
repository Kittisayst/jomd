<?php
class ManageController extends Controller
{
    private FileDatabase $db;

    public function __construct()
    {
        $this->db = FileDatabase::getInstance();
        $auth = new AuthenMiddleware();
        $auth->handle();
    }

    function index()
    {
        $result = $this->db->getAllFiles();
        $this->render('manage/index', ['files' => $result]);
    }

    function add()
    {
        $this->render('manage/create');
    }

    function store()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            // ກວດສອບຂໍ້ມູນ
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');

            if (empty($title) || empty($content)) {
                throw new Exception('ກະລຸນາປ້ອນຂໍ້ມູນໃຫ້ຄົບ');
            }

            // ສ້າງຊື່ໄຟລ໌
            $fileName = $this->generateFileName($title);
            $filePath = UPLOADS_PATH . '/' . $fileName;

            // ບັນທຶກໄຟລ໌ຈິງ
            if (!file_put_contents($filePath, $content)) {
                throw new Exception('ບໍ່ສາມາດບັນທຶກໄຟລ໌ໄດ້');
            }

            // ບັນທຶກຂໍ້ມູນລົງຖານຂໍ້ມູນ
            $fileData = [
                'title' => $title,
                'file_name' => $fileName,
                'content' => $content
            ];

            // ຖ້າມີ ID ແມ່ນການອັບເດດ
            if (!empty($_POST['id'])) {
                $fileData['id'] = (int)$_POST['id'];
                // ລຶບໄຟລ໌ເກົ່າ
                $oldFile = $this->db->getFile($fileData['id']);
                if ($oldFile && $oldFile['file_name'] !== $fileName) {
                    $oldPath = UPLOADS_PATH . '/' . $oldFile['file_name'];
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
            }

            $id = $this->db->saveFile($fileData);

            if (!$id) {
                // ລຶບໄຟລ໌ທີ່ພຶ່ງບັນທຶກຖ້າບັນທຶກຖານຂໍ້ມູນບໍ່ສຳເລັດ
                unlink($filePath);
                throw new Exception('ບໍ່ສາມາດບັນທຶກຂໍ້ມູນໄດ້');
            }

            // ກັບໄປໜ້າຈັດການ
            Helper::redirect('manage');
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            Helper::redirect('manage/create');
        }
    }

    function view($id)
    {
        $file = $this->db->getFile($id);
        $this->render('home/viewfile', ['file' => $file]);
    }

    function edit($id)
    {
        try {
            $file = $this->db->getFile($id);
            if (!$file) {
                throw new Exception('ບໍ່ພົບໄຟລ໌');
            }

            $this->render('manage/edit', ['file' => $file]);
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            Helper::redirect('manage');
        }
    }

    function update($id)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            // ກວດສອບວ່າມີ ID
            if (empty($_POST['id'])) {
                throw new Exception('ບໍ່ພົບໄຟລ໌ທີ່ຕ້ອງການແກ້ໄຂ');
            }

            $id = (int)$_POST['id'];
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');

            // ກວດສອບຂໍ້ມູນ
            if (empty($title) || empty($content)) {
                throw new Exception('ກະລຸນາປ້ອນຂໍ້ມູນໃຫ້ຄົບ');
            }

            // ດຶງຂໍ້ມູນໄຟລ໌ເກົ່າ
            $oldFile = $this->db->getFile($id);
            if (!$oldFile) {
                throw new Exception('ບໍ່ພົບໄຟລ໌ທີ່ຕ້ອງການແກ້ໄຂ');
            }

            // ອັບເດດໄຟລ໌ຈິງ
            $oldPath = UPLOADS_PATH . '/' . $oldFile['file_name'];
            if (file_exists($oldPath)) {
                // ອັບເດດເນື້ອໃນໄຟລ໌ເກົ່າ
                if (!file_put_contents($oldPath, $content)) {
                    throw new Exception('ບໍ່ສາມາດອັບເດດໄຟລ໌ໄດ້');
                }
            }

            // ອັບເດດຂໍ້ມູນໃນຖານຂໍ້ມູນ
            $fileData = [
                'id' => $id,
                'title' => $title,
                'file_name' => $oldFile['file_name'], // ໃຊ້ຊື່ໄຟລ໌ເກົ່າ
                'content' => $content
            ];

            if (!$this->db->saveFile($fileData)) {
                throw new Exception('ບໍ່ສາມາດອັບເດດຂໍ້ມູນໄດ້');
            }

            $_SESSION['success'] = 'ອັບເດດໄຟລ໌ສຳເລັດ';
            Helper::redirect('manage');
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            Helper::redirect("manage/edit/{$id}");
        }
    }

    private function generateFileName(string $title): string
    {
        $prefix = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $unique = uniqid() . bin2hex(random_bytes(8));
        return $prefix . '-' . $unique . '.md';
    }

    public function delete(int $id): void
    {
        try {
            // ກວດສອບວ່າມີ id
            if (!$id) {
                throw new Exception('ບໍ່ພົບໄຟລ໌ທີ່ຕ້ອງການລຶບ');
            }

            // ດຶງຂໍ້ມູນໄຟລ໌ທີ່ຈະລຶບ
            $file = $this->db->getFile($id);
            if (!$file) {
                throw new Exception('ບໍ່ພົບໄຟລ໌ທີ່ຕ້ອງການລຶບ');
            }

            // ລຶບໄຟລ໌ຈິງໃນໂຟລເດີ uploads
            $filePath = UPLOADS_PATH . '/' . $file['file_name'];
            if (file_exists($filePath)) {
                if (!unlink($filePath)) {
                    throw new Exception('ບໍ່ສາມາດລຶບໄຟລ໌ໄດ້');
                }
            }

            // ລຶບຂໍ້ມູນໃນຖານຂໍ້ມູນ
            if (!$this->db->deleteFile($id)) {
                throw new Exception('ບໍ່ສາມາດລຶບຂໍ້ມູນໄດ້');
            }

            $_SESSION['success'] = 'ລຶບໄຟລ໌ສຳເລັດ';
            Helper::redirect('manage');
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            Helper::redirect('manage');
        }
    }

    // ດາວໂຫຼດໄຟລ໌
    public function download(int $id): void
    {
        try {
            $file = $this->db->getFile($id);
            if (!$file) {
                throw new Exception('ບໍ່ພົບໄຟລ໌');
            }

            $filePath = UPLOADS_PATH . '/' . $file['file_name'];
            if (!file_exists($filePath)) {
                throw new Exception('ບໍ່ພົບໄຟລ໌ໃນລະບົບ');
            }

            // ສົ່ງໄຟລ໌ໃຫ້ດາວໂຫຼດ
            header('Content-Type: text/markdown');
            header('Content-Disposition: attachment; filename="' . $file['file_name'] . '"');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            Helper::redirect('manage');
        }
    }

    // ເພີ່ມຟັງຊັນກວດສອບ MIME type ຂອງໄຟລ໌
    private function isMarkdownFile(string $filePath): bool
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        return in_array($mimeType, [
            'text/markdown',
            'text/plain',
            'text/x-markdown'
        ]);
    }
}
