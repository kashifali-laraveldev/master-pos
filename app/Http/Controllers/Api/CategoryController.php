<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;

class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/categories",
     *   tags={"Categories"},
     *   summary="List categories",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index()
    {
        $cats = Category::withCount('products')
            ->orderBy('display_order')
            ->get()
            ->map(function ($c) {
                $arr = $c->toArray();
                $arr['image_url'] = $c->image_url;
                return $arr;
            });

        return response()->json($cats);
    }

    public function show(Category $category)
    {
        return response()->json($category);
    }

    /**
     * @OA\Post(
     *   path="/api/categories",
     *   tags={"Categories"},
     *   summary="Create category",
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(required=true, @OA\JsonContent(example={"name":"Accessories","description":"Accessories","color":"#6366f1"})),
     *   @OA\Response(response=201, description="Created"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'image' => 'nullable|image|max:2048',
            'display_order' => 'nullable|integer',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        $validated['slug'] = Str::slug($validated['name']);

        return response()->json(Category::create($validated), 201);
    }

    /**
     * @OA\Put(
     *   path="/api/categories/{id}",
     *   tags={"Categories"},
     *   summary="Update category",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(required=true, @OA\JsonContent(example={"name":"Updated Category"})),
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=422, description="Validation error"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'nullable|boolean',
            'display_order' => 'nullable|integer',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($validated);

        return response()->json($category);
    }

    /**
     * @OA\Delete(
     *   path="/api/categories/{id}",
     *   tags={"Categories"},
     *   summary="Delete category",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=404, description="Not found"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function destroy(Category $category)
    {
        if ($category->products()->count()) {
            return response()->json(['message' => 'Category has products'], 422);
        }

        $category->delete();

        return response()->json(['message' => 'Deleted']);
    }
}

