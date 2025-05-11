<?php

namespace App\Http\Controllers;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
   

    protected function formatImages($images)
    {
        $baseUrl = url('storage');
        $imageArray = is_string($images) ? json_decode($images, true) : $images;
        $formattedImages = [];
        
        if (is_array($imageArray)) {
            foreach ($imageArray as $index => $imgPath) {
                $formattedImages[] = [
                    (string)($index + 1) => "{$baseUrl}/{$imgPath}"
                ];
            }
        }
        
        return $formattedImages;
    }

    public function create(Request $request)
    {
        // Get authenticated user
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication required'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'category_name' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'choice_astrologer' => 'nullable|string|max:255',
            'multi_image' => 'nullable|array',
            'multi_image.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // Max 5MB per image
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

        try {
            $imagePaths = [];
            
            // Handle both single and multiple file uploads
            if ($request->hasFile('multi_image')) {
                $files = $request->file('multi_image');
                
                // If it's a single file
                if (!is_array($files)) {
                    $path = $files->store('images', 'public');
                    $imagePaths[] = $path;
                }
                // If it's multiple files
                else {
                    foreach ($files as $file) {
                        $path = $file->store('images', 'public');
                        $imagePaths[] = $path;
                    }
                }
            }

            // Format images for storage
            $formattedImages = json_encode($imagePaths);

            $question = Question::create([
                'user_id' => $user->id,
                'category_id' => $request->category_id,
                'category_name' => $request->category_name,
                'name' => $request->name,
                'choice_astrologer' => $request->choice_astrologer,
                'multi_image' => $formattedImages,
                'dob' => $request->dob,
                'textarea' => $request->textarea,
                'status' => $request->status ?? 'pending',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Question created successfully',
                'data' => [
                    'id' => $question->id,
                    'user_id' => $question->user_id,
                    'category_id' => $question->category_id,
                    'category_name' => $question->category_name,
                    'name' => $question->name,
                    'choice_astrologer' => $question->choice_astrologer,
                    'multi_image' => $this->formatImages($question->multi_image),
                    'dob' => $question->dob,
                    'textarea' => $question->textarea,
                    'status' => $question->status,
                    'created_at' => $question->created_at,
                    'updated_at' => $question->updated_at
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create question',
                'error' => $e->getMessage()
            ], 500);
        }
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
