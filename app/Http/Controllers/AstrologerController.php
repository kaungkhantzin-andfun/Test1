<?php

namespace App\Http\Controllers;

use App\Models\Astrologer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AstrologerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $astrologers = Astrologer::with([
            'user',
            'tasks'
        ])->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $astrologers
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:astrologers',
            'phone' => 'required|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $astrologer = Astrologer::create($request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'Astrologer created successfully',
                'data' => $astrologer
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create astrologer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $astrologer = Astrologer::with([
            'user',
            'tasks'
        ])->find($id);

        if (!$astrologer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Astrologer not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $astrologer
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $astrologer = Astrologer::find($id);

        if (!$astrologer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Astrologer not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'email' => 'email|unique:astrologers,email,'.$id,
            'phone' => 'string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $astrologer->update($request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'Astrologer updated successfully',
                'data' => $astrologer
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update astrologer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $astrologer = Astrologer::find($id);

        if (!$astrologer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Astrologer not found'
            ], 404);
        }

        try {
            $astrologer->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Astrologer deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete astrologer',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}