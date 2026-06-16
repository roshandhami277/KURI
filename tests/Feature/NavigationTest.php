<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_logged_in_users_can_open_every_sidebar_page(): void
    {
        $user = User::factory()->create();

        $routes = [
            'dashboard',
            'tasks',
            'calendar',
            'grades',
            'notes',
            'chat',
            'news',
            'settings',
        ];

        foreach ($routes as $route) {
            $this->actingAs($user)
                ->get(route($route))
                ->assertOk();
        }
    }

    public function test_guests_cannot_open_sidebar_pages(): void
    {
        $this->get(route('tasks'))->assertRedirect(route('login'));
        $this->get(route('settings'))->assertRedirect(route('login'));
    }
}
