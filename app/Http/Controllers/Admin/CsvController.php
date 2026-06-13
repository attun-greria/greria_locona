<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Municipality;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * CSV入出力（ADM-012）。
 * 自治体・活動のエクスポート、自治体のインポートに対応する。
 */
class CsvController extends Controller
{
    /** 活動CSVエクスポート */
    public function exportActivities(): StreamedResponse
    {
        Audit::log('csv_export', null, ['type' => 'activities']);

        $columns = ['id', 'title', 'municipality', 'prefecture', 'category', 'status',
            'source_url', 'apply_url', 'application_deadline', 'start_at', 'end_at',
            'fee_text', 'child_friendly', 'online_available', 'verified_at'];

        return $this->stream('activities.csv', $columns, function ($out) {
            Activity::with(['municipality', 'category'])->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $a) {
                    fputcsv($out, [
                        $a->id, $a->title, $a->municipality?->name, $a->municipality?->prefecture,
                        $a->category?->name, $a->status, $a->source_url, $a->apply_url,
                        optional($a->application_deadline)->toDateString(),
                        optional($a->start_at)->toDateTimeString(),
                        optional($a->end_at)->toDateTimeString(),
                        $a->fee_text, $a->child_friendly ? '1' : '0',
                        $a->online_available ? '1' : '0',
                        optional($a->verified_at)->toDateString(),
                    ]);
                }
            });
        });
    }

    /** 自治体CSVエクスポート */
    public function exportMunicipalities(): StreamedResponse
    {
        Audit::log('csv_export', null, ['type' => 'municipalities']);

        $columns = ['id', 'name', 'prefecture', 'city', 'summary', 'official_url', 'line_url', 'is_published'];

        return $this->stream('municipalities.csv', $columns, function ($out) {
            Municipality::chunk(200, function ($rows) use ($out) {
                foreach ($rows as $m) {
                    fputcsv($out, [
                        $m->id, $m->name, $m->prefecture, $m->city, $m->summary,
                        $m->official_url, $m->line_url, $m->is_published ? '1' : '0',
                    ]);
                }
            });
        });
    }

    /** 自治体CSVインポートフォーム */
    public function importForm()
    {
        return view('admin.csv.import');
    }

    /**
     * 自治体CSVインポート。
     * ヘッダ: name, prefecture, city, summary, official_url, line_url, is_published
     */
    public function importMunicipalities(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimetypes:text/plain,text/csv,application/csv', 'max:2048'],
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $header = fgetcsv($handle);
        if (! $header) {
            return back()->withErrors(['file' => 'CSVが空です。']);
        }
        $header = array_map(fn ($h) => trim((string) $h), $header);

        $created = 0;
        $updated = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count(array_filter($row, fn ($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }
            $data = array_combine($header, array_pad($row, count($header), null));
            if (empty($data['name']) || empty($data['prefecture'])) {
                continue;
            }

            $slug = Str::slug($data['name']) ?: 'm-'.Str::lower(Str::random(6));
            $existing = Municipality::where('slug', $slug)->first();
            $payload = [
                'name' => $data['name'],
                'prefecture' => $data['prefecture'],
                'city' => $data['city'] ?? null,
                'summary' => $data['summary'] ?? null,
                'official_url' => $data['official_url'] ?? null,
                'line_url' => $data['line_url'] ?? null,
                'is_published' => isset($data['is_published']) ? (bool) (int) $data['is_published'] : true,
                'slug' => $slug,
            ];
            if ($existing) {
                $existing->update($payload);
                $updated++;
            } else {
                Municipality::create($payload);
                $created++;
            }
        }
        fclose($handle);

        Audit::log('csv_import', null, ['type' => 'municipalities', 'created' => $created, 'updated' => $updated]);

        return redirect()->route('admin.municipalities.index')
            ->with('status', "CSVインポート完了：新規 {$created}件 / 更新 {$updated}件");
    }

    private function stream(string $filename, array $columns, callable $writer): StreamedResponse
    {
        return response()->streamDownload(function () use ($columns, $writer) {
            $out = fopen('php://output', 'w');
            fprintf($out, "\xEF\xBB\xBF"); // BOM（Excelで文字化けを防ぐ）
            fputcsv($out, $columns);
            $writer($out);
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
