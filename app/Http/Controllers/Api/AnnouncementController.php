<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class AnnouncementController extends Controller
{
    /**
     * Get the latest announcement
     */
    public function latest(): JsonResponse
    {
        try {
            $announcement = Announcement::where("show_on_homepage", true)->orderBy("created_at", "desc")->first();

            if (!$announcement) {
                return response()->json([
                    'success' => false,
                    'message' => 'No announcements found',
                    'data' => null
                ], 404);
            }

            // Increment view count
            $announcement->incrementViewCount();

            return response()->json([
                'success' => true,
                'message' => 'Latest announcement retrieved successfully',
                'data' => $announcement
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve latest announcement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new announcement
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'type' => 'required|in:info,warning,success,danger',
                'priority' => 'required|in:low,medium,high,critical',
                'is_active' => 'boolean',
                'is_sticky' => 'boolean',
                'show_on_homepage' => 'boolean',
                'show_in_dashboard' => 'boolean',
                'send_notification' => 'boolean',
                'target_audience' => 'required|in:all,users,superadmins',
                'published_at' => 'nullable|date',
                'expires_at' => 'nullable|date|after:published_at',
                'image_url' => 'nullable|url',
                'action_url' => 'nullable|url',
                'action_text' => 'nullable|string|max:100',
                'metadata' => 'nullable|array'
            ]);

            // Set created_by to authenticated user (you might want to add auth middleware)
            $validated['created_by'] = 1; // Hardcoded for now, replace with auth()->id()
            
            // Set published_at to now if not provided and is_active is true
            if (!isset($validated['published_at']) && ($validated['is_active'] ?? true)) {
                $validated['published_at'] = now();
            }

            $announcement = Announcement::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Announcement created successfully',
                'data' => $announcement->load('creator:id,name,email')
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create announcement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing announcement
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $announcement = Announcement::findOrFail($id);

            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'content' => 'sometimes|required|string',
                'type' => 'sometimes|required|in:info,warning,success,danger',
                'priority' => 'sometimes|required|in:low,medium,high,critical',
                'is_active' => 'boolean',
                'is_sticky' => 'boolean',
                'show_on_homepage' => 'boolean',
                'show_in_dashboard' => 'boolean',
                'send_notification' => 'boolean',
                'target_audience' => 'sometimes|required|in:all,users,superadmins',
                'published_at' => 'nullable|date',
                'expires_at' => 'nullable|date|after:published_at',
                'image_url' => 'nullable|url',
                'action_url' => 'nullable|url',
                'action_text' => 'nullable|string|max:100',
                'metadata' => 'nullable|array'
            ]);

            $announcement->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Announcement updated successfully',
                'data' => $announcement->fresh()->load('creator:id,name,email')
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Announcement not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update announcement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an announcement
     */
    public function delete($id): JsonResponse
    {
        try {
            $announcement = Announcement::findOrFail($id);
            
            $announcement->delete();

            return response()->json([
                'success' => true,
                'message' => 'Announcement deleted successfully'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Announcement not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete announcement',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
