<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\DuplicateFinder;

/**
 * 重複候補一覧（ADM-009 / CRW-011）。
 */
class DuplicateController extends Controller
{
    public function index(DuplicateFinder $finder)
    {
        $groups = $finder->candidates();

        return view('admin.duplicates.index', compact('groups'));
    }
}
