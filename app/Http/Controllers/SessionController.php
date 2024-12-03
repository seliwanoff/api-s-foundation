<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;

class SessionController extends Controller
{
    public function clearSession()
    {
        Session::flush();
        return response()->json(['message' => 'All session data has been cleared.']);
    }
}