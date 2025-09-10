<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRecord;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage; 
use Inertia\Inertia; 

class AttendanceRecordController extends Controller
{
    public function index()
    {
        $attendances = AttendanceRecord::query()
            ->orderBy('date', 'desc')
            ->orderBy('timeIn', 'desc')
            ->get()
            ->map(function ($attendance) {
                return [
                    ...$attendance->toArray(),
                    'timeInPhoto' => $attendance->timeInPhoto ? Storage::url($attendance->timeInPhoto) : null,
                    'breakInPhoto' => $attendance->breakInPhoto ? Storage::url($attendance->breakInPhoto) : null,
                    'breakOutPhoto' => $attendance->breakOutPhoto ? Storage::url($attendance->breakOutPhoto) : null,
                    'timeOutPhoto' => $attendance->timeOutPhoto ? Storage::url($attendance->timeOutPhoto) : null,
                ];
            });

        return Inertia::render('Attendance/Index', [
            'attendances' => $attendances
        ]);
    }

    public function store(Request $request)
    {
        if ($request->has('type') && $request->has('time') && $request->has('photo')) {
            return $this->storeFromApi($request);
        }

        $validated = $request->validate([
            'staffId' => 'required|string|max:255',
            'storeId' => 'required|string|max:255',
            'date' => 'required|date',
            'timeIn' => 'required|string|max:255',
            'timeInPhoto' => 'required|image|max:2048',
            'breakIn' => 'nullable|string|max:255',
            'breakInPhoto' => 'nullable|image|max:2048',
            'breakOut' => 'nullable|string|max:255',
            'breakOutPhoto' => 'nullable|image|max:2048',
            'timeOut' => 'nullable|string|max:255',
            'timeOutPhoto' => 'nullable|image|max:2048',
            'status' => 'required|string|max:255'
        ]);

        $photoFields = ['timeInPhoto', 'breakInPhoto', 'breakOutPhoto', 'timeOutPhoto'];
        foreach ($photoFields as $field) {
            if ($request->hasFile($field)) {
                $validated[$field] = $request->file($field)->store('attendance-photos', 'public');
            }
        }

        $attendanceRecord = AttendanceRecord::create($validated);

        Log::info('Attendance record created for staff: ' . $validated['staffId'] . ' on ' . $validated['date']);

        return redirect()->route('attendance.index')->with('success', 'Attendance record created successfully.');
    }

    private function storeFromApi(Request $request)
    {
        Log::info('[storeFromApi] Attendance store process started', [
            'endpoint' => $request->getPathInfo(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        Log::info('Incoming request payload', [
            'all_fields' => $request->all(),
            'file_fields' => $request->allFiles(),
            'headers' => $request->headers->all(),
            'content_type' => $request->header('Content-Type'),
            'request_size' => $request->header('Content-Length')
        ]);

        Log::info('Connected DB info', [
            'connection' => \DB::connection()->getName(),
            'database' => \DB::connection()->getDatabaseName(),
        ]);

        \DB::listen(function ($query) {
            Log::debug('SQL Query Executed', [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time_ms' => $query->time
            ]);
        });

        Log::info('Validation starting...');

        $validated = $request->validate([
            'staffId' => 'required|string|max:255',
            'storeId' => 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'required|string|max:255',
            'type' => 'required|string|in:TIME_IN,TIME_OUT,BREAK_IN,BREAK_OUT',
            'photo' => 'required|image|max:2048'
        ]);

        Log::info('Validation passed', ['validated' => $validated]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoFile = $request->file('photo');
            Log::info('Photo received', [
                'original_name' => $photoFile->getClientOriginalName(),
                'mime_type' => $photoFile->getMimeType(),
                'size_kb' => round($photoFile->getSize() / 1024, 2)
            ]);

            $photoPath = $photoFile->store('attendance_photos', 'public');
            Log::info('Photo stored at', [
                'path' => $photoPath,
                'full_path' => Storage::disk('public')->path($photoPath)
            ]);
        }

        $existingRecord = AttendanceRecord::where('staffId', $validated['staffId'])
            ->where('date', $validated['date'])
            ->first();

        try {
            $attendanceRecord = null;

            if ($validated['type'] === 'TIME_IN') {
                Log::info('Pre-creation debugging', [
                    'database_connection' => \DB::connection()->getName(),
                    'database_name' => \DB::connection()->getDatabaseName(),
                    'table_exists' => \Schema::hasTable('attendance_records'),
                    'table_columns' => \Schema::getColumnListing('attendance_records'),
                    'current_record_count' => AttendanceRecord::count(),
                ]);

                \DB::enableQueryLog();

                if ($existingRecord) {
                    Log::info('Updating existing record', ['existing_id' => $existingRecord->id]);
                    
                    $existingRecord->update([
                        'timeIn' => $validated['time'],
                        'timeInPhoto' => $photoPath,
                        'status' => 'ACTIVE'
                    ]);
                    
                    $attendanceRecord = $existingRecord->fresh();
                    Log::info('Existing record updated', ['id' => $attendanceRecord->id]);
                    
                } else {
                    Log::info('Creating new record with data:', [
                        'staffId' => $validated['staffId'],
                        'storeId' => $validated['storeId'],
                        'date' => $validated['date'],
                        'timeIn' => $validated['time'],
                        'timeInPhoto' => $photoPath,
                        'status' => 'ACTIVE'
                    ]);

                    try {
                        \DB::beginTransaction();
                        
                        $attendanceRecord = AttendanceRecord::create([
                            'staffId' => $validated['staffId'],
                            'storeId' => $validated['storeId'],
                            'date' => $validated['date'],
                            'timeIn' => $validated['time'],
                            'timeInPhoto' => $photoPath,
                            'status' => 'ACTIVE'
                        ]);

                        Log::info('After create() attempt', [
                            'record_object' => $attendanceRecord ? 'exists' : 'null',
                            'record_id' => $attendanceRecord->id ?? 'no id',
                            'record_exists' => $attendanceRecord->exists ?? 'no exists property',
                            'record_attributes' => $attendanceRecord->getAttributes() ?? 'no attributes'
                        ]);

                        $verifyRecord = AttendanceRecord::where('staffId', $validated['staffId'])
                            ->where('date', $validated['date'])
                            ->first();
                        
                        Log::info('Verification query result', [
                            'found_record' => $verifyRecord ? 'yes' : 'no',
                            'found_id' => $verifyRecord->id ?? 'no id',
                            'total_records_now' => AttendanceRecord::count()
                        ]);

                        \DB::commit();
                        Log::info('Transaction committed');

                        if (!$attendanceRecord || !$attendanceRecord->exists) {
                            Log::warning('create() failed, trying alternative method');
                            
                            $attendanceRecord = new AttendanceRecord();
                            $attendanceRecord->staffId = $validated['staffId'];
                            $attendanceRecord->storeId = $validated['storeId'];
                            $attendanceRecord->date = $validated['date'];
                            $attendanceRecord->timeIn = $validated['time'];
                            $attendanceRecord->timeInPhoto = $photoPath;
                            $attendanceRecord->status = 'ACTIVE';
                            
                            $saved = $attendanceRecord->save();
                            
                            Log::info('Alternative save() result', [
                                'save_result' => $saved,
                                'record_id' => $attendanceRecord->id,
                                'record_exists' => $attendanceRecord->exists
                            ]);
                        }

                    } catch (\Exception $e) {
                        \DB::rollback();
                        Log::error('Transaction failed', [
                            'error' => $e->getMessage(),
                            'line' => $e->getLine(),
                            'file' => $e->getFile()
                        ]);
                        throw $e;
                    }
                }

                $queries = \DB::getQueryLog();
                Log::info('SQL Queries executed', $queries);

                $finalCount = AttendanceRecord::count();
                $todayCount = AttendanceRecord::whereDate('date', $validated['date'])->count();
                
                Log::info('Final database state', [
                    'total_records' => $finalCount,
                    'today_records' => $todayCount,
                    'staff_today_records' => AttendanceRecord::where('staffId', $validated['staffId'])
                        ->whereDate('date', $validated['date'])->count()
                ]);

            } else {
                if (!$existingRecord) {
                    Log::error('No TIME_IN found for update', [
                        'staff' => $validated['staffId'],
                        'date' => $validated['date']
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'No attendance record found for this date. Please TIME_IN first.',
                        'error' => 'MISSING_TIME_IN'
                    ], 400);
                }

                $updateData = [];
                switch ($validated['type']) {
                    case 'BREAK_IN':
                        $updateData['breakIn'] = $validated['time'];
                        $updateData['breakInPhoto'] = $photoPath;
                        break;
                    case 'BREAK_OUT':
                        $updateData['breakOut'] = $validated['time'];
                        $updateData['breakOutPhoto'] = $photoPath;
                        break;
                    case 'TIME_OUT':
                        $updateData['timeOut'] = $validated['time'];
                        $updateData['timeOutPhoto'] = $photoPath;
                        break;
                }

                Log::info('Updating attendance record', [
                    'type' => $validated['type'],
                    'update_data' => $updateData,
                    'existing_record_id' => $existingRecord->id
                ]);

                $existingRecord->update($updateData);
                $attendanceRecord = $existingRecord->fresh();
                
                Log::info('Attendance updated successfully', [
                    'type' => $validated['type'],
                    'updated_fields' => array_keys($updateData),
                    'record_id' => $attendanceRecord->id
                ]);
            }

            $responseData = [
                'id' => $attendanceRecord->id ?? null,
                'staffId' => $attendanceRecord->staffId ?? null,
                'storeId' => $attendanceRecord->storeId ?? null,
                'date' => $attendanceRecord->date ?? null,
                'timeIn' => $attendanceRecord->timeIn ?? null,
                'breakIn' => $attendanceRecord->breakIn ?? null,
                'breakOut' => $attendanceRecord->breakOut ?? null,
                'timeOut' => $attendanceRecord->timeOut ?? null,
                'status' => $attendanceRecord->status ?? null,
                'type' => $validated['type'],
                'recorded_time' => $validated['time']
            ];

            Log::info('Attendance process completed successfully', [
                'response_data' => $responseData
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Attendance recorded successfully.',
                'data' => $responseData
            ]);

        } catch (\Exception $e) {
            Log::error('Exception while saving attendance', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save attendance record.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, AttendanceRecord $attendance)
    {
        $validated = $request->validate([
            'staffId' => 'required|string|max:255',
            'storeId' => 'required|string|max:255',
            'date' => 'required|date',
            'timeIn' => 'required|string|max:255',
            'timeInPhoto' => 'sometimes|image|max:2048',
            'breakIn' => 'nullable|string|max:255',
            'breakInPhoto' => 'nullable|image|max:2048',
            'breakOut' => 'nullable|string|max:255',
            'breakOutPhoto' => 'nullable|image|max:2048',
            'timeOut' => 'nullable|string|max:255',
            'timeOutPhoto' => 'nullable|image|max:2048',
            'status' => 'required|string|max:255'
        ]);

        $photoFields = ['timeInPhoto', 'breakInPhoto', 'breakOutPhoto', 'timeOutPhoto'];
        foreach ($photoFields as $field) {
            if ($request->hasFile($field)) {
                if ($attendance->$field) {
                    Storage::disk('public')->delete($attendance->$field);
                }
                $validated[$field] = $request->file($field)->store('attendance-photos', 'public');
            } else {
                unset($validated[$field]);
            }
        }

        $attendance->update($validated);

        Log::info('Attendance record updated', [
            'id' => $attendance->id,
            'staff' => $attendance->staffId,
            'date' => $attendance->date
        ]);

        return redirect()->back()->with('success', 'Attendance record updated successfully.');
    }

    public function destroy(AttendanceRecord $attendance)
    {
        Log::info('Deleting attendance record', [
            'id' => $attendance->id,
            'staff' => $attendance->staffId,
            'date' => $attendance->date
        ]);

        $photoFields = ['timeInPhoto', 'breakInPhoto', 'breakOutPhoto', 'timeOutPhoto'];
        foreach ($photoFields as $field) {
            if ($attendance->$field) {
                Storage::disk('public')->delete($attendance->$field);
                Log::info('Deleted photo', ['field' => $field, 'path' => $attendance->$field]);
            }
        }

        $attendance->delete();

        Log::info('Attendance record deleted successfully', ['id' => $attendance->id]);

        return redirect()->back()->with('success', 'Attendance record deleted successfully.');
    }

    public function show(AttendanceRecord $attendance)
    {
        $attendanceData = [
            ...$attendance->toArray(),
            'timeInPhoto' => $attendance->timeInPhoto ? Storage::url($attendance->timeInPhoto) : null,
            'breakInPhoto' => $attendance->breakInPhoto ? Storage::url($attendance->breakInPhoto) : null,
            'breakOutPhoto' => $attendance->breakOutPhoto ? Storage::url($attendance->breakOutPhoto) : null,
            'timeOutPhoto' => $attendance->timeOutPhoto ? Storage::url($attendance->timeOutPhoto) : null,
        ];

        return Inertia::render('Attendance/Show', [
            'attendance' => $attendanceData
        ]);
    }

    public function edit(AttendanceRecord $attendance)
    {
        $attendanceData = [
            ...$attendance->toArray(),
            'timeInPhoto' => $attendance->timeInPhoto ? Storage::url($attendance->timeInPhoto) : null,
            'breakInPhoto' => $attendance->breakInPhoto ? Storage::url($attendance->breakInPhoto) : null,
            'breakOutPhoto' => $attendance->breakOutPhoto ? Storage::url($attendance->breakOutPhoto) : null,
            'timeOutPhoto' => $attendance->timeOutPhoto ? Storage::url($attendance->timeOutPhoto) : null,
        ];

        return Inertia::render('Attendance/Edit', [
            'attendance' => $attendanceData
        ]);
    }

    public function getAttendanceByStaff(Request $request)
    {
        $staffId = $request->input('staffId');
        $date = $request->input('date', date('Y-m-d'));

        $attendance = AttendanceRecord::where('staffId', $staffId)
            ->where('date', $date)
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'No attendance record found for this staff and date.',
                'data' => null
            ], 404);
        }

        $attendanceData = [
            ...$attendance->toArray(),
            'timeInPhoto' => $attendance->timeInPhoto ? Storage::url($attendance->timeInPhoto) : null,
            'breakInPhoto' => $attendance->breakInPhoto ? Storage::url($attendance->breakInPhoto) : null,
            'breakOutPhoto' => $attendance->breakOutPhoto ? Storage::url($attendance->breakOutPhoto) : null,
            'timeOutPhoto' => $attendance->timeOutPhoto ? Storage::url($attendance->timeOutPhoto) : null,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Attendance record retrieved successfully.',
            'data' => $attendanceData
        ]);
    }

    public function getAttendanceByDateRange(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'staffId' => 'nullable|string'
        ]);

        $query = AttendanceRecord::whereBetween('date', [$validated['start_date'], $validated['end_date']]);

        if ($validated['staffId']) {
            $query->where('staffId', $validated['staffId']);
        }

        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('timeIn', 'desc')
            ->get()
            ->map(function ($attendance) {
                return [
                    ...$attendance->toArray(),
                    'timeInPhoto' => $attendance->timeInPhoto ? Storage::url($attendance->timeInPhoto) : null,
                    'breakInPhoto' => $attendance->breakInPhoto ? Storage::url($attendance->breakInPhoto) : null,
                    'breakOutPhoto' => $attendance->breakOutPhoto ? Storage::url($attendance->breakOutPhoto) : null,
                    'timeOutPhoto' => $attendance->timeOutPhoto ? Storage::url($attendance->timeOutPhoto) : null,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Attendance records retrieved successfully.',
            'data' => $attendances
        ]);
    }
}