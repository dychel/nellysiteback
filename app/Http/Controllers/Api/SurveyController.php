<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SurveyAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SurveyController extends Controller
{
    public function index()
    {
        $surveys = SurveyAnswer::where('user_id', Auth::id())
                              ->orderBy('created_at', 'desc')
                              ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $surveys
        ]);
    }

    public function show($id)
    {
        $survey = SurveyAnswer::where('id', $id)
                             ->where('user_id', Auth::id())
                             ->first();

        if (!$survey) {
            return response()->json([
                'success' => false,
                'message' => 'Enquête non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $survey
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q1' => 'required|integer|between:1,5',
            'q2' => 'required|integer|between:1,5',
            'q3' => 'required|integer|between:1,5',
            'q4' => 'required|integer|between:1,5',
            'q5' => 'required|integer|between:1,5',
            'q6' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $survey = SurveyAnswer::create([
            'user_id' => Auth::id(),
            'q1' => $request->q1,
            'q2' => $request->q2,
            'q3' => $request->q3,
            'q4' => $request->q4,
            'q5' => $request->q5,
            'q6' => $request->q6,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Enquête enregistrée avec succès',
            'data' => $survey
        ], 201);
    }
}