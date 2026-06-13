<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Municipality;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * 自治体管理（ADM-003 / ADM-03）。
 */
class MunicipalityController extends Controller
{
    public function index(Request $request)
    {
        $municipalities = Municipality::query()
            ->when($request->input('q'), fn ($q, $k) => $q->where('name', 'like', "%{$k}%"))
            ->withCount('activities')
            ->orderBy('prefecture')->orderBy('name')
            ->paginate(20)->withQueryString();

        return view('admin.municipalities.index', compact('municipalities'));
    }

    public function create()
    {
        return view('admin.municipalities.form', ['municipality' => new Municipality()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $municipality = Municipality::create($data);
        Audit::log('created', $municipality, $data);

        return redirect()->route('admin.municipalities.index')->with('status', '自治体を登録しました。');
    }

    public function edit(Municipality $municipality)
    {
        return view('admin.municipalities.form', compact('municipality'));
    }

    public function update(Request $request, Municipality $municipality)
    {
        $data = $this->validateData($request, $municipality);
        $municipality->update($data);
        Audit::log('updated', $municipality, $data);

        return redirect()->route('admin.municipalities.index')->with('status', '自治体を更新しました。');
    }

    public function destroy(Municipality $municipality)
    {
        Audit::log('deleted', $municipality);
        $municipality->delete();

        return redirect()->route('admin.municipalities.index')->with('status', '自治体を削除しました。');
    }

    private function validateData(Request $request, ?Municipality $municipality = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'catchphrase' => ['nullable', 'string', 'max:255'],
            'prefecture' => ['required', 'string', 'max:16'],
            'city' => ['nullable', 'string', 'max:64'],
            'summary' => ['nullable', 'string'],
            'official_url' => ['nullable', 'url', 'max:255'],
            'image_url' => ['nullable', 'url', 'max:1024'],
            'line_url' => ['nullable', 'url', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_url' => ['nullable', 'url', 'max:255'],
            'related_urls_text' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        // 関連URLは1行1URLで入力させ配列化する
        $related = collect(preg_split('/\r\n|\r|\n/', (string) $request->input('related_urls_text')))
            ->map(fn ($u) => trim($u))->filter()->values()->all();
        $data['related_urls'] = $related ?: null;
        unset($data['related_urls_text']);

        $data['is_published'] = $request->boolean('is_published');
        $data['slug'] = $municipality?->slug ?: $this->uniqueSlug($data['name']);

        return $data;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'm-'.Str::lower(Str::random(6));
        $slug = $base;
        $i = 1;
        while (Municipality::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
