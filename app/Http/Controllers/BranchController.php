<?php

namespace App\Http\Controllers;

use App\Models\Branch;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::with('cashbox')->get();
        return view('branches.index', compact('branches'));
    }
}