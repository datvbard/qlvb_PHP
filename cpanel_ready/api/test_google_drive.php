<?php
require_once 'config.php';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Test Google Drive Connection</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #A52A47;
            padding-bottom: 10px;
        }
        .success {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #4CAF50;
            margin: 10px 0;
        }
        .error {
            background: #ffebee;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #f44336;
            margin: 10px 0;
        }
        .warning {
            background: #fff3e0;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #ff9800;
            margin: 10px 0;
        }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #2196F3;
            margin: 10px 0;
        }
        .check-item {
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
        }
        .check-pass {
            background: #e8f5e9;
            border-left: 3px solid #4CAF50;
        }
        .check-fail {
            background: #ffebee;
            border-left: 3px solid #f44336;
        }
        pre {
            background: #263238;
            color: #aed581;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Google Drive Connection Test</h1>
        
        <?php
        $checks = [];
        $allPassed = true;
        
        // Check 1: Google Drive Enabled
        if (GOOGLE_DRIVE_ENABLED) {
            $checks[] = ['pass' => true, 'message' => '✅ Google Drive is ENABLED in config'];
        } else {
            $checks[] = ['pass' => false, 'message' => '❌ Google Drive is DISABLED in config'];
            $allPassed = false;
        }
        
        // Check 2: Credentials File Exists
        if (file_exists(GOOGLE_CREDENTIALS_FILE)) {
            $checks[] = ['pass' => true, 'message' => '✅ Credentials file exists: ' . GOOGLE_CREDENTIALS_FILE];
            
            // Check file size
            $filesize = filesize(GOOGLE_CREDENTIALS_FILE);
            if ($filesize > 100) {
                $checks[] = ['pass' => true, 'message' => '✅ Credentials file size OK: ' . round($filesize/1024, 2) . ' KB'];
            } else {
                $checks[] = ['pass' => false, 'message' => '❌ Credentials file too small: ' . $filesize . ' bytes'];
                $allPassed = false;
            }
            
            // Try to read JSON
            $json = @file_get_contents(GOOGLE_CREDENTIALS_FILE);
            if ($json) {
                $data = @json_decode($json, true);
                if ($data) {
                    $checks[] = ['pass' => true, 'message' => '✅ Credentials JSON is valid'];
                    
                    if (isset($data['client_email'])) {
                        $checks[] = ['pass' => true, 'message' => '✅ Service Account Email: ' . $data['client_email']];
                    }
                    if (isset($data['project_id'])) {
                        $checks[] = ['pass' => true, 'message' => '✅ Project ID: ' . $data['project_id']];
                    }
                } else {
                    $checks[] = ['pass' => false, 'message' => '❌ Invalid JSON in credentials file'];
                    $allPassed = false;
                }
            } else {
                $checks[] = ['pass' => false, 'message' => '❌ Cannot read credentials file'];
                $allPassed = false;
            }
        } else {
            $checks[] = ['pass' => false, 'message' => '❌ Credentials file NOT FOUND: ' . GOOGLE_CREDENTIALS_FILE];
            $allPassed = false;
        }
        
        // Check 3: Folder ID
        if (!empty(GOOGLE_DRIVE_FOLDER_ID)) {
            $checks[] = ['pass' => true, 'message' => '✅ Folder ID configured: ' . GOOGLE_DRIVE_FOLDER_ID];
        } else {
            $checks[] = ['pass' => false, 'message' => '❌ Folder ID is EMPTY'];
            $allPassed = false;
        }
        
        // Check 4: Vendor/Autoload
        $autoloadPath = __DIR__ . '/vendor/autoload.php';
        if (file_exists($autoloadPath)) {
            $checks[] = ['pass' => true, 'message' => '✅ Google API Client library installed (vendor/autoload.php found)'];
            
            // Try to load
            require_once $autoloadPath;
            
            if (class_exists('Google_Client')) {
                $checks[] = ['pass' => true, 'message' => '✅ Google_Client class available'];
                
                // Try to initialize
                try {
                    $client = new Google_Client();
                    $client->setAuthConfig(GOOGLE_CREDENTIALS_FILE);
                    $client->addScope(Google_Service_Drive::DRIVE_FILE);
                    
                    $checks[] = ['pass' => true, 'message' => '✅ Google Client initialized successfully'];
                    
                    // Try to create Drive service
                    $service = new Google_Service_Drive($client);
                    $checks[] = ['pass' => true, 'message' => '✅ Google Drive Service created'];
                    
                    // Try to list files in folder
                    try {
                        $response = $service->files->listFiles([
                            'q' => "'" . GOOGLE_DRIVE_FOLDER_ID . "' in parents",
                            'pageSize' => 5,
                            'fields' => 'files(id, name)'
                        ]);
                        
                        $files = $response->getFiles();
                        $checks[] = ['pass' => true, 'message' => '✅ Successfully connected to Google Drive!'];
                        $checks[] = ['pass' => true, 'message' => '✅ Files in folder: ' . count($files)];
                        
                        if (count($files) > 0) {
                            echo '<div class="info"><strong>📁 Files in your Drive folder:</strong><ul>';
                            foreach ($files as $file) {
                                echo '<li>' . htmlspecialchars($file->getName()) . '</li>';
                            }
                            echo '</ul></div>';
                        }
                        
                    } catch (Exception $e) {
                        $checks[] = ['pass' => false, 'message' => '❌ Cannot access Drive folder: ' . $e->getMessage()];
                        $allPassed = false;
                    }
                    
                } catch (Exception $e) {
                    $checks[] = ['pass' => false, 'message' => '❌ Cannot initialize Google Client: ' . $e->getMessage()];
                    $allPassed = false;
                }
                
            } else {
                $checks[] = ['pass' => false, 'message' => '❌ Google_Client class NOT FOUND'];
                $allPassed = false;
            }
            
        } else {
            $checks[] = ['pass' => false, 'message' => '❌ Google API Client NOT INSTALLED (vendor/autoload.php not found)'];
            $allPassed = false;
        }
        
        // Display results
        echo '<h2>📊 Test Results:</h2>';
        
        foreach ($checks as $check) {
            $class = $check['pass'] ? 'check-pass' : 'check-fail';
            echo '<div class="check-item ' . $class . '">' . $check['message'] . '</div>';
        }
        
        if ($allPassed) {
            echo '<div class="success">';
            echo '<h3>🎉 ALL CHECKS PASSED!</h3>';
            echo '<p>Google Drive integration is working perfectly!</p>';
            echo '<p>You can now upload files through the website.</p>';
            echo '</div>';
        } else {
            echo '<div class="error">';
            echo '<h3>⚠️ SOME CHECKS FAILED</h3>';
            echo '<p>Please fix the issues above before uploading files.</p>';
            echo '</div>';
        }
        ?>
        
        <h2>📝 Configuration Info:</h2>
        <div class="info">
            <strong>GOOGLE_DRIVE_ENABLED:</strong> <?php echo GOOGLE_DRIVE_ENABLED ? 'true' : 'false'; ?><br>
            <strong>GOOGLE_CREDENTIALS_FILE:</strong> <?php echo GOOGLE_CREDENTIALS_FILE; ?><br>
            <strong>GOOGLE_DRIVE_FOLDER_ID:</strong> <?php echo GOOGLE_DRIVE_FOLDER_ID ?: '(empty)'; ?><br>
        </div>
        
        <?php if (!$allPassed): ?>
        <h2>🔧 What to do next:</h2>
        <div class="warning">
            <ol>
                <li>Read <strong>HUONG_DAN_GOOGLE_DRIVE.txt</strong> for detailed setup instructions</li>
                <li>Make sure you completed all 10 steps</li>
                <li>Verify Service Account email is shared with the Drive folder</li>
                <li>Check that google-credentials.json is uploaded correctly</li>
                <li>Install Google API PHP Client if missing</li>
                <li>Refresh this page after fixing issues</li>
            </ol>
        </div>
        <?php endif; ?>
        
        <div class="warning">
            <strong>⚠️ Security Note:</strong><br>
            Delete this test file after verification:<br>
            <code>/public_html/api/test_google_drive.php</code>
        </div>
    </div>
</body>
</html>
