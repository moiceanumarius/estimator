<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Models\UserModel;

class UserModelTest extends TestCase
{
    public function testCreateUser(): void
    {
        $user = new UserModel('marius', true);
        $this->assertEquals('marius', $user->getName());
        $this->assertTrue($user->isAdmin());
        $this->assertNull($user->getVote());
    }

    public function testSetAndGetVote(): void
    {
        $user = new UserModel('ana', false);
        $user->setVote(34);
        $this->assertEquals(34, $user->getVote());
        $this->assertFalse($user->isAdmin());
    }

    public function testToArrayAndFromArray(): void
    {
        $user = new UserModel('gigi', false);
        $user->setVote(8);
        $arr = $user->toArray();
        $this->assertEquals('gigi', $arr['name']);
        $this->assertEquals(8, $arr['vote']);
        $this->assertFalse($arr['isAdmin']);
        $user2 = UserModel::fromArray($arr);
        $this->assertEquals('gigi', $user2->getName());
        $this->assertEquals(8, $user2->getVote());
        $this->assertFalse($user2->isAdmin());
    }
    
    public function testUserWithCustomId(): void
    {
        $customId = 'custom_user_123';
        $user = new UserModel('marius', false, $customId);
        $this->assertEquals($customId, $user->getId());
        $this->assertEquals('marius', $user->getName());
    }
    
    public function testToVoteArray(): void
    {
        $user = new UserModel('marius', true);
        $user->setVote(13);
        
        $voteArray = $user->toVoteArray(true); // revealed = true
        $this->assertEquals('marius', $voteArray['name']);
        $this->assertEquals(13, $voteArray['vote']);
        $this->assertTrue($voteArray['isAdmin']);
        $this->assertTrue($voteArray['hasVoted']);
    }
    
    public function testToVoteArrayWithoutVote(): void
    {
        $user = new UserModel('marius', false);
        
        $voteArray = $user->toVoteArray(false); // revealed = false
        $this->assertEquals('marius', $voteArray['name']);
        $this->assertNull($voteArray['vote']);
        $this->assertFalse($voteArray['isAdmin']);
        $this->assertFalse($voteArray['hasVoted']);
    }
}
