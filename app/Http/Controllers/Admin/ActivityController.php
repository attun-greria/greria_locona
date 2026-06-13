<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityCategory;
use App\Models\Municipality;
use App\Models\Tag;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * 活動管理（ADM-004/005 / ADM-04）。
 * 登録・編集・複製・公開状態管理・改訂履歴を扱う。
 */
class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $activities = Activity::query()
            ->with(['municipality', 'category'])
            ->when($request->input('q'), fn ($q, $k) => $q->where('title', 'like', "%{$k}%"))
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->input('municipality'), fn ($q, $m) => $q->where('municipality_id', $m))
            ->latest('updated_at')
            ->paginate(20)->withQueryString();

        $municipalities = Municipality::orderBy('name')->get();

        return view('admin.activities.index', compact('activities', 'municipalities'));
    }

    public function create()
    {
        return view('admin.activities.form', $this->formData(new Activity(['status' => 'draft'])));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $tagIds = $data['tags'] ?? [];
        unset($data['tags']);

        $data['slug'] = $this->uniqueSlug($data['title']);
        $activity = Activity::create($data);
        $activity->tags()->sync($tagIds);

        Audit::log('created', $activity, $data);

        return redirect()->route('admin.activities.edit', $activity)->with('status', '活動を登録しました。');
    }

    public function edit(Activity $activity)
    {
        return view('admin.activities.form', $this->formData($activity));
    }

    public function update(Request $request, Activity $activity)
    {
        $before = $activity->getOriginal();
        $data = $this->validateData($request, $activity);
        $tagIds = $data['tags'] ?? [];
        unset($data['tags']);

        $activity->update($data);
        $activity->tags()->sync($tagIds);

        // 改訂履歴（activity_revisions）
        $activity->revisions()->create([
            'user_id' => $request->user()->id,
            'before' => array_intersect_key($before, $data),
            'after' => $data,
            'reason' => $request->input('revision_reason'),
        ]);
        Audit::log('updated', $activity, $activity->getChanges());

        return redirect()->route('admin.activities.edit', $activity)->with('status', '活動を更新しました。');
    }

    /** 複製（ADM-004） */
    public function duplicate(Activity $activity)
    {
        $copy = $activity->replicate(['slug', 'click_count']);
        $copy->title = $activity->title.'（複製）';
        $copy->slug = $this->uniqueSlug($copy->title);
        $copy->status = 'draft';
        $copy->save();
        $copy->tags()->sync($activity->tags->pluck('id'));
        Audit::log('duplicated', $copy, ['from' => $activity->id]);

        return redirect()->route('admin.activities.edit', $copy)->with('status', '活動を複製しました。');
    }

    /** 公開ステータス変更（ADM-005） */
    public function changeStatus(Request $request, Activity $activity)
    {
        $request->validate(['status' => ['required', 'in:'.implode(',', Activity::STATUSES)]]);
        $status = $request->input('status');

        $activity->status = $status;
        if ($status === 'published' && ! $activity->verified_at) {
            $activity->verified_at = now();
        }
        $activity->save();

        Audit::log('status_changed', $activity, ['status' => $status]);

        return back()->with('status', "ステータスを「{$activity->statusLabel()}」に変更しました。");
    }

    public function destroy(Activity $activity)
    {
        Audit::log('deleted', $activity);
        $activity->delete();

        return redirect()->route('admin.activities.index')->with('status', '活動を削除しました。');
    }

    private function formData(Activity $activity): array
    {
        return [
            'activity' => $activity->load('tags'),
            'municipalities' => Municipality::orderBy('name')->get(),
            'categories' => ActivityCategory::orderBy('display_order')->get(),
            'tags' => Tag::orderBy('type')->orderBy('name')->get(),
        ];
    }

    private function validateData(Request $request, ?Activity $activity = null): array
    {
        $data = $request->validate([
            'municipality_id' => ['required', 'exists:municipalities,id'],
            'category_id' => ['nullable', 'exists:activity_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['required', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'source_url' => ['required', 'url', 'max:1024'],
            'apply_url' => ['nullable', 'url', 'max:1024'],
            'organizer_name' => ['nullable', 'string', 'max:255'],
            'application_deadline' => ['nullable', 'date'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'fee_text' => ['nullable', 'string', 'max:255'],
            'target_audience' => ['nullable', 'string', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:'.implode(',', Activity::STATUSES)],
            'verified_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
        ]);

        foreach (['is_recurring', 'child_friendly', 'beginner_friendly', 'online_available', 'has_reward', 'transport_support', 'lodging_support'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }

        return $data;
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'a-'.Str::lower(Str::random(6));
        $slug = $base;
        $i = 1;
        while (Activity::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
