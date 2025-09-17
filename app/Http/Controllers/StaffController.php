<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class StaffController extends Controller
{
    /**
     * Display a listing of the staff.
     */
    public function index()
    {
        try {
            $user = auth()->user();
            $storeid = $user->storeid ?? 1;

            Log::info('Staff index accessed', [
                'user_id' => $user->id ?? 'guest',
                'store_id' => $storeid,
                'timestamp' => now()
            ]);

            // If user has no storeid (like MIS/SUPERADMIN), show all staff
            if ($user->storeid === null) {
                $staff = DB::table('staff as a')
                    ->leftJoin('users as b', DB::raw('CAST(a.userid AS UNSIGNED)'), '=', 'b.id')
                    ->select('a.id', 'a.name', 'a.passcode', 'a.image', 'b.storeid', 'a.ROLE as role', 'a.userid')
                    ->get();

                Log::info('SUPERADMIN accessing all staff', [
                    'user_role' => 'SUPERADMIN',
                    'showing_all_stores' => true
                ]);
            } else {
                $staff = DB::table('staff as a')
                    ->leftJoin('users as b', DB::raw('CAST(a.userid AS UNSIGNED)'), '=', 'b.id')
                    ->select('a.id', 'a.name', 'a.passcode', 'a.image', 'b.storeid', 'a.ROLE as role', 'a.userid')
                    ->where('b.storeid', '=', $storeid)
                    ->get();

                Log::info('Store user accessing filtered staff', [
                    'user_storeid' => $storeid,
                    'filtering_by_store' => true
                ]);
            }

            Log::info('Staff data retrieved', [
                'store_id' => $storeid,
                'staff_count' => $staff->count(),
                'staff_ids' => $staff->pluck('id')->toArray()
            ]);

            return Inertia::render('Staff/Index', ['staff' => $staff]);
        } catch (\Exception $e) {
            Log::error('Staff index error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id() ?? 'guest'
            ]);

            return Inertia::render('Staff/Index', ['staff' => []]);
        }
    }

    /**
     * Show the form for creating a new staff member.
     */
    public function create()
    {
        return redirect()->route('staff.index');
    }

    /**
     * Store a newly created staff member.
     */
    public function store(Request $request)
    {
        Log::info('Staff store request initiated', [
            'user_id' => auth()->id(),
            'request_data' => $request->only(['name', 'role', 'userid']),
            'timestamp' => now()
        ]);

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'passcode' => 'required|string|min:4',
                'role' => 'required|in:ST,SV',
                'userid' => 'required|exists:users,id'
            ]);

            Log::info('Staff store validation passed', [
                'name' => $request->name,
                'role' => $request->role,
                'userid' => $request->userid
            ]);

            $staffData = [
                'name' => $request->name,
                'passcode' => $request->passcode,
                'role' => $request->role,
                'userid' => $request->userid,
                'image' => $request->image ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $staffId = DB::table('staff')->insertGetId($staffData);

            Log::info('Staff member created successfully', [
                'staff_id' => $staffId,
                'name' => $request->name,
                'role' => $request->role,
                'created_by' => auth()->id()
            ]);

            return redirect()->back()->with('success', 'Staff member created successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Staff store validation failed', [
                'errors' => $e->errors(),
                'user_id' => auth()->id(),
                'request_data' => $request->only(['name', 'role', 'userid'])
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Staff store error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id(),
                'request_data' => $request->only(['name', 'role', 'userid'])
            ]);
            return redirect()->back()->with('error', 'Error creating staff member: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified staff member.
     */
    public function show($id)
    {
        return redirect()->route('staff.index');
    }

    /**
     * Show the form for editing the specified staff member.
     */
    public function edit($id)
    {
        return redirect()->route('staff.index');
    }

    /**
     * Update the specified staff member.
     */
    public function update(Request $request, $id)
    {
        Log::info('Staff update request initiated', [
            'staff_id' => $id,
            'user_id' => auth()->id(),
            'request_data' => $request->only(['name', 'role', 'userid']),
            'timestamp' => now()
        ]);

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'passcode' => 'required|string|min:4',
                'role' => 'required|in:ST,SV',
                'userid' => 'required|exists:users,id'
            ]);

            Log::info('Staff update validation passed', [
                'staff_id' => $id,
                'name' => $request->name,
                'role' => $request->role,
                'userid' => $request->userid
            ]);

            $updateData = [
                'name' => $request->name,
                'passcode' => $request->passcode,
                'role' => $request->role,
                'userid' => $request->userid,
                'image' => $request->image ?? null,
                'updated_at' => now(),
            ];

            $affectedRows = DB::table('staff')
                ->where('id', $id)
                ->update($updateData);

            Log::info('Staff member updated', [
                'staff_id' => $id,
                'affected_rows' => $affectedRows,
                'name' => $request->name,
                'role' => $request->role,
                'updated_by' => auth()->id()
            ]);

            return redirect()->back()->with('success', 'Staff member updated successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Staff update validation failed', [
                'staff_id' => $id,
                'errors' => $e->errors(),
                'user_id' => auth()->id(),
                'request_data' => $request->only(['name', 'role', 'userid'])
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Staff update error', [
                'staff_id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id(),
                'request_data' => $request->only(['name', 'role', 'userid'])
            ]);
            return redirect()->back()->with('error', 'Error updating staff member: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified staff member.
     */
    public function destroy($id)
    {
        Log::info('Staff delete request initiated', [
            'staff_id' => $id,
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);

        try {
            // Get staff info before deletion for logging
            $staff = DB::table('staff')->where('id', $id)->first();

            if (!$staff) {
                Log::warning('Staff member not found for deletion', [
                    'staff_id' => $id,
                    'user_id' => auth()->id()
                ]);
                return redirect()->back()->with('error', 'Staff member not found');
            }

            Log::info('Staff member found for deletion', [
                'staff_id' => $id,
                'staff_name' => $staff->name,
                'staff_role' => $staff->role
            ]);

            $deletedRows = DB::table('staff')->where('id', $id)->delete();

            Log::info('Staff member deleted successfully', [
                'staff_id' => $id,
                'staff_name' => $staff->name,
                'deleted_rows' => $deletedRows,
                'deleted_by' => auth()->id()
            ]);

            return redirect()->back()->with('success', 'Staff member deleted successfully');

        } catch (\Exception $e) {
            Log::error('Staff delete error', [
                'staff_id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->with('error', 'Error deleting staff member: ' . $e->getMessage());
        }
    }

    /**
     * Get users for the current store (for dropdown in forms)
     */
    public function getUsers()
    {
        Log::info('Staff getUsers request initiated', [
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);

        try {
            $storeid = auth()->user()->storeid ?? 1;

            Log::info('Fetching users for store', [
                'store_id' => $storeid,
                'requested_by' => auth()->id()
            ]);

            $users = DB::table('users')
                ->select('id', 'name', 'email')
                ->where('storeid', $storeid)
                ->get();

            Log::info('Users retrieved successfully', [
                'store_id' => $storeid,
                'user_count' => $users->count(),
                'user_ids' => $users->pluck('id')->toArray()
            ]);

            return response()->json($users);

        } catch (\Exception $e) {
            Log::error('Staff getUsers error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id()
            ]);
            return response()->json([], 500);
        }
    }
}