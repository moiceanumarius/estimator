<?php

namespace Models;

use Core\Model;

class UserModel extends Model
{
    public string $id;
    public string $name;
    public bool $isAdmin;
    public ?int $vote;
    
    public function __construct(string $name, bool $isAdmin = false, ?string $id = null)
    {
        $this->id = $id ?: uniqid('user_', true);
        $this->name = $name;
        $this->isAdmin = $isAdmin;
        $this->vote = null;
    }
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }
    
    public function getVote(): ?int
    {
        return $this->vote;
    }
    
    public function setVote(?int $vote): void
    {
        $this->vote = $vote;
    }
    
    public function hasVoted(): bool
    {
        return $this->vote !== null;
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'isAdmin' => $this->isAdmin,
            'vote' => $this->vote
        ];
    }
    
    public function toVoteArray(bool $revealed = false, string $currentUser = ''): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'isAdmin' => $this->isAdmin,
            'vote' => $revealed || $this->name === $currentUser ? $this->vote : null,
            'hasVoted' => $this->vote !== null
        ];
    }
    
    public static function fromArray(array $arr): self
    {
        $user = new self($arr['name'], $arr['isAdmin'] ?? false, $arr['id'] ?? null);
        $user->vote = $arr['vote'] ?? null;
        return $user;
    }
}


