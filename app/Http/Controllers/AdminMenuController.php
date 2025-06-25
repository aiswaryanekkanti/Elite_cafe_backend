<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MenuInfo;
use Illuminate\Database\QueryException;
class AdminMenuController extends Controller
{
   public function index()
{
    $menuItems = MenuInfo::where('is_deleted',0)->get();
    return response()->json([
        'data' => $menuItems
    ]);
}


//     public function store(Request $request)
// {
//     $validated = $request->validate([
//         'name' => 'required|string|max:255',
//         'price' => 'required|numeric',
//         'img' => 'nullable|string',
//         'category' => 'nullable|string',
//         'subcategory' => 'nullable|string',
//         'veg' => 'required|boolean',
//         'description' => 'nullable|string',
//     ]);
//     $menu = MenuInfo::create($validated);
//     return response()->json($menu, 201);
// }



public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'price' => 'required|numeric',
        'img' => 'nullable|string',
        'category' => 'nullable|string',
        'subcategory' => 'nullable|string',
        'veg' => 'required|boolean',
        'description' => 'nullable|string',
    ]);


    try {
        $menu = MenuInfo::create($validated);
        return response()->json($menu, 201);
    } catch (QueryException $e) {
        if ($e->errorInfo[1] == 1062) { // Duplicate entry error code
            return response()->json(['message' => 'Item already exists'], 409);
        }
        throw $e; // other DB exceptions
    }
}


    public function update(Request $request, $id)
{
    $menu = MenuInfo::findOrFail($id);

    // Only validate fields that are present in the request
    $rules = [];
    if ($request->has('name')) $rules['name'] = 'required|string|max:255';
    if ($request->has('price')) $rules['price'] = 'required|numeric';
    if ($request->has('img')) $rules['img'] = 'nullable|string';
    if ($request->has('category')) $rules['category'] = 'nullable|string';
    if ($request->has('subcategory')) $rules['subcategory'] = 'nullable|string';
    if ($request->has('veg')) $rules['veg'] = 'required|boolean';
    if ($request->has('description')) $rules['description'] = 'nullable|string';

    $validated = $request->validate($rules);

    $menu->update($validated);
    return response()->json($menu);
}


    public function destroy($id)
{
    $menu = MenuInfo::findOrFail($id);

    // Soft hide the item
    $menu->is_deleted = 1;
    $menu->save();

    return response()->json(['message' => 'Item hidden successfully']);
}

    public function hiddenItems()
{
    $hiddenItems = MenuInfo::where('is_deleted', 1)->get();
    return response()->json(['data' => $hiddenItems]);
}
public function toggleVisibility($id)
{
    $menu = MenuInfo::findOrFail($id);
    $menu->is_deleted = !$menu->is_deleted;
    $menu->save();

    return response()->json([
        'message' => $menu->is_deleted ? 'Item hidden' : 'Item restored',
        'data' => $menu
    ]);
}

}
