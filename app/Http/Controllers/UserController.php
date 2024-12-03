<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Assuming User is the model for the new users

class UserController extends Controller
{
    public function createUser(Request $request)
    {
        // Validate the request
        $request->validate([
            'surname' => 'required|string|max:255',
            'firstname' => 'required|string|max:255',
            'othername' => 'nullable|string|max:255',
            'sex' => 'required|string|in:male,female,other',
            'marital_status' => 'required|string|in:single,married,divorced',
            'phoneNumber' => 'required|string|max:15|unique:users',
            'localgovernment' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'occupation' => 'required|string|max:255',
            'shop_address' => 'nullable|string|max:255',
            'purpose' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);

        // Create the user
        $user = User::create([
            'surname' => $request->surname,
            'firstname' => $request->firstname,
            'othername' => $request->othername,
            'sex' => $request->sex,
            'marital_status' => $request->marital_status,
            'phoneNumber' => $request->phoneNumber,
            'localgovernment' => $request->localgovernment,
            'address' => $request->address,
            'occupation' => $request->occupation,
            'shop_address' => $request->shop_address,
            'purpose' => $request->purpose,
            'amount' => $request->amount,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
        ], 201);
    }
}