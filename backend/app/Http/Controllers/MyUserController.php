<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MyUserController extends Controller
{
    public function createCurrentUser(Request $request)
    {
        try {
            // Extract auth0Id from the request body
            $auth0Id = $request->input('auth0Id');
    
            // Check if the user already exists
            $existingUser = User::where('auth0Id', $auth0Id)->first();
    
            if ($existingUser) {
                return response()->json([], 200); // User already exists
            }
    
            // Extract the second half of auth0Id after '|'
            $customId = explode('|', $auth0Id)[1] ?? null;
    
            if (!$customId) {
                return response()->json(['message' => 'Invalid auth0Id format'], 400); // Bad request
            }
    
            // Create a new user with the custom `_id`
            $newUser = User::create([
                '_id' => $customId, // Set custom _id
                'auth0Id' => $auth0Id,
                'email' => $request->input('email'),
                'name' => $request->input('name'),
                'addressLine1' => $request->input('addressLine1'),
                'city' => $request->input('city'),
                'country' => $request->input('country'),
            ]);
    
            return response()->json($newUser, 201); // Return the created user
    
        } catch (\Exception $error) {
            logger()->error($error); // Log the error for debugging
            return response()->json(['message' => 'Error creating user'], 500);
        }
    }
    

    public function getCurrentUser(Request $request)
    {
        try {
            // Retrieve the current authenticated user
            $userId = $request->userId; // Assuming middleware sets the authenticated user
            $currentUser = User::where('auth0Id', $userId)->first();

            if (!$currentUser) {
                return response()->json(['message' => 'User not found', 'sub' => $userId], 404);
            }

            return response()->json($currentUser);

        } catch (\Exception $error) {
            logger()->error($error);
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    public function updateCurrentUser(Request $request)
    {
        try {
            $userId = $request->userId; // Assuming middleware sets the authenticated user
            $user = User::where('auth0Id', $userId)->first();

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            // Update user details
            $user->name = $request->input('name');
            $user->addressLine1 = $request->input('addressLine1');
            $user->city = $request->input('city');
            $user->country = $request->input('country');

            $user->save();

            return response()->json($user);

        } catch (\Exception $error) {
            logger()->error($error);
            return response()->json(['message' => 'Error updating user'], 500);
        }
    }
}
