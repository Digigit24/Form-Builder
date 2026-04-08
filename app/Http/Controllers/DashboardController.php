<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Response;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $forms = Form::withCount('responses')->latest()->get();
        $totalResponses = Response::count();

        return view('dashboard', [
            'forms' => $forms,
            'totalResponses' => $totalResponses,
        ]);
    }
}
