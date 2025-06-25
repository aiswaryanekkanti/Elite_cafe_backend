<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class ApiStaffController extends Controller
{
    public function index()
    {
        return response()->json(Staff::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:staff,email',
            'phone'      => 'nullable|string|max:20',
            'address'    => 'nullable|string|max:255',
            'role'       => 'required|string|max:50',
        ]);

        $staff = Staff::create($validated);

        return response()->json($staff, 201);
    }

    public function show($id)
    {
        $staff = Staff::findOrFail($id);
        return response()->json($staff);
    }

    public function update(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'sometimes|required|string|max:100',
            'last_name'  => 'sometimes|required|string|max:100',
            'email' => ['sometimes', 'required', 'email', Rule::unique('staff', 'email')->ignore($id, 'staff_id')],
            'phone'      => 'nullable|string|max:20',
            'address'    => 'nullable|string|max:255',
            'role'       => 'sometimes|required|string|max:50',
        ]);

        $staff->update($validated);

        return response()->json([
            'message' => 'Staff updated successfully.',
            'staff'   => $staff
        ]);
    }

    public function destroy($id)
    {
        $staff = Staff::findOrFail($id);
        $staff->delete();

        return response()->json(['message' => 'Staff marked as removed.']);
    }

    public function removed()
    {
        $removedStaff = Staff::onlyTrashed()->get();
        return response()->json($removedStaff);
    }

    public function restore($id)
    {
        $staff = Staff::onlyTrashed()->findOrFail($id);
        $staff->restore();

        return response()->json(['message' => 'Staff restored successfully.']);
    }
}
