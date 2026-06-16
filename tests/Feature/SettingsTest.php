<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_change_only_their_name(): void
    {
        $course = Course::create(['name' => 'Economics', 'is_active' => true]);
        $schoolClass = SchoolClass::create(['name' => '11.1', 'grade_level' => 11, 'is_active' => true]);
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'student@ael.edu.pt',
            'course_id' => $course->id,
            'school_class_id' => $schoolClass->id,
        ]);

        $this->actingAs($user)
            ->put(route('settings.update'), [
                'name' => 'New Name',
                'email' => 'changed@ael.edu.pt',
                'course_id' => null,
                'school_class_id' => null,
            ])
            ->assertRedirect(route('settings'));

        $user->refresh();

        $this->assertSame('New Name', $user->name);
        $this->assertSame('student@ael.edu.pt', $user->email);
        $this->assertSame($course->id, $user->course_id);
        $this->assertSame($schoolClass->id, $user->school_class_id);
    }

    public function test_settings_page_shows_locked_school_information(): void
    {
        $course = Course::create(['name' => 'Languages', 'is_active' => true]);
        $schoolClass = SchoolClass::create(['name' => '12.2', 'grade_level' => 12, 'is_active' => true]);
        $user = User::factory()->create([
            'course_id' => $course->id,
            'school_class_id' => $schoolClass->id,
        ]);

        $this->actingAs($user)
            ->get(route('settings'))
            ->assertOk()
            ->assertSee('Settings')
            ->assertSee('Languages')
            ->assertSee('12.2')
            ->assertSee('Send an email');
    }
}
