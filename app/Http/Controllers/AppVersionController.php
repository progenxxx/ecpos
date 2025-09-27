<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppVersion;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AppVersionController extends Controller
{
    public function getCurrentVersion()
    {
        try {
            $version = AppVersion::where('is_active', true)->first();

            if (!$version) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active version found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'version_number' => $version->version_number,
                    'version_name' => $version->version_name,
                    'force_update' => $version->force_update,
                    'min_supported_version' => $version->min_supported_version,
                    'release_notes' => $version->release_notes,
                    'download_url' => $version->download_url,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting current version: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving version information'
            ], 500);
        }
    }

    public function checkVersion(Request $request)
    {
        try {
            $request->validate([
                'current_version' => 'required|string'
            ]);

            $currentAppVersion = $request->current_version;
            $latestVersion = AppVersion::where('is_active', true)->first();

            if (!$latestVersion) {
                return response()->json([
                    'success' => false,
                    'message' => 'No version information available'
                ], 404);
            }

            $needsUpdate = version_compare($currentAppVersion, $latestVersion->version_number, '<');
            $forceUpdate = $needsUpdate && version_compare($currentAppVersion, $latestVersion->min_supported_version, '<');

            return response()->json([
                'success' => true,
                'data' => [
                    'needs_update' => $needsUpdate,
                    'force_update' => $forceUpdate,
                    'latest_version' => $latestVersion->version_number,
                    'version_name' => $latestVersion->version_name,
                    'release_notes' => $latestVersion->release_notes,
                    'download_url' => $latestVersion->download_url,
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error checking version: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error checking version'
            ], 500);
        }
    }

    public function index()
    {
        $versions = AppVersion::orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $versions
        ]);
    }

    public function indexWeb()
    {
        $versions = AppVersion::orderBy('created_at', 'desc')->get();
        return inertia('AppVersion/Index', [
            'versions' => $versions
        ]);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'version_number' => 'required|string|unique:app_versions,version_number',
                'version_name' => 'required|string',
                'release_notes' => 'nullable|string',
                'download_url' => 'nullable|url',
                'force_update' => 'boolean',
                'min_supported_version' => 'required|string'
            ]);

            // Deactivate current active version
            AppVersion::where('is_active', true)->update(['is_active' => false]);

            $version = AppVersion::create([
                'version_number' => $request->version_number,
                'version_name' => $request->version_name,
                'release_notes' => $request->release_notes,
                'download_url' => $request->download_url,
                'force_update' => $request->force_update ?? false,
                'min_supported_version' => $request->min_supported_version,
                'is_active' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Version created successfully',
                'data' => $version
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating version: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating version'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $version = AppVersion::findOrFail($id);

            $request->validate([
                'version_number' => 'required|string|unique:app_versions,version_number,' . $id,
                'version_name' => 'required|string',
                'release_notes' => 'nullable|string',
                'download_url' => 'nullable|url',
                'force_update' => 'boolean',
                'min_supported_version' => 'required|string',
                'is_active' => 'boolean'
            ]);

            // If setting this version as active, deactivate others
            if ($request->is_active) {
                AppVersion::where('is_active', true)->where('id', '!=', $id)->update(['is_active' => false]);
            }

            $version->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Version updated successfully',
                'data' => $version
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating version: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating version'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $version = AppVersion::findOrFail($id);

            if ($version->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete active version'
                ], 400);
            }

            $version->delete();

            return response()->json([
                'success' => true,
                'message' => 'Version deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting version: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting version'
            ], 500);
        }
    }

    public function setActive($id)
    {
        try {
            AppVersion::where('is_active', true)->update(['is_active' => false]);

            $version = AppVersion::findOrFail($id);
            $version->update(['is_active' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Version set as active successfully',
                'data' => $version
            ]);
        } catch (\Exception $e) {
            Log::error('Error setting active version: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error setting active version'
            ], 500);
        }
    }
}