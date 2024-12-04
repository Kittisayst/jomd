<?php
class AuthenMiddleware
{
    public function handle()
    {
        if (!isset($_SESSION['user'])) {
            Helper::redirect('login');
            exit;
        }
    }
}
