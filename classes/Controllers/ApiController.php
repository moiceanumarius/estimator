<?php

namespace Controllers;

use Core\Controller;
use Core\Container;
use Models\RoomModel;
use Models\UserModel;

class ApiController extends Controller
{
    private Container $container;
    
    public function __construct(?Container $container = null)
    {
        parent::__construct();
        $this->container = $container ?? new Container();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    private function createRoomModel(string $roomId): RoomModel
    {
        if ($this->container->has('roomModel')) {
            $factory = $this->container->get('roomModel');
            return $factory($roomId);
        }
        
        return new RoomModel($roomId, $this->database);
    }
    
    private function createUserModel(string $name, bool $isAdmin = false, ?string $id = null): UserModel
    {
        if ($this->container->has('userModel')) {
            $factory = $this->container->get('userModel');
            return $factory($name, $isAdmin, $id);
        }
        
        return new UserModel($name, $isAdmin, $id);
    }

    public function login(): void
    {
        $this->validateMethod('POST');
        $data = $this->getRequestData();
        $name = trim($data['name'] ?? '');
        $isAdmin = !empty($data['isAdmin']);
        
        if (!$name) {
            $this->errorResponse('Name is required', 400);
        }
        
        $roomId = $this->getRoomId();
        $room = $this->createRoomModel($roomId);
        
        // Look for existing user with same name and id (if coming from session)
        $existingUser = null;
        if (!empty($data['id'])) {
            $existingUser = $room->getUserById($data['id']);
        }
        
        if ($existingUser) {
            // Set session for existing user
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user'] = $existingUser->toArray();
            $this->successResponse(['user' => $existingUser->toArray()]);
        }
        
        $user = $room->addUser($name, $isAdmin);
        
        // Set session for new user
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user'] = $user->toArray();
        $this->successResponse(['user' => $user->toArray()]);
    }

    public function users(): void
    {
        $this->validateMethod('GET');
        
        $roomId = $this->getRoomId();
        $room = $this->createRoomModel($roomId);
        $this->successResponse(['users' => $room->getUsers()]);
    }
    
    public function logout(): void
    {
        $this->validateMethod('POST');
        $data = $this->getRequestData();
        $id = $data['id'] ?? null;
        
        if (!$id) {
            $this->errorResponse('ID is required', 400);
        }
        
        $roomId = $this->getRoomId();
        $room = $this->createRoomModel($roomId);
        
        // Check if user exists before removing
        $user = $room->getUserById($id);
        if (!$user) {
            $this->errorResponse('User does not exist', 404);
        }
        
        // Check if the current user is logging out themselves
        $isSelfLogout = false;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['user']) && $_SESSION['user']['id'] === $id) {
            $isSelfLogout = true;
        }
        
        $room->removeUserById($id);
        
        // Only clear session and delete user session file if the user is logging out themselves
        if ($isSelfLogout) {
            // Delete user session file
            $userSessionFile = $this->database->getSessionDir() . "/user_{$roomId}_{$id}.json";
            if (file_exists($userSessionFile)) {
                unlink($userSessionFile);
            }
            
            // Clear session
            unset($_SESSION['user']);
            session_destroy();
        }
        
        $this->successResponse();
    }
    
    public function vote(): void
    {
        $this->validateMethod('POST');
        $data = $this->getRequestData();
        $id = $data['id'] ?? null;
        $vote = $data['vote'] ?? null;
        
        if (!$id) {
            $this->errorResponse('ID is required', 400);
        }
        
        $roomId = $this->getRoomId();
        $room = $this->createRoomModel($roomId);
        $room->updateUserVoteById($id, $vote);
        $this->successResponse();
    }
    
    public function flip(): void
    {
        $this->validateMethod('POST');
        
        $roomId = $this->getRoomId();
        $room = $this->createRoomModel($roomId);
        $room->reveal();
        $this->successResponse();
    }
    
    public function votes(): void
    {
        $this->validateMethod('GET');
        
        $roomId = $this->getRoomId();
        $room = $this->createRoomModel($roomId);
        $currentUser = $_GET['user'] ?? '';
        $votes = $room->getVotes($currentUser);
        $this->successResponse([
            'votes' => $votes,
            'revealed' => $room->isRevealed()
        ]);
    }
    
    public function flipstate(): void
    {
        $this->validateMethod('GET');
        
        $roomId = $this->getRoomId();
        $room = $this->createRoomModel($roomId);
        $this->successResponse(['revealed' => $room->isRevealed()]);
    }
    
    public function resetflip(): void
    {
        $this->validateMethod('POST');
        
        $roomId = $this->getRoomId();
        $room = $this->createRoomModel($roomId);
        $room->reset();
        $this->successResponse();
    }

    public function sessionUser(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
            $this->successResponse(['user' => $_SESSION['user']]);
        } else {
            $this->successResponse(['user' => null]);
        }
    }
    
    public function promoteUser(): void
    {
        $this->validateMethod('POST');
        $data = $this->getRequestData();
        $newAdminId = $data['newAdminId'] ?? null;
        $currentAdminId = $data['currentAdminId'] ?? null;
        
        if (!$newAdminId || !$currentAdminId) {
            $this->errorResponse('IDs are required', 400);
        }
        
        $roomId = $this->getRoomId();
        $room = $this->createRoomModel($roomId);
        $room->promoteUserToAdmin($newAdminId, $currentAdminId);
        
        // Update session for current user if they are the one being demoted
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['user']) && $_SESSION['user']['id'] === $currentAdminId) {
            $_SESSION['user']['isAdmin'] = false;
        }
        
        $this->successResponse(['message' => 'User promoted successfully']);
    }
}


