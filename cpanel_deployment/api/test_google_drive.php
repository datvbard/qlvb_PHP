<?php
require_once 'config.php';

// ===================================
// TEST GOOGLE DRIVE CONFIGURATION
// ===================================

header('Content-Type: application/json; charset=utf-8');

function testGoogleDrive() {
    $results = [];
    
    // Test 1: Check PHP version
    $results['php_version'] = [
        'version' => PHP_VERSION,
        'status' => version_compare(PHP_VERSION, '7.4.0', '>=') ? 'OK' : 'FAIL',
        'message' => 'PHP version should be 7.4 or higher'
    ];
    
    // Test 2: Check if Google Drive enabled
    $results['google_drive_enabled'] = [
        'value' => GOOGLE_DRIVE_ENABLED,
        'status' => GOOGLE_DRIVE_ENABLED ? 'OK' : 'DISABLED',
        'message' => 'Google Drive integration status'
    ];
    
    // Test 3: Check credentials file
    if (GOOGLE_DRIVE_ENABLED) {
        $credentialsExists = file_exists(GOOGLE_CREDENTIALS_FILE);
        $results['credentials_file'] = [
            'path' => GOOGLE_CREDENTIALS_FILE,
            'exists' => $credentialsExists,
            'status' => $credentialsExists ? 'OK' : 'FAIL',
            'message' => 'Google credentials file check'
        ];
        
        // Test 4: Check credentials content
        if ($credentialsExists) {
            $credentials = json_decode(file_get_contents(GOOGLE_CREDENTIALS_FILE), true);
            $results['credentials_content'] = [
                'type' => $credentials['type'] ?? 'unknown',
                'client_email' => $credentials['client_email'] ?? 'not found',
                'status' => (isset($credentials['type']) && $credentials['type'] === 'service_account') ? 'OK' : 'FAIL',
                'message' => 'Credentials file structure validation'
            ];
        }
        
        // Test 5: Check folder ID
        $results['folder_id'] = [
            'value' => GOOGLE_DRIVE_FOLDER_ID,
            'status' => !empty(GOOGLE_DRIVE_FOLDER_ID) ? 'OK' : 'FAIL',
            'message' => 'Google Drive folder ID configuration'
        ];
        
        // Test 6: Check if composer autoload exists
        $autoloadPath = __DIR__ . '/vendor/autoload.php';
        $autoloadExists = file_exists($autoloadPath);
        $results['composer_autoload'] = [
            'path' => $autoloadPath,
            'exists' => $autoloadExists,
            'status' => $autoloadExists ? 'OK' : 'FAIL',
            'message' => 'Google API PHP Client (composer) check'
        ];
        
        // Test 7: Try to initialize Google Client
        if ($autoloadExists) {
            try {
                require_once $autoloadPath;
                require_once 'google_drive.php';
                
                $service = getGoogleDriveService();
                
                if ($service) {
                    $results['google_client'] = [
                        'status' => 'OK',
                        'message' => 'Google Drive service initialized successfully'
                    ];
                    
                    // Test 8: Try to list files (verify permissions)
                    try {
                        $response = $service->files->listFiles([
                            'q' => "'" . GOOGLE_DRIVE_FOLDER_ID . "' in parents",
                            'pageSize' => 5,
                            'fields' => 'files(id, name)'
                        ]);
                        
                        $results['drive_access'] = [
                            'status' => 'OK',
                            'files_count' => count($response->files),
                            'message' => 'Successfully accessed Google Drive folder',
                            'files' => array_map(function($file) {
                                return ['id' => $file->id, 'name' => $file->name];
                            }, $response->files)
                        ];
                    } catch (Exception $e) {
                        $results['drive_access'] = [
                            'status' => 'FAIL',
                            'error' => $e->getMessage(),
                            'message' => 'Cannot access Google Drive folder - check service account permissions'
                        ];
                    }
                } else {
                    $results['google_client'] = [
                        'status' => 'FAIL',
                        'message' => 'Failed to initialize Google Drive service'
                    ];
                }
            } catch (Exception $e) {
                $results['google_client'] = [
                    'status' => 'FAIL',
                    'error' => $e->getMessage(),
                    'message' => 'Error initializing Google client'
                ];
            }
        }
    }
    
    // Overall status
    $hasFailures = false;
    foreach ($results as $test) {
        if (isset($test['status']) && $test['status'] === 'FAIL') {
            $hasFailures = true;
            break;
        }
    }
    
    return [
        'overall_status' => $hasFailures ? 'FAIL' : 'OK',
        'timestamp' => date('Y-m-d H:i:s'),
        'tests' => $results
    ];
}

try {
    $result = testGoogleDrive();
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode([
        'overall_status' => 'ERROR',
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}

?>
