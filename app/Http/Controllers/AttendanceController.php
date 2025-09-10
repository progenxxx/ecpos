<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
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
                    'timeInPhoto' => $this->getImageUrl($attendance->timeInPhoto),
                    'breakInPhoto' => $this->getImageUrl($attendance->breakInPhoto),
                    'breakOutPhoto' => $this->getImageUrl($attendance->breakOutPhoto),
                    'timeOutPhoto' => $this->getImageUrl($attendance->timeOutPhoto),
                ];
            });

        return Inertia::render('Attendance/Index', [
            'attendances' => $attendances
        ]);
    }

    private function getImageUrl($imagePath)
    {
        if (!$imagePath || $imagePath === 'Image not found' || trim($imagePath) === '') {
            return null;
        }

        if (str_starts_with($imagePath, 'http')) {
            return $imagePath;
        }

        $cleanPath = ltrim($imagePath, '/');
        $cleanPath = str_replace('storage/', '', $cleanPath);
        
        $possiblePaths = [
            $cleanPath, 
            'attendance_photos/' . $cleanPath, 
            'attendance_photos/' . basename($cleanPath), 
            basename($cleanPath), 
            'attendance-photos/' . $cleanPath, 
            'attendance-photos/' . basename($cleanPath), 
        ];
        
        foreach ($possiblePaths as $testPath) {
            if (Storage::disk('public')->exists($testPath)) {
                $url = Storage::disk('public')->url($testPath);
                
                Log::info('🔗 Image URL generated successfully', [
                    'original_path' => $imagePath,
                    'found_at_path' => $testPath,
                    'generated_url' => $url,
                    'file_exists' => true
                ]);
                
                return $url;
            }
        }

        Log::warning('Image file not found in any expected location', [
            'original_path' => $imagePath,
            'tried_paths' => $possiblePaths,
            'storage_root' => storage_path('app/public/'),
            'available_files_in_attendance_photos' => Storage::disk('public')->files('attendance_photos'),
            'available_files_in_attendance-photos' => Storage::disk('public')->files('attendance-photos'),
        ]);

        return null;
    }

    private function storeFromApi(Request $request)
    {
        Log::info('📥 [storeFromApi] Attendance store process started', [
            'endpoint' => $request->getPathInfo(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $validated = $request->validate([
            'staffId' => 'required|string|max:255',
            'storeId' => 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'required|string|max:255',
            'type' => 'required|string|in:TIME_IN,TIME_OUT,BREAK_IN,BREAK_OUT',
            'photo' => 'required|image|max:2048'
        ]);

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
                'full_path' => Storage::disk('public')->path($photoPath),
                'exists' => Storage::disk('public')->exists($photoPath),
                'url_generated' => Storage::disk('public')->url($photoPath)
            ]);
        }

        $existingRecord = AttendanceRecord::where('staffId', $validated['staffId'])
            ->where('date', $validated['date'])
            ->first();

        try {
            $attendanceRecord = null;

            if ($validated['type'] === 'TIME_IN') {
                if ($existingRecord) {
                    $existingRecord->update([
                        'timeIn' => $validated['time'],
                        'timeInPhoto' => $photoPath, 
                        'status' => 'ACTIVE'
                    ]);
                    $attendanceRecord = $existingRecord->fresh();
                } else {
                    $attendanceRecord = AttendanceRecord::create([
                        'staffId' => $validated['staffId'],
                        'storeId' => $validated['storeId'],
                        'date' => $validated['date'],
                        'timeIn' => $validated['time'],
                        'timeInPhoto' => $photoPath, 
                        'status' => 'ACTIVE'
                    ]);
                }
            } else {
                if (!$existingRecord) {
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

                $existingRecord->update($updateData);
                $attendanceRecord = $existingRecord->fresh();
            }

            $responseData = [
                'id' => $attendanceRecord->id,
                'staffId' => $attendanceRecord->staffId,
                'storeId' => $attendanceRecord->storeId,
                'date' => $attendanceRecord->date,
                'timeIn' => $attendanceRecord->timeIn,
                'breakIn' => $attendanceRecord->breakIn,
                'breakOut' => $attendanceRecord->breakOut,
                'timeOut' => $attendanceRecord->timeOut,
                'status' => $attendanceRecord->status,
                'type' => $validated['type'],
                'recorded_time' => $validated['time'],

                'timeInPhoto' => $this->getImageUrl($attendanceRecord->timeInPhoto),
                'breakInPhoto' => $this->getImageUrl($attendanceRecord->breakInPhoto),
                'breakOutPhoto' => $this->getImageUrl($attendanceRecord->breakOutPhoto),
                'timeOutPhoto' => $this->getImageUrl($attendanceRecord->timeOutPhoto),
            ];

            Log::info('Generated image URLs', [
                'timeInPhoto' => $this->getImageUrl($attendanceRecord->timeInPhoto),
                'breakInPhoto' => $this->getImageUrl($attendanceRecord->breakInPhoto),
                'breakOutPhoto' => $this->getImageUrl($attendanceRecord->breakOutPhoto),
                'timeOutPhoto' => $this->getImageUrl($attendanceRecord->timeOutPhoto),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Attendance recorded successfully.',
                'data' => $responseData
            ]);

        } catch (\Exception $e) {
            Log::error('Exception while saving attendance', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save attendance record.',
                'error' => $e->getMessage()
            ], 500);
        }
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
                $validated[$field] = $request->file($field)->store('attendance_photos', 'public');
            }
        }

        $attendanceRecord = AttendanceRecord::create($validated);

        Log::info('Attendance record created for staff: ' . $validated['staffId'] . ' on ' . $validated['date']);

        return redirect()->route('attendance.index')->with('success', 'Attendance record created successfully.');
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
                $validated[$field] = $request->file($field)->store('attendance_photos', 'public');
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
            'timeInPhoto' => $this->getImageUrl($attendance->timeInPhoto),
            'breakInPhoto' => $this->getImageUrl($attendance->breakInPhoto),
            'breakOutPhoto' => $this->getImageUrl($attendance->breakOutPhoto),
            'timeOutPhoto' => $this->getImageUrl($attendance->timeOutPhoto),
        ];

        return Inertia::render('Attendance/Show', [
            'attendance' => $attendanceData
        ]);
    }

    public function edit(AttendanceRecord $attendance)
    {
        $attendanceData = [
            ...$attendance->toArray(),
            'timeInPhoto' => $this->getImageUrl($attendance->timeInPhoto),
            'breakInPhoto' => $this->getImageUrl($attendance->breakInPhoto),
            'breakOutPhoto' => $this->getImageUrl($attendance->breakOutPhoto),
            'timeOutPhoto' => $this->getImageUrl($attendance->timeOutPhoto),
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
            'timeInPhoto' => $this->getImageUrl($attendance->timeInPhoto),
            'breakInPhoto' => $this->getImageUrl($attendance->breakInPhoto),
            'breakOutPhoto' => $this->getImageUrl($attendance->breakOutPhoto),
            'timeOutPhoto' => $this->getImageUrl($attendance->timeOutPhoto),
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
                    'timeInPhoto' => $this->getImageUrl($attendance->timeInPhoto),
                    'breakInPhoto' => $this->getImageUrl($attendance->breakInPhoto),
                    'breakOutPhoto' => $this->getImageUrl($attendance->breakOutPhoto),
                    'timeOutPhoto' => $this->getImageUrl($attendance->timeOutPhoto),
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Attendance records retrieved successfully.',
            'data' => $attendances
        ]);
    }

    public function debugStorage()
    {
        $info = [
            'storage_path' => storage_path('app/public'),
            'storage_disk_exists' => Storage::disk('public')->exists(''),
            'attendance_photos_exists' => Storage::disk('public')->exists('attendance_photos'),
            'attendance-photos_exists' => Storage::disk('public')->exists('attendance-photos'),
            'files_in_attendance_photos' => Storage::disk('public')->files('attendance_photos'),
            'files_in_attendance-photos' => Storage::disk('public')->files('attendance-photos'),
            'all_files' => Storage::disk('public')->allFiles(),
        ];
        
        Log::info('Storage Debug Info', $info);
        
        return response()->json($info);
    }
}