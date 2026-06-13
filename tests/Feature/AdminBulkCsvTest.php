<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminBulkCsvTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => '管理者', 'email' => 'a@locona.test',
            'password' => Hash::make('password'), 'role' => User::ROLE_ADMIN, 'is_active' => true,
        ]);
    }

    private function municipality(): Municipality
    {
        return Municipality::create(['name' => '町', 'slug' => 'town', 'prefecture' => '県', 'is_published' => true]);
    }

    public function test_bulk_publish_updates_status_and_audits(): void
    {
        $m = $this->municipality();
        $a1 = Activity::create(['municipality_id' => $m->id, 'title' => 'A1', 'slug' => 'a1', 'summary' => 'x', 'source_url' => 'https://e.com', 'status' => 'draft']);
        $a2 = Activity::create(['municipality_id' => $m->id, 'title' => 'A2', 'slug' => 'a2', 'summary' => 'x', 'source_url' => 'https://e.com', 'status' => 'draft']);

        $this->actingAs($this->admin())
            ->post('/admin/activities/bulk', ['ids' => [$a1->id, $a2->id], 'bulk_action' => 'publish'])
            ->assertRedirect();

        $this->assertEquals('published', $a1->fresh()->status);
        $this->assertEquals('published', $a2->fresh()->status);
        $this->assertNotNull($a1->fresh()->verified_at);
        $this->assertDatabaseHas('audit_logs', ['action' => 'bulk_publish']);
    }

    public function test_municipalities_csv_export(): void
    {
        $this->municipality();

        $res = $this->actingAs($this->admin())->get('/admin/csv/municipalities');
        $res->assertOk();
        $this->assertStringContainsString('text/csv', $res->headers->get('Content-Type'));
        $this->assertStringContainsString('町', $res->streamedContent());
    }

    public function test_municipalities_csv_import_creates_records(): void
    {
        $csv = "name,prefecture,city,summary,is_published\n伊那市,長野県,伊那市,テスト,1\n";
        $file = UploadedFile::fake()->createWithContent('m.csv', $csv);

        $this->actingAs($this->admin())
            ->post('/admin/csv/import/municipalities', ['file' => $file])
            ->assertRedirect(route('admin.municipalities.index'));

        $this->assertDatabaseHas('municipalities', ['name' => '伊那市', 'prefecture' => '長野県']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'csv_import']);
    }
}
