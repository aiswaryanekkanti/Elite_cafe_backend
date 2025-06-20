<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MenuController extends Controller
{
    /**
     * Display a listing of the menu items.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $menuItems = MenuItem::all(); // Fetch all menu items

        // Return the menu items as a JSON response
        return response()->json($menuItems);
    }

    /**
     * Store a newly created menu item in storage.
     * This method would typically be protected by authentication middleware (e.g., Sanctum).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'img' => 'nullable|string|max:255', // Assuming img is a path string. For file uploads, it's different.
                'category' => 'required|string|max:50',
                'subcategory' => 'nullable|string|max:50',
                'veg' => 'boolean',
                'description' => 'nullable|string',
            ]);

            // Default 'veg' to true if not provided (matches your table default)
            if (!isset($validatedData['veg'])) {
                $validatedData['veg'] = true;
            }

            $menuItem = MenuItem::create($validatedData);

            return response()->json([
                'message' => 'Menu item created successfully!',
                'item' => $menuItem
            ], 201); // 201 Created
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors()
            ], 422); // 422 Unprocessable Entity
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the menu item.',
                'error' => $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    // You could add update, show, destroy methods here as well for a full CRUD API
}