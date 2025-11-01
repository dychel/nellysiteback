<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SurveyAnswer;
use Illuminate\Http\Request;

class AdminSurveyController extends Controller
{
    public function index(Request $request)
    {
        $query = SurveyAnswer::with('user');

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $surveys = $query->latest()->paginate(20);

        // Statistiques
        $stats = [
            'totalSurveys' => SurveyAnswer::count(),
            'averageQ1' => SurveyAnswer::avg('q1'),
            'averageQ2' => SurveyAnswer::avg('q2'),
            'averageQ3' => SurveyAnswer::avg('q3'),
            'averageQ4' => SurveyAnswer::avg('q4'),
            'averageQ5' => SurveyAnswer::avg('q5'),
            'averageQ6' => SurveyAnswer::avg('q6'),
            'totalAverage' => SurveyAnswer::selectRaw('AVG((q1 + q2 + q3 + q4 + q5 + q6) / 6) as avg')->value('avg')
        ];

        return view('admin.surveys.index', compact('surveys', 'stats'));
    }

    public function show(SurveyAnswer $survey)
    {
        $survey->load('user');
        return view('admin.surveys.show', compact('survey'));
    }

    public function stats()
    {
        $stats = [
            'totalSurveys' => SurveyAnswer::count(),
            'averageRatings' => [
                'q1' => SurveyAnswer::avg('q1'),
                'q2' => SurveyAnswer::avg('q2'),
                'q3' => SurveyAnswer::avg('q3'),
                'q4' => SurveyAnswer::avg('q4'),
                'q5' => SurveyAnswer::avg('q5'),
                'q6' => SurveyAnswer::avg('q6'),
            ],
            'recentSurveys' => SurveyAnswer::with('user')->latest()->limit(10)->get()
        ];

        return view('admin.surveys.stats', compact('stats'));
    }
}