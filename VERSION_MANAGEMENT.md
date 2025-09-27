# App Version Management System

This system allows you to manage app versions for your mobile application and prompt users to update when new versions are available.

## Features

- **Version Management**: Create and manage different versions of your app
- **Force Updates**: Mark versions as requiring mandatory updates
- **Update Notifications**: API endpoints for the app to check for updates
- **Admin Interface**: Web interface to manage versions
- **Minimum Version Support**: Define oldest supported version

## Setup Instructions

### 1. Run Database Migration

First, make sure your database is connected and run the migration:

```bash
php artisan migrate
```

This will create the `app_versions` table with the following structure:
- `id`: Primary key
- `version_number`: Version string (e.g., "1.0.0")
- `version_name`: Human-readable name (e.g., "Major Update")
- `release_notes`: Description of what's new
- `download_url`: URL to download the app (optional)
- `force_update`: Boolean flag for mandatory updates
- `min_supported_version`: Minimum version that's still supported
- `is_active`: Only one version can be active at a time

### 2. Access Admin Interface

Navigate to `/app-versions` in your web browser (requires admin authentication).

The admin interface allows you to:
- Create new versions
- Edit existing versions
- Set which version is active
- Delete versions (except active ones)
- Test version checking

### 3. API Endpoints for Mobile App

#### Get Current Version
```
GET /api/app-version/current
```

Response:
```json
{
  "success": true,
  "data": {
    "version_number": "1.2.0",
    "version_name": "Bug Fixes",
    "force_update": false,
    "min_supported_version": "1.0.0",
    "release_notes": "Fixed login issues and improved performance",
    "download_url": "https://example.com/app.apk"
  }
}
```

#### Check if Update is Needed
```
POST /api/app-version/check
Content-Type: application/json

{
  "current_version": "1.1.0"
}
```

Response:
```json
{
  "success": true,
  "data": {
    "needs_update": true,
    "force_update": false,
    "latest_version": "1.2.0",
    "version_name": "Bug Fixes",
    "release_notes": "Fixed login issues and improved performance",
    "download_url": "https://example.com/app.apk"
  }
}
```

## Mobile App Integration

### Example Implementation (React Native/Flutter)

```javascript
// Check for updates on app launch
const checkForUpdates = async () => {
  try {
    const currentVersion = "1.1.0"; // Get from app config

    const response = await fetch('/api/app-version/check', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        current_version: currentVersion
      })
    });

    const result = await response.json();

    if (result.success && result.data.needs_update) {
      // Show update dialog
      showUpdateDialog({
        message: result.data.release_notes,
        isForced: result.data.force_update,
        downloadUrl: result.data.download_url
      });
    }
  } catch (error) {
    console.error('Failed to check for updates:', error);
  }
};

// Call on app start
checkForUpdates();
```

### Update Dialog Logic

1. **Optional Update**: Show dialog with "Update" and "Later" buttons
2. **Force Update**: Show dialog with only "Update" button, prevent app usage until updated
3. **Version Below Minimum**: Force update required, show appropriate message

## Usage Workflow

### For Administrators:

1. **Release New Version**:
   - Access `/app-versions` in web browser
   - Click "Create New Version"
   - Fill in version details:
     - Version Number (e.g., "1.2.0")
     - Version Name (e.g., "Bug Fix Release")
     - Release Notes (what's new)
     - Download URL (where users can get the new version)
     - Force Update checkbox (if mandatory)
     - Min Supported Version (oldest version still allowed)
   - Save the version

2. **Activate Version**:
   - New versions are automatically set as active
   - To change active version, click "Set Active" on desired version

3. **Test Updates**:
   - Use the test section to simulate version checks
   - Enter a version number and click "Test Check"
   - Verify the response is correct

### For Mobile App:

1. **On App Launch**:
   - Call `/api/app-version/check` with current version
   - Parse response to determine if update is needed
   - Show appropriate update dialog based on `force_update` flag

2. **Handle Updates**:
   - Optional updates: Allow user to skip
   - Force updates: Block app usage until updated
   - Provide download link or redirect to app store

## API Response Handling

### Success Response
```json
{
  "success": true,
  "data": { ... }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "errors": { ... } // Only for validation errors
}
```

## Security Notes

- Admin routes require authentication
- Public API endpoints (`/api/app-version/current` and `/api/app-version/check`) are accessible without authentication
- Version numbers should follow semantic versioning (e.g., 1.0.0, 1.0.1, 1.1.0)

## Troubleshooting

### Common Issues:

1. **Migration Fails**: Ensure database connection is properly configured
2. **Admin Page Not Loading**: Check that routes are registered and user has proper permissions
3. **API Returns 404**: Verify API routes are registered in `routes/api.php`
4. **Version Comparison Issues**: Use consistent version numbering format

### Debugging:

- Check Laravel logs: `storage/logs/laravel.log`
- Test API endpoints directly using Postman or curl
- Use browser developer tools to inspect network requests
- Check database records in `app_versions` table

## Future Enhancements

Potential additions to the system:
- Automatic rollback functionality
- A/B testing for version releases
- User analytics for update adoption
- Integration with app stores for automatic updates
- Scheduled version releases