<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Models\RoomModel;
use Core\Database;
use ReflectionClass;

class RoomModelTest extends TestCase
{
    private string $roomId = 'test-room';
    private RoomModel $room;
    private Database $mockDatabase;

    protected function setUp(): void
    {
        // Create mock database
        $this->mockDatabase = $this->createMock(Database::class);
        $this->mockDatabase->method('getUsersFile')->willReturn('/tmp/test_users.json');
        $this->mockDatabase->method('getFlipFile')->willReturn('/tmp/test_flip.json');
        $this->mockDatabase->method('readJson')->willReturn([]);
        $this->mockDatabase->method('writeJson')->willReturn(true);
        
        // Create room with injected mock database
        $this->room = new RoomModel($this->roomId, $this->mockDatabase);
        
        // Reset state for each test
        $reflection = new ReflectionClass($this->room);
        $usersProp = $reflection->getProperty('users');
        $usersProp->setAccessible(true);
        $usersProp->setValue($this->room, []);
        $flipProp = $reflection->getProperty('flip');
        $flipProp->setAccessible(true);
        $flipProp->setValue($this->room, ['revealed' => false]);
    }

    public function testAddUser(): void
    {
        $user = $this->room->addUser('marius', true);
        $this->assertEquals('marius', $user->getName());
        $this->assertTrue($user->isAdmin());
        $this->assertCount(1, $this->room->getUsers());
    }

    public function testVoteAndReveal(): void
    {
        $this->room->addUser('marius', true);
        $this->room->addUser('ana', false);
        $this->room->updateUserVote('ana', 21);
        $votes = $this->room->getVotes('ana');
        $this->assertEquals(21, $votes[1]['vote']);
        $this->assertFalse($this->room->isRevealed());
        $this->room->reveal();
        $this->assertTrue($this->room->isRevealed());
    }

    public function testReset(): void
    {
        $this->room->addUser('marius', true);
        $this->room->updateUserVote('marius', 13);
        $this->room->reveal();
        $this->room->reset();
        $votes = $this->room->getVotes('marius');
        $this->assertNull($votes[0]['vote']);
        $this->assertFalse($this->room->isRevealed());
    }
    
    public function testRemoveUserById(): void
    {
        $user1 = $this->room->addUser('marius', true);
        $user2 = $this->room->addUser('ana', false);
        
        $this->assertCount(2, $this->room->getUsers());
        
        $this->room->removeUserById($user2->getId());
        $this->assertCount(1, $this->room->getUsers());
        $this->assertEquals('marius', $this->room->getUsers()[0]['name']);
    }
    
    public function testPromoteUserToAdmin(): void
    {
        $admin = $this->room->addUser('marius', true);
        $user = $this->room->addUser('ana', false);
        
        $this->room->promoteUserToAdmin($user->getId(), $admin->getId());
        
        $users = $this->room->getUsers();
        
        // Find the new admin
        $newAdmin = null;
        $oldAdmin = null;
        
        foreach ($users as $u) {
            if ($u['id'] === $user->getId()) {
                $newAdmin = $u;
            }
            if ($u['id'] === $admin->getId()) {
                $oldAdmin = $u;
            }
        }
        
        $this->assertNotNull($newAdmin);
        $this->assertNotNull($oldAdmin);
        $this->assertTrue($newAdmin['isAdmin']);
        $this->assertFalse($oldAdmin['isAdmin']);
    }
    
    public function testPromoteNextUserToAdminWhenAdminRemoved(): void
    {
        $admin = $this->room->addUser('marius', true);
        $user1 = $this->room->addUser('ana', false);
        $user2 = $this->room->addUser('bob', false);
        
        $this->room->removeUserById($admin->getId());
        
        $users = $this->room->getUsers();
        $this->assertCount(2, $users);
        
        // First non-admin user should be promoted
        $this->assertTrue($users[0]['isAdmin']);
        $this->assertEquals('ana', $users[0]['name']);
        $this->assertFalse($users[1]['isAdmin']);
    }
}
