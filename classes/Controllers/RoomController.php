<?php

namespace Controllers;

use Core\Controller;

class RoomController extends Controller
{
    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user'])) {
            $roomId = $_GET['room'] ?? null;
            if ($roomId) {
                $this->render('LoginJoinView', ['roomId' => $roomId]);
            } else {
                $this->render('LoginCreateView');
            }
            return;
        }
        
        $this->render('RoomView');
    }
}
