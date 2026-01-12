<?php

namespace Tests\Feature\Api\V1;

use App\Models\BootstrapState;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class BootstrapTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test getting bootstrap status when pending.
     */
    public function test_get_bootstrap_status_when_pending(): void
    {
        $response = $this->getJson('/api/v1/bootstrap/status');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'super_admin_exists',
                'super_admin_email',
                'created_at',
                'confirmed_at',
                'can_create',
                'can_confirm',
            ])
            ->assertJson([
                'status' => 'BOOTSTRAP_PENDING',
                'super_admin_exists' => false,
                'super_admin_email' => null,
                'can_create' => true,
                'can_confirm' => false,
            ]);
    }

    /**
     * Test creating first Super Admin successfully.
     */
    public function test_create_super_admin_successfully(): void
    {
        $data = [
            'email' => 'admin@metatech.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ];

        $response = $this->postJson('/api/v1/bootstrap/create', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'email',
                    'first_name',
                    'last_name',
                    'role',
                    'email_verified_at',
                    'created_at',
                ],
                'status',
                'requires_confirmation',
                'next_step',
            ])
            ->assertJson([
                'message' => 'Super Admin created successfully',
                'user' => [
                    'email' => 'admin@metatech.com',
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'role' => 'super_admin',
                ],
                'status' => 'BOOTSTRAP_CONFIRMED',
                'requires_confirmation' => true,
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@metatech.com',
            'role' => 'super_admin',
        ]);

        $this->assertDatabaseHas('bootstrap_states', [
            'status' => 'BOOTSTRAP_CONFIRMED',
            'super_admin_email' => 'admin@metatech.com',
        ]);
    }

    /**
     * Test creating Super Admin fails with weak password.
     */
    public function test_create_super_admin_fails_with_weak_password(): void
    {
        $data = [
            'email' => 'admin@metatech.com',
            'password' => 'weak',
            'password_confirmation' => 'weak',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ];

        $response = $this->postJson('/api/v1/bootstrap/create', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test creating Super Admin fails with invalid email.
     */
    public function test_create_super_admin_fails_with_invalid_email(): void
    {
        $data = [
            'email' => 'invalid-email',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ];

        $response = $this->postJson('/api/v1/bootstrap/create', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test creating Super Admin fails when password confirmation doesn't match.
     */
    public function test_create_super_admin_fails_password_mismatch(): void
    {
        $data = [
            'email' => 'admin@metatech.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'DifferentPass123!',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ];

        $response = $this->postJson('/api/v1/bootstrap/create', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password_confirmation']);
    }

    /**
     * Test creating Super Admin fails when already exists.
     */
    public function test_create_super_admin_fails_when_already_exists(): void
    {
        // Create Super Admin first
        $user = User::create([
            'email' => 'admin@metatech.com',
            'password' => Hash::make('SecurePass123!'),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'name' => 'John Doe',
            'role' => 'super_admin',
        ]);

        $bootstrap = BootstrapState::current();
        $bootstrap->update([
            'status' => 'BOOTSTRAP_CONFIRMED',
            'super_admin_email' => $user->email,
            'super_admin_id' => $user->id,
        ]);

        $data = [
            'email' => 'admin@metatech.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ];

        $response = $this->postJson('/api/v1/bootstrap/create', $data);

        $response->assertStatus(409)
            ->assertJson([
                'error_code' => 'SUPER_ADMIN_EXISTS',
            ]);
    }

    /**
     * Test creating Super Admin fails when regular user exists with email.
     */
    public function test_create_super_admin_fails_when_regular_user_exists(): void
    {
        // Create a regular user with the email
        User::create([
            'email' => 'user@metatech.com',
            'password' => Hash::make('Password123!'),
            'first_name' => 'Regular',
            'last_name' => 'User',
            'name' => 'Regular User',
            'role' => 'user',
        ]);

        $data = [
            'email' => 'user@metatech.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ];

        $response = $this->postJson('/api/v1/bootstrap/create', $data);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Email already taken',
            ]);
    }

    /**
     * Test creating Super Admin fails when bootstrap is already active.
     */
    public function test_create_super_admin_fails_when_bootstrap_active(): void
    {
        // Create Super Admin and activate bootstrap
        $user = User::create([
            'email' => 'admin@metatech.com',
            'password' => Hash::make('SecurePass123!'),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'name' => 'John Doe',
            'role' => 'super_admin',
        ]);

        $bootstrap = BootstrapState::current();
        $bootstrap->update([
            'status' => 'ACTIVE',
            'super_admin_email' => $user->email,
            'super_admin_id' => $user->id,
            'confirmed_at' => now(),
        ]);

        $data = [
            'email' => 'admin2@metatech.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ];

        $response = $this->postJson('/api/v1/bootstrap/create', $data);

        $response->assertStatus(403)
            ->assertJson([
                'error_code' => 'BOOTSTRAP_ALREADY_COMPLETED',
            ]);
    }

    /**
     * Test getting bootstrap status when confirmed.
     */
    public function test_get_bootstrap_status_when_confirmed(): void
    {
        $user = User::create([
            'email' => 'admin@metatech.com',
            'password' => Hash::make('SecurePass123!'),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'name' => 'John Doe',
            'role' => 'super_admin',
        ]);

        $bootstrap = BootstrapState::current();
        $bootstrap->update([
            'status' => 'BOOTSTRAP_CONFIRMED',
            'super_admin_email' => $user->email,
            'super_admin_id' => $user->id,
        ]);

        $response = $this->getJson('/api/v1/bootstrap/status');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'BOOTSTRAP_CONFIRMED',
                'super_admin_exists' => true,
                'super_admin_email' => 'admin@metatech.com',
                'can_create' => false,
                'can_confirm' => true,
            ]);
    }

    /**
     * Test confirming bootstrap successfully.
     */
    public function test_confirm_bootstrap_successfully(): void
    {
        $user = User::create([
            'email' => 'admin@metatech.com',
            'password' => Hash::make('SecurePass123!'),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'name' => 'John Doe',
            'role' => 'super_admin',
        ]);

        $bootstrap = BootstrapState::current();
        $bootstrap->update([
            'status' => 'BOOTSTRAP_CONFIRMED',
            'super_admin_email' => $user->email,
            'super_admin_id' => $user->id,
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/bootstrap/confirm', []);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'status',
                'confirmed_at',
                'system_ready',
            ])
            ->assertJson([
                'status' => 'ACTIVE',
                'system_ready' => true,
            ]);

        $this->assertDatabaseHas('bootstrap_states', [
            'status' => 'ACTIVE',
            'super_admin_id' => $user->id,
        ]);
    }

    /**
     * Test confirming bootstrap fails without authentication.
     */
    public function test_confirm_bootstrap_fails_without_auth(): void
    {
        $response = $this->postJson('/api/v1/bootstrap/confirm', []);

        $response->assertStatus(401);
    }

    /**
     * Test confirming bootstrap fails when not super admin.
     */
    public function test_confirm_bootstrap_fails_when_not_super_admin(): void
    {
        $user = User::create([
            'email' => 'user@metatech.com',
            'password' => Hash::make('SecurePass123!'),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'name' => 'John Doe',
            'role' => 'user',
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/bootstrap/confirm', []);

        $response->assertStatus(403)
            ->assertJson([
                'error_code' => 'INSUFFICIENT_PERMISSIONS',
            ]);
    }

    /**
     * Test confirming bootstrap fails when already confirmed.
     */
    public function test_confirm_bootstrap_fails_when_already_confirmed(): void
    {
        $user = User::create([
            'email' => 'admin@metatech.com',
            'password' => Hash::make('SecurePass123!'),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'name' => 'John Doe',
            'role' => 'super_admin',
        ]);

        $bootstrap = BootstrapState::current();
        $bootstrap->update([
            'status' => 'ACTIVE',
            'super_admin_email' => $user->email,
            'super_admin_id' => $user->id,
            'confirmed_at' => now(),
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/bootstrap/confirm', []);

        $response->assertStatus(403)
            ->assertJson([
                'error_code' => 'BOOTSTRAP_ALREADY_CONFIRMED',
            ]);
    }

    /**
     * Test getting audit logs requires authentication.
     */
    public function test_get_audit_logs_requires_auth(): void
    {
        $response = $this->getJson('/api/v1/bootstrap/audit');

        $response->assertStatus(401);
    }

    /**
     * Test getting audit logs requires super admin.
     */
    public function test_get_audit_logs_requires_super_admin(): void
    {
        $user = User::create([
            'email' => 'user@metatech.com',
            'password' => Hash::make('SecurePass123!'),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'name' => 'John Doe',
            'role' => 'user',
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/bootstrap/audit');

        $response->assertStatus(403)
            ->assertJson([
                'error_code' => 'INSUFFICIENT_PERMISSIONS',
            ]);
    }

    /**
     * Test getting audit logs successfully.
     */
    public function test_get_audit_logs_successfully(): void
    {
        $user = User::create([
            'email' => 'admin@metatech.com',
            'password' => Hash::make('SecurePass123!'),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'name' => 'John Doe',
            'role' => 'super_admin',
        ]);

        $token = JWTAuth::fromUser($user);

        // Create some audit logs by creating a super admin
        $this->postJson('/api/v1/bootstrap/create', [
            'email' => 'admin@metatech.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/bootstrap/audit');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'action',
                        'result',
                        'ip_address',
                        'user_id',
                        'email',
                        'request_payload',
                        'error_message',
                        'created_at',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                    'from',
                    'to',
                ],
            ]);
    }

    /**
     * Test getting bootstrap status when active.
     */
    public function test_get_bootstrap_status_when_active(): void
    {
        $user = User::create([
            'email' => 'admin@metatech.com',
            'password' => Hash::make('SecurePass123!'),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'name' => 'John Doe',
            'role' => 'super_admin',
        ]);

        $bootstrap = BootstrapState::current();
        $bootstrap->update([
            'status' => 'ACTIVE',
            'super_admin_email' => $user->email,
            'super_admin_id' => $user->id,
            'confirmed_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/bootstrap/status');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ACTIVE',
                'super_admin_exists' => true,
                'super_admin_email' => 'admin@metatech.com',
                'can_create' => false,
                'can_confirm' => false,
            ])
            ->assertJsonStructure([
                'confirmed_at',
            ]);
    }
}
