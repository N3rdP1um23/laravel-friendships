<?php

namespace Tests;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Mockery;

class FriendshipsEventsTest extends TestCase
{
    // use DatabaseTransactions;

    /** @test */
    public function friend_request_is_sent()
    {
        $sender    = createUser();
        $recipient = createUser();

        Event::shouldReceive('dispatch')->once()->withArgs(['friendships.sent', Mockery::any()]);

        $sender->befriend($recipient);

        Mockery::close();
    }

    /** @test */
    public function friend_request_is_accepted()
    {
        $sender    = createUser();
        $recipient = createUser();

        $sender->befriend($recipient);
        Event::shouldReceive('dispatch')->once()->withArgs(['friendships.accepted', Mockery::any()]);

        $recipient->acceptFriendRequest($sender);

        Mockery::close();
    }

    /** @test */
    public function friend_request_is_denied()
    {
        $sender    = createUser();
        $recipient = createUser();

        $sender->befriend($recipient);
        Event::shouldReceive('dispatch')->once()->withArgs(['friendships.denied', Mockery::any()]);

        $recipient->denyFriendRequest($sender);

        Mockery::close();
    }

    /** @test */
    public function friend_is_blocked()
    {
        $sender    = createUser();
        $recipient = createUser();

        $sender->befriend($recipient);
        $recipient->acceptFriendRequest($sender);
        Event::shouldReceive('dispatch')->once()->withArgs(['friendships.blocked', Mockery::any()]);

        $recipient->blockFriend($sender);

        Mockery::close();
    }

    /** @test */
    public function friend_is_unblocked()
    {
        $sender    = createUser();
        $recipient = createUser();

        $sender->befriend($recipient);
        $recipient->acceptFriendRequest($sender);
        $recipient->blockFriend($sender);
        Event::shouldReceive('dispatch')->once()->withArgs(['friendships.unblocked', Mockery::any()]);

        $recipient->unblockFriend($sender);

        Mockery::close();
    }

    /** @test */
    public function friendship_is_cancelled()
    {
        $sender    = createUser();
        $recipient = createUser();

        $sender->befriend($recipient);
        $recipient->acceptFriendRequest($sender);
        Event::shouldReceive('dispatch')->once()->withArgs(['friendships.cancelled', Mockery::any()]);

        $recipient->unfriend($sender);

        Mockery::close();
    }
}
