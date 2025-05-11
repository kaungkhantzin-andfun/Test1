<?php

namespace App\Http\Controllers;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'category_name' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'choice_astrologer' => 'nullable|string|max:255',
            'multi_image' => 'nullable|array',
            'multi_image.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB per image
            'dob' => 'nullable|date',
            'textarea' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        
        $imagePaths = [];
        if ($request->hasFile('multi_image')) {
            foreach ($request->file('multi_image') as $image) {
                $path = $image->store('images', 'public'); 
                $imagePaths[] = $path;
            }
        }

        // Create the Question
        $question = Question::create([
            'category_id' => $request->category_id,
            'category_name' => $request->category_name,
            'name' => $request->name,
            'choice_astrologer' => $request->choice_astrologer,
            'multi_image' => json_encode($imagePaths), 
            'dob' => $request->dob,
            'textarea' => $request->textarea,
            'status' => $request->status,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Question created successfully',
            'data' => $question,
        ], 201);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
