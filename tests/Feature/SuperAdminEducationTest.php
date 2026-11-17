<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\EducationCategory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminEducationTest extends TestCase
{
    protected function getSuperAdmin(): User
    {
        $admin = User::where('email', 'admin@ktsmarkets.com')->first();
        if (!$admin) {
            $admin = User::create([
                'name' => 'KTS Admin',
                'email' => 'admin@ktsmarkets.com',
                'password' => Hash::make('Password123!'),
                'status' => 'active',
            ]);
        }

        Role::firstOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Administrator', 'description' => 'Full administrative access']
        );

        if (!$admin->hasRole('super-admin')) {
            $admin->assignRole('super-admin');
        }

        return $admin;
    }

    public function test_education_category_and_course_lifecycle(): void
    {
        $admin = $this->getSuperAdmin();

        // 1. Admin creates education category
        $catName = 'Algorithmic Systems ' . uniqid();
        $catResponse = $this->actingAs($admin, 'web')->post(route('admin.education-categories.store'), [
            'name' => $catName,
            'description' => 'Automated bot coding and risk management',
            'icon' => 'cpu',
            'color' => '#10B981',
            'is_active' => true,
        ]);
        $catResponse->assertSessionHasNoErrors();

        $category = EducationCategory::where('name', $catName)->first();
        $this->assertNotNull($category);

        // 2. Admin creates course in category
        $courseTitle = 'Mastering 10 Pips Scalper EA ' . uniqid();
        $courseResponse = $this->actingAs($admin, 'web')->post(route('admin.courses.store'), [
            'title' => $courseTitle,
            'description' => 'Learn how to configure and execute trades using the KTS 10 Pips EA.',
            'category_id' => $category->id,
            'difficulty' => 'intermediate',
            'estimated_hours' => 5,
            'is_published' => false,
        ]);
        $courseResponse->assertSessionHasNoErrors();

        $course = Course::where('title', $courseTitle)->first();
        $this->assertNotNull($course);
        $this->assertFalse((bool) $course->is_published);

        // 3. Admin publishes course
        $publishResponse = $this->actingAs($admin, 'web')->post(route('admin.courses.publish', $course));
        $publishResponse->assertSessionHasNoErrors();

        $course->refresh();
        $this->assertTrue((bool) $course->is_published);

        // 4. Mobile API reflects published course
        $mobileResponse = $this->getJson('/api/v1/courses');
        $mobileResponse->assertStatus(200);
        $courseIds = collect($mobileResponse->json('data.data'))->pluck('id')->toArray();
        $this->assertContains($course->id, $courseIds);
    }
}
