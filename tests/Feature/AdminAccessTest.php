<?php

namespace Tests\Feature;

use App\Models\Municipality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => '管理者', 'email' => 'a@locona.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN, 'is_active' => true,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_log_in_and_view_dashboard(): void
    {
        $this->actingAs($this->admin())->get('/admin')->assertOk()->assertSee('ダッシュボード');
    }

    public function test_operator_cannot_view_audit_logs(): void
    {
        $operator = User::create([
            'name' => '運用', 'email' => 'o@locona.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_OPERATOR, 'is_active' => true,
        ]);

        $this->actingAs($operator)->get('/admin/audit-logs')->assertForbidden();
        $this->actingAs($this->admin())->get('/admin/audit-logs')->assertOk();
    }

    public function test_admin_can_create_municipality_and_it_is_audited(): void
    {
        $this->actingAs($this->admin())->post('/admin/municipalities', [
            'name' => '新規町', 'prefecture' => 'テスト県', 'is_published' => '1',
        ])->assertRedirect(route('admin.municipalities.index'));

        $this->assertDatabaseHas('municipalities', ['name' => '新規町']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'created', 'target_type' => 'Municipality']);
    }

    public function test_inactive_user_cannot_access_admin(): void
    {
        $user = User::create([
            'name' => '無効', 'email' => 'x@locona.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_OPERATOR, 'is_active' => false,
        ]);

        $this->actingAs($user)->get('/admin')->assertRedirect(route('admin.login'));
    }
}
