<?php

namespace Tests\Unit\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_is_created_successfully_with_valid_data(): void
    {
        $user = User::create([
            'name' => 'Test',
            'email' => 'test@test.com',
            'password' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@test.com',
            'name' => 'Test',
        ]);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertNotEquals('password123', $user->password);
    }

    public function test_user_creation_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'duplicate@test.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::create([
            'name' => 'Other',
            'email' => 'duplicate@test.com',
            'password' => 'password123',
        ]);
    }

    public function test_password_is_securely_hashed_upon_creation(): void
    {
        $user = User::create([
            'name' => 'HashTest',
            'email' => 'hash@test.com',
            'password' => 'securePass123',
        ]);

        $this->assertTrue(Hash::check('securePass123', $user->password));
        $this->assertNotSame('securePass123', $user->password);
    }

    public function test_user_can_update_profile_name(): void
    {
        $user = User::factory()->create(['name' => 'Original Name']);

        $user->name = 'Updated Name';
        $user->save();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_user_cannot_update_to_an_existing_email(): void
    {
        $existingUser = User::factory()->create(['email' => 'existing@test.com']);
        $currentUser = User::factory()->create(['email' => 'current@test.com']);

        $data = [
            'name' => 'Updated Name',
            'email' => $existingUser->email,
        ];

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($currentUser->id)],
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->messages());
    }
}
