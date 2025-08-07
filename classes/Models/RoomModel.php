<?php

namespace Models;

use Core\Model;
use Models\UserModel;
use Core\Database;

class RoomModel extends Model
{
    private string $roomId;
    private array $users = [];
    private array $flip = ['revealed' => false];
    
    public function __construct(string $roomId, ?Database $database = null)
    {
        parent::__construct($database);
        $this->roomId = $this->sanitizeId($roomId);
        $this->loadData();
    }
    
    private function loadData(): void
    {
        $usersFile = $this->database->getUsersFile($this->roomId);
        $flipFile = $this->database->getFlipFile($this->roomId);
        
        $this->users = $this->database->readJson($usersFile);
        $this->flip = $this->database->readJson($flipFile) ?: ['revealed' => false];
    }
    
    public function getRoomId(): string
    {
        return $this->roomId;
    }
    
    public function getUsers(): array
    {
        return $this->users;
    }
    
    public function getUserById(string $id): ?UserModel
    {
        foreach ($this->users as $userData) {
            if ($userData['id'] === $id) {
                return UserModel::fromArray($userData);
            }
        }
        return null;
    }
    
    public function getUser(string $name): ?UserModel
    {
        foreach ($this->users as $userData) {
            if ($userData['name'] === $name) {
                return UserModel::fromArray($userData);
            }
        }
        return null;
    }
    
    public function addUser(string $name, bool $isAdmin = false): UserModel
    {
        $user = new UserModel($name, $isAdmin);
        $this->users[] = $user->toArray();
        $this->saveUsers();
        return $user;
    }
    
    public function removeUser(string $name): void
    {
        $this->users = array_values(array_filter($this->users, function($user) use ($name) {
            return $user['name'] !== $name;
        }));
        $this->saveUsers();
    }
    
    public function updateUserVoteById(string $id, ?int $vote): void
    {
        foreach ($this->users as $key => $user) {
            if ($user['id'] === $id) {
                $this->users[$key]['vote'] = $vote;
                break;
            }
        }
        $this->saveUsers();
    }
    
    public function removeUserById(string $id): void
    {
        // Check if the user being removed is admin
        $removedUser = $this->getUserById($id);
        $wasAdmin = $removedUser && $removedUser->isAdmin();
        
        $this->users = array_values(array_filter($this->users, function($user) use ($id) {
            return $user['id'] !== $id;
        }));
        
        // If admin was removed and there are still users, promote the next user to admin
        if ($wasAdmin && !empty($this->users)) {
            $this->promoteNextUserToAdmin();
        }
        
        $this->saveUsers();
        // Note: deleteUserSession is now handled in ApiController::logout() only for self-logout
    }
    
    public function promoteNextUserToAdmin(): void
    {
        // Find the first non-admin user and make them admin
        foreach ($this->users as $key => $user) {
            if (!$user['isAdmin']) {
                $this->users[$key]['isAdmin'] = true;
                break;
            }
        }
        
        // Save changes
        $this->saveUsers();
    }
    
    public function promoteUserToAdmin(string $newAdminId, string $currentAdminId): void
    {
        // First, demote current admin to regular user
        foreach ($this->users as $key => $user) {
            if ($user['id'] === $currentAdminId) {
                $this->users[$key]['isAdmin'] = false;
            }
        }
        
        // Then promote the new user to admin
        foreach ($this->users as $key => $user) {
            if ($user['id'] === $newAdminId) {
                $this->users[$key]['isAdmin'] = true;
                break;
            }
        }
        
        $this->saveUsers();
    }
    
    public function updateUserVote(string $name, ?int $vote): void
    {
        foreach ($this->users as $key => $user) {
            if ($user['name'] === $name) {
                $this->users[$key]['vote'] = $vote;
                break;
            }
        }
        $this->saveUsers();
    }
    
    public function getVotes(string $currentUser = ''): array
    {
        $votes = [];
        foreach ($this->users as $userData) {
            $user = UserModel::fromArray($userData);
            $votes[] = $user->toVoteArray($this->flip['revealed'] ?? false, $currentUser);
        }
        return $votes;
    }
    
    public function isRevealed(): bool
    {
        return $this->flip['revealed'] ?? false;
    }
    
    public function reveal(): void
    {
        $this->flip['revealed'] = true;
        $this->saveFlip();
    }
    
    public function reset(): void
    {
        $this->flip['revealed'] = false;
        foreach ($this->users as $key => $user) {
            $this->users[$key]['vote'] = null;
        }
        $this->saveUsers();
        $this->saveFlip();
    }
    
    private function saveUsers(): void
    {
        $usersFile = $this->database->getUsersFile($this->roomId);
        $this->database->writeJson($usersFile, $this->users);
    }
    
    private function saveFlip(): void
    {
        $flipFile = $this->database->getFlipFile($this->roomId);
        $this->database->writeJson($flipFile, $this->flip);
    }
    
    private function deleteUserSession(string $id): void
    {
        $userSessionFile = $this->database->getSessionDir() . "/user_{$this->roomId}_{$id}.json";
        if (file_exists($userSessionFile)) {
            $this->database->deleteFile($userSessionFile);
        }
    }
    
    public function toArray(): array
    {
        return [
            'roomId' => $this->roomId,
            'users' => $this->users,
            'revealed' => $this->flip['revealed'] ?? false
        ];
    }
}
