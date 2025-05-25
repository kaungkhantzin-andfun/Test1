<?php

namespace App\Http\Controllers;

use App\Models\Toask;
use App\Models\User;
use App\Models\Astrologer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class BadyinToaskController extends Controller
{
    /**
     * Display a listing of the tasks
     */
    public function index()
    {
        // If user is an astrologer, show only their assigned tasks
        if (Auth::user()->role === 'astrologer') {
            $tasks = Toask::with(['user', 'astrologer', 'category'])
                ->where('astrologers_id', Auth::id())
                ->latest()
                ->get();
        } else {
            $tasks = Toask::with(['user', 'astrologer', 'category'])->latest()->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => $tasks
        ]);
    }

    /**
     * Get available astrologers
     */
    public function getAvailableAstrologers()
    {
        $astrologers = Astrologer::select('id', 'name', 'email')->get();

        return response()->json([
            'status' => 'success',
            'data' => $astrologers
        ]);
    }

    /**
     * Get tasks for specific astrologer
     */
    public function getAstrologerTasks($astrologerId)
    {
        try {
            $astrologer = Astrologer::findOrFail($astrologerId);
            
            $tasks = Toask::with(['user', 'category'])
                ->where('astrologers_id', $astrologerId)
                ->latest()
                ->get();

            return response()->json([
                'status' => 'success',
                'astrologer' => [
                    'id' => $astrologer->id,
                    'name' => $astrologer->name
                ],
                'data' => $tasks
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Astrologer not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Create a new task
     */
    public function create(Request $request)
    {
        try {
            $validated = $request->validate([
                'astrologers_id' => 'required|exists:astrologers,id',
                'user_id' => 'nullable|exists:users,id',
                'categories_id' => 'nullable|exists:categories,id',
                'description' => 'required|string',
                'photos' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'voice' => 'nullable|mimes:mp3,wav|max:5120',
                'video' => 'nullable|mimes:mp4,mov,avi|max:10240',
                'status' => 'nullable|in:pending,in progress,completed'
            ]);

            // Verify if the assigned astrologer exists
            $astrologer = Astrologer::findOrFail($validated['astrologers_id']);

            $data = $request->except(['photos', 'voice', 'video']);
            $data['user_id'] = $data['user_id'] ?? Auth::id(); // Set current user if not specified
            $data['status'] = $data['status'] ?? 'pending'; // Default status

            // Handle file uploads
            if ($request->hasFile('photos')) {
                $photoPath = $request->file('photos')->store('task_photos', 'public');
                $data['photos'] = $photoPath;
            }

            if ($request->hasFile('voice')) {
                $voicePath = $request->file('voice')->store('task_voices', 'public');
                $data['voice'] = $voicePath;
            }

            if ($request->hasFile('video')) {
                $videoPath = $request->file('video')->store('task_videos', 'public');
                $data['video'] = $videoPath;
            }

            $task = Toask::create($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Task created successfully and assigned to astrologer',
                'data' => $task->load(['user', 'astrologer', 'category'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create task',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified task
     */
    public function show($id)
    {
        try {
            $task = Toask::with(['user', 'astrologer', 'category'])->findOrFail($id);
            return response()->json([
                'status' => 'success',
                'data' => $task
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Task not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified task
     */
    public function update(Request $request, $id)
    {
        try {
            $task = Toask::findOrFail($id);

            $validated = $request->validate([
                'astrologers_id' => 'nullable|exists:users,id',
                'user_id' => 'nullable|exists:users,id',
                'categories_id' => 'nullable|exists:categories,id',
                'description' => 'nullable|string',
                'photos' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'voice' => 'nullable|mimes:mp3,wav|max:5120',
                'video' => 'nullable|mimes:mp4,mov,avi|max:10240',
                'status' => 'nullable|in:pending,in progress,completed'
            ]);

            $data = $request->except(['photos', 'voice', 'video']);

            // Handle file uploads
            if ($request->hasFile('photos')) {
                // Delete old file if exists
                if ($task->photos) {
                    Storage::disk('public')->delete($task->photos);
                }
                $photoPath = $request->file('photos')->store('task_photos', 'public');
                $data['photos'] = $photoPath;
            }

            if ($request->hasFile('voice')) {
                if ($task->voice) {
                    Storage::disk('public')->delete($task->voice);
                }
                $voicePath = $request->file('voice')->store('task_voices', 'public');
                $data['voice'] = $voicePath;
            }

            if ($request->hasFile('video')) {
                if ($task->video) {
                    Storage::disk('public')->delete($task->video);
                }
                $videoPath = $request->file('video')->store('task_videos', 'public');
                $data['video'] = $videoPath;
            }

            $task->update($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Task updated successfully',
                'data' => $task
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update task',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove the specified task
     */
    public function destroy($id)
    {
        try {
            $task = Toask::findOrFail($id);

            // Delete associated files
            if ($task->photos) {
                Storage::disk('public')->delete($task->photos);
            }
            if ($task->voice) {
                Storage::disk('public')->delete($task->voice);
            }
            if ($task->video) {
                Storage::disk('public')->delete($task->video);
            }

            $task->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Task deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete task',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update task status by astrologer
     */
    public function updateTaskStatus(Request $request, $id)
    {
        try {
            $task = Toask::findOrFail($id);

            // Verify if the current user is the assigned astrologer
            $astrologer = Astrologer::where('user_id', Auth::id())->first();
            if (!$astrologer || $astrologer->id !== $task->astrologers_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Only the assigned astrologer can update this task\'s status'
                ], 403);
            }

            $validated = $request->validate([
                'status' => 'required|in:pending,in progress,completed'
            ]);

            $task->update(['status' => $validated['status']]);

            return response()->json([
                'status' => 'success',
                'message' => 'Task status updated successfully',
                'data' => $task->load(['user', 'astrologer', 'category'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update task status',
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
