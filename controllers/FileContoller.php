<?php
class FileContoller extends Controller
{
    public function index()
    {
        $database = Database::getInstance();
        $result = $database->getAllFiles();
        $this->render('file/index', ['files' => $result]);
    }
}