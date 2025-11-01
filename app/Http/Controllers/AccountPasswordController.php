<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AccountPasswordController extends Controller
{
    public function index()
    {
        return view('account.password');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->with('error', 'Votre mot de passe actuel n\'est pas le bon.');
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->back()
            ->with('success', 'Votre mot de passe a bien été mis à jour');
    }
}