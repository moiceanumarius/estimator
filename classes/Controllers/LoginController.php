<?php

namespace Controllers;

use Core\Controller;
use Core\View;
use Models\RoomModel;
use Models\UserModel;
use Views\LoginView;

class LoginController extends Controller
{
    public function index(): void
    {
        $roomId = $this->getRoomIdFromUrl();
        $isCreateRoom = !$roomId;
        
        if ($isCreateRoom) {
            $this->render('LoginCreateView');
        } else {
            $this->render('LoginJoinView', ['roomId' => $roomId]);
        }
    }
    
    private function getRoomIdFromUrl(): ?string
    {
        return $_GET['room'] ?? null;
    }
    
    public function createRoom(): void
    {
        $this->validateMethod('POST');
        
        $data = $this->getRequestData();
        $username = trim($data['username'] ?? '');
        
        if (!$username) {
            $this->errorResponse('Name is required', 400);
        }
        
        $roomId = $this->generateRoomId();
        $room = new RoomModel($roomId);
        $user = $room->addUser($username, true); // Create room user is admin
        
        // Set session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user'] = $user->toArray();
        
        $this->successResponse([
            'roomId' => $roomId,
            'user' => $user->toArray()
        ]);
    }
    
    public function joinRoom(): void
    {
        $this->validateMethod('POST');
        
        $data = $this->getRequestData();
        $username = trim($data['username'] ?? '');
        $roomId = $data['roomId'] ?? '';
        
        if (!$name || !$roomId) {
            $this->errorResponse('Name and Room ID are required', 400);
        }
        
        $room = new RoomModel($roomId);
        $existingUser = $room->getUser($username);
        
        if ($existingUser) {
            // Set session for existing user
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user'] = $existingUser->toArray();
            $this->successResponse(['user' => $existingUser->toArray()]);
        }
        
        $user = $room->addUser($username, false);
        
        // Set session for new user
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user'] = $user->toArray();
        
        $this->successResponse(['user' => $user->toArray()]);
    }
    
    private function generateRoomId(): string
    {
        return 'room-' . substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 9);
    }
}


