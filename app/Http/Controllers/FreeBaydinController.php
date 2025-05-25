<?php

namespace App\Http\Controllers;

use App\Models\FreeBaydin;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FreeBaydinController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $freeBaydins = FreeBaydin::latest()->get();
        return response()->json([
            'status' => 'success',
            'data' => $freeBaydins
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('free-baydin.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'date' => 'required|date',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'status' => 'required|string|in:pending,in progress,completed'
            ]);

            $freeBaydin = FreeBaydin::create([
                'date' => Carbon::parse($validated['date']),
                'title' => $validated['title'],
                'description' => $validated['description'],
                'status' => $validated['status']
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Record created successfully',
                'data' => $freeBaydin
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create record',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $freeBaydin = FreeBaydin::findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $freeBaydin
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $freeBaydin = FreeBaydin::findOrFail($id);
            
            $validated = $request->validate([
                'date' => 'sometimes|required|date',
                'title' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'status' => 'sometimes|required|string|in:pending,in progress,completed'
            ]);

            $freeBaydin->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Record updated successfully',
                'data' => $freeBaydin
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update record',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $freeBaydin = FreeBaydin::findOrFail($id);
            $freeBaydin->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Record deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete record',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Search FreeBaydin records by date and other criteria
     */
    public function search(Request $request)
    {
        try {
            $validated = $request->validate([
                'date' => 'nullable|date',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
                'title' => 'nullable|string',
                'status' => 'nullable|string|in:pending,in progress,completed'
            ]);

            $query = FreeBaydin::query();

            // Search by exact date if provided
            if (!empty($validated['date'])) {
                $query->whereDate('date', Carbon::parse($validated['date']));
            }

            // Search by date range if start_date and end_date are provided
            if (!empty($validated['start_date']) && !empty($validated['end_date'])) {
                $query->whereBetween('date', [
                    Carbon::parse($validated['start_date'])->startOfDay(),
                    Carbon::parse($validated['end_date'])->endOfDay()
                ]);
            }

            // Optional title search
            if (!empty($validated['title'])) {
                $query->where('title', 'like', '%' . $validated['title'] . '%');
            }

            // Optional status filter
            if (!empty($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            $results = $query->latest()->get();

            return response()->json([
                'status' => 'success',
                'data' => $results,
                'count' => $results->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to search records',
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
