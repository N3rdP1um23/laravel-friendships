<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Mockery;
use Orchestra\Testbench\TestCase;

class FriendshipsEventsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--path' => realpath(dirname(__DIR__).'/tests/database/migrations'),
        ]);
        $this->withFactories(realpath(dirname(__DIR__).'/database/factories'));
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('friendships.tables.fr_groups_pivot', 'user_friendship_groups');
        $app['config']->set('friendships.tables.fr_pivot', 'friendships');
        $app['config']->set('friendships.groups.acquaintances', 0);
        $app['config']->set('friendships.groups.close_friends', 1);
        $app['config']->set('friendships.groups.family', 2);
    }

    /** @test */
    public function friend_request_is_sent()
    {
        $sender    = createUser();
        $recipient = createUser();

        Event::shouldReceive('dispatch')->once()->withArgs(['friendships.sent', Mockery::any()]);
        $sender->befriend($recipient);
    }

    /** @test */
    public function friend_request_is_accepted()
    {

        $sender    = createUser();
        $recipient = createUser();

        $sender->befriend($recipient);

        Event::shouldReceive('dispatch')->once()->withArgs(['friendships.accepted', Mockery::any()]);

        $recipient->acceptFriendRequest($sender);
    }

    /** @test */
    public function friend_request_is_denied()
    {

        $sender    = createUser();
        $recipient = createUser();

        $sender->befriend($recipient);
        Event::shouldReceive('dispatch')->once()->withArgs(['friendships.denied', Mockery::any()]);

        $recipient->denyFriendRequest($sender);
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
    }
}
