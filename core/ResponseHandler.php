<?php
// /includes/ResponseHandler.php

class ResponseHandler {
    public static function success($data = null, $message = '') {
        self::sendResponse(true, $data, $message);
    }

    public static function error($message, $code = 400) {
        http_response_code($code);
        self::sendResponse(false, null, $message);
    }

    private static function sendResponse($success, $data = null, $message = '') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'data' => $data,
            'message' => $message
        ]);
        exit;
    }
}