#====================================================================================================
# START - Testing Protocol - DO NOT EDIT OR REMOVE THIS SECTION
#====================================================================================================

# THIS SECTION CONTAINS CRITICAL TESTING INSTRUCTIONS FOR BOTH AGENTS
# BOTH MAIN_AGENT AND TESTING_AGENT MUST PRESERVE THIS ENTIRE BLOCK

# Communication Protocol:
# If the `testing_agent` is available, main agent should delegate all testing tasks to it.
#
# You have access to a file called `test_result.md`. This file contains the complete testing state
# and history, and is the primary means of communication between main and the testing agent.
#
# Main and testing agents must follow this exact format to maintain testing data. 
# The testing data must be entered in yaml format Below is the data structure:
# 
## user_problem_statement: {problem_statement}
## backend:
##   - task: "Task name"
##     implemented: true
##     working: true  # or false or "NA"
##     file: "file_path.py"
##     stuck_count: 0
##     priority: "high"  # or "medium" or "low"
##     needs_retesting: false
##     status_history:
##         -working: true  # or false or "NA"
##         -agent: "main"  # or "testing" or "user"
##         -comment: "Detailed comment about status"
##
## frontend:
##   - task: "Task name"
##     implemented: true
##     working: true  # or false or "NA"
##     file: "file_path.js"
##     stuck_count: 0
##     priority: "high"  # or "medium" or "low"
##     needs_retesting: false
##     status_history:
##         -working: true  # or false or "NA"
##         -agent: "main"  # or "testing" or "user"
##         -comment: "Detailed comment about status"
##
## metadata:
##   created_by: "main_agent"
##   version: "1.0"
##   test_sequence: 0
##   run_ui: false
##
## test_plan:
##   current_focus:
##     - "Task name 1"
##     - "Task name 2"
##   stuck_tasks:
##     - "Task name with persistent issues"
##   test_all: false
##   test_priority: "high_first"  # or "sequential" or "stuck_first"
##
## agent_communication:
##     -agent: "main"  # or "testing" or "user"
##     -message: "Communication message between agents"

# Protocol Guidelines for Main agent
#
# 1. Update Test Result File Before Testing:
#    - Main agent must always update the `test_result.md` file before calling the testing agent
#    - Add implementation details to the status_history
#    - Set `needs_retesting` to true for tasks that need testing
#    - Update the `test_plan` section to guide testing priorities
#    - Add a message to `agent_communication` explaining what you've done
#
# 2. Incorporate User Feedback:
#    - When a user provides feedback that something is or isn't working, add this information to the relevant task's status_history
#    - Update the working status based on user feedback
#    - If a user reports an issue with a task that was marked as working, increment the stuck_count
#    - Whenever user reports issue in the app, if we have testing agent and task_result.md file so find the appropriate task for that and append in status_history of that task to contain the user concern and problem as well 
#
# 3. Track Stuck Tasks:
#    - Monitor which tasks have high stuck_count values or where you are fixing same issue again and again, analyze that when you read task_result.md
#    - For persistent issues, use websearch tool to find solutions
#    - Pay special attention to tasks in the stuck_tasks list
#    - When you fix an issue with a stuck task, don't reset the stuck_count until the testing agent confirms it's working
#
# 4. Provide Context to Testing Agent:
#    - When calling the testing agent, provide clear instructions about:
#      - Which tasks need testing (reference the test_plan)
#      - Any authentication details or configuration needed
#      - Specific test scenarios to focus on
#      - Any known issues or edge cases to verify
#
# 5. Call the testing agent with specific instructions referring to test_result.md
#
# IMPORTANT: Main agent must ALWAYS update test_result.md BEFORE calling the testing agent, as it relies on this file to understand what to test next.

#====================================================================================================
# END - Testing Protocol - DO NOT EDIT OR REMOVE THIS SECTION
#====================================================================================================



#====================================================================================================
# Testing Data - Main Agent and testing sub agent both should log testing data below this section
#====================================================================================================

user_problem_statement: |
  Ứng dụng quản lý văn bản đã được migrate sang cPanel với MySQL/PHP backend.
  Hiện có 2 vấn đề cần fix:
  1. PHP Handler: File PHP hiển thị mã nguồn thô thay vì execute
  2. Google Drive Integration: Upload file thành công nhưng không xuất hiện trong folder, không xem/download được

backend:
  - task: "Fix PHP Handler - ensure PHP files execute instead of showing source code"
    implemented: true
    working: "pending_user_testing"
    file: "/app/cpanel_deployment/api/.htaccess"
    stuck_count: 0
    priority: "high"
    needs_retesting: false
    status_history:
      - working: "pending_user_testing"
        agent: "main"
        comment: |
          - Updated .htaccess with PHP handler directives (AddHandler application/x-httpd-php .php)
          - Created comprehensive guide FIX_PHP_HANDLER.md with step-by-step instructions
          - Guide includes:
            * Configure PHP version in cPanel (8.0 or 8.1)
            * Enable required PHP extensions
            * Configure PHP handler settings
            * Update .htaccess with proper directives
            * Test files created (test-php.php, test-json.php)
          - User needs to follow guide and test on their cPanel hosting
  
  - task: "Fix Google Drive Integration - upload, permissions, and file access"
    implemented: true
    working: "pending_user_testing"
    file: "/app/cpanel_deployment/api/google_drive.php, /app/cpanel_deployment/api/config.php"
    stuck_count: 0
    priority: "high"
    needs_retesting: false
    status_history:
      - working: "pending_user_testing"
        agent: "main"
        comment: |
          - Created test_google_drive.php for comprehensive testing
          - Created FIX_GOOGLE_DRIVE.md with detailed troubleshooting guide
          - Guide covers:
            * Verify service account credentials
            * Share Google Drive folder with service account email (EDITOR role)
            * Install Google API PHP Client via composer or manual upload
            * Configure config.php (GOOGLE_DRIVE_ENABLED, folder ID)
            * Test suite checks: PHP version, credentials, folder access, API client
            * Upload test file and verify in Google Drive
            * File permissions settings for public access
          - User needs to:
            1. Share folder 1OBWCc80JfExTWyUxx3W2I6ltY8CZILa0 with service account
            2. Upload google-credentials.json to /public_html/api/
            3. Install vendor/autoload.php (Google API Client)
            4. Run test_google_drive.php to verify all components

metadata:
  created_by: "main_agent"
  version: "1.0"
  test_sequence: 0
  run_ui: false

test_plan:
  current_focus:
    - "Fix PHP Handler on cPanel"
    - "Fix Google Drive Integration"
  stuck_tasks: []
  test_all: false
  test_priority: "high_first"

agent_communication:
  - agent: "main"
    message: |
      ✅ Đã tạo đầy đủ:
      1. File test_google_drive.php - comprehensive test suite
      2. FIX_PHP_HANDLER.md - hướng dẫn chi tiết fix PHP handler
      3. FIX_GOOGLE_DRIVE.md - hướng dẫn chi tiết fix Google Drive
      4. Cập nhật .htaccess với PHP handler directives
      5. Package zip sẵn sàng upload: cpanel_upload_package.zip
      
      User cần làm:
      1. Download package và upload lên cPanel
      2. Làm theo FIX_PHP_HANDLER.md để fix PHP execution
      3. Làm theo FIX_GOOGLE_DRIVE.md để cấu hình Google Drive
      4. Test bằng các file test đã cung cấp
      
      Chờ user test và báo kết quả.