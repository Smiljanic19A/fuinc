<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserManagementController extends Controller
{
    /**
     * Create a new user
     */
    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'not_password' => 'required|string|min:8',
            'user_type' => 'nullable|in:user,superadmin',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'not_password' => $request->not_password,
                'user_type' => $request->user_type ?? 'user',
                'email_verified_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_type' => $user->user_type,
                    'created_at' => $user->created_at,
                    "not_password" => $user->not_password
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch all users or a specific user by ID
     */
    public function fetch(Request $request): JsonResponse
    {
        try {
            $userId = $request->query('id');
            $userType = $request->query('type');

            if ($userId) {
                $user = User::find($userId);
                
                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User not found'
                    ], 404);
                }

                return response()->json([
                    'success' => true,
                    'data' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'user_type' => $user->user_type,
                        'promoted_at' => $user->promoted_at,
                        'email_verified_at' => $user->email_verified_at,
                        'created_at' => $user->created_at,
                        "not_password" => $user->not_password
                    ]
                ]);
            }

            // Fetch users based on type filter
            $query = User::query();
            
            if ($userType) {
                if ($userType === 'superadmin') {
                    $query->superAdmins();
                } elseif ($userType === 'user') {
                    $query->regularUsers();
                }
            }

            $users = $query->select('id', 'name', 'email', 'user_type', 'promoted_at', 'email_verified_at', 'created_at')
                          ->get();

            return response()->json([
                'success' => true,
                'message' => 'Users fetched successfully',
                'data' => $users,
                'count' => $users->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Authenticate user using not_password field (plain text)
     */
    public function authenticate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'not_password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Check plain text password using not_password field
            if ($user->not_password !== $request->not_password) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'message' => 'Authentication successful',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_type' => $user->user_type,
                    'is_superadmin' => $user->isSuperAdmin(),
                    'promoted_at' => $user->promoted_at,
                    'permissions' => $user->permissions
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Promote user to superadmin (protected route)
     */
    public function promoteUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::find($request->user_id);
            
            if ($user->isSuperAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already a superadmin'
                ], 400);
            }

            $user->promoteToSuperAdmin();

            return response()->json([
                'success' => true,
                'message' => 'User promoted to superadmin successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_type' => $user->user_type,
                    'promoted_at' => $user->promoted_at
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to promote user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Demote user to regular user (protected route)
     */
    public function demoteUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::find($request->user_id);
            
            if ($user->isRegularUser()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already a regular user'
                ], 400);
            }

            $user->demoteToUser();

            return response()->json([
                'success' => true,
                'message' => 'User demoted to regular user successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_type' => $user->user_type,
                    'promoted_at' => $user->promoted_at
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to demote user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            if ($user->not_password !== $request->password) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'type' => $user->user_type,
                'data' => [
                    'token' => $user
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
