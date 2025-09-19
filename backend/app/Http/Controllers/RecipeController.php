<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Validator;

class RecipeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) : JsonResponse
    {
        $recipes = $request->user()->recipes()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($recipes);
    }


     /**
     * Get all public recipes (from all users)
     */
    public function public() : JsonResponse
    {
        $recipes = Recipe::with('user')
            ->where('is_public', true)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($recipes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) : JsonResponse
    {

        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'instructions' => 'required|string',
                'prep_time' => 'required|integer|min:1',
                'cook_time' => 'required|integer|min:1',
                'servings' => 'required|integer|min:1',
                'difficulty' => 'required|string|in:easy,medium,hard',
                'cuisine_type' => 'required|string',
                'image_path' => 'nullable|string',
                'is_public' => 'boolean'
            ]);

            $recipe = Recipe::query()->create([
                'user_id' => $request->user()->id,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'instructions' => $validated['instructions'],
                'prep_time' => $validated['prep_time'],
                'cook_time' => $validated['cook_time'],
                'servings' => $validated['servings'],
                'difficulty' => $validated['difficulty'],
                'cuisine_type' => $validated['cuisine_type'],
                'image_path' => $validated['image_path'],
                'is_public' => $validated['is_public'],
            ]);

            return response()->json($recipe->load('user'), 201);
        } catch(\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch(\Exception $e) {
            return response()->json([ 'error_message' => $e->getMessage(), 'code' => $e->getCode()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id) : JsonResponse
    {
        $recipe = Recipe::query()->where('id', $id)
            ->firstOrFail();
        return response()->json($recipe);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Recipe $recipe): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'instructions' => 'nullable|string',
                'prep_time' => 'nullable|integer|min:1',
                'cook_time' => 'nullable|integer|min:1',
                'servings' => 'nullable|integer|min:1',
                'difficulty' => 'nullable|string|in:easy,medium,hard',
                'cuisine_type' => 'nullable|string',
                'image_path' => 'nullable|string',
            ]);
            $recipe->update($validated);
            return response()->json($recipe->load('user'), 200);
        } catch(\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch(\Exception $e) {
            return response()->json([ 'error_message' => $e->getMessage(), 'code' => $e->getCode()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Recipe $recipe) : JsonResponse
    {
        $recipe->delete();
        return response()->json([
            'deleted' => true
        ]);
    }
}
