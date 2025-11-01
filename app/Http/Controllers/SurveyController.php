<?php

namespace App\Http\Controllers;

use App\Models\SurveyAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SurveyController extends Controller
{
    public function showForm()
    {
        if (!Auth::check()) {
            return redirect()->route('home');
        }

        return view('survey.form');
    }

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('home');
        }

        $request->validate([
            'q1' => 'required|integer|between:1,5',
            'q2' => 'required|integer|between:1,5',
            'q3' => 'required|integer|between:1,5',
            'q4' => 'required|integer|between:1,5',
            'q5' => 'required|integer|between:1,5',
            'q6' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000'
        ]);

        SurveyAnswer::create([
            'user_id' => Auth::id(),
            'q1' => $request->q1,
            'q2' => $request->q2,
            'q3' => $request->q3,
            'q4' => $request->q4,
            'q5' => $request->q5,
            'q6' => $request->q6,
            'comment' => $request->comment,
            'created_at' => now()
        ]);

        return redirect()->route('home')
            ->with('success', 'Merci pour votre retour !');
    }
}