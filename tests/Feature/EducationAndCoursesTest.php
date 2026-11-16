<?php

namespace Tests\Feature;

use Tests\TestCase;

class EducationAndCoursesTest extends TestCase
{
    public function test_public_can_access_courses_catalog(): void
    {
        $response = $this->getJson('/api/v1/courses');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data',
        ]);
    }
}
