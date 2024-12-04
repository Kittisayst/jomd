<?php
class HomeController extends Controller
{

    private FileDatabase $filedb;

    public function __construct()
    {
        $this->filedb = FileDatabase::getInstance();
    }
    function index()
    {
        $result = $this->filedb->getAllFiles();
        $this->render('home/index', ['files' => $result]);
    }

    function viewfile($id)
    {
        $file = $this->filedb->getFile($id);
        $this->render('home/viewfile', ['file' => $file]);
    }
}
