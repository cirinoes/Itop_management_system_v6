$baseUrl = "http://localhost:8000"
$cssPath = "c:\xampp\htdocs\Itop_management_system_v6\public\assets\css\app.css"
$cssContent = Get-Content $cssPath -Raw

$roles = @{
    "PUBLIC USER" = @{
        "role" = "public"
        "pages" = @{
            "Homepage" = "home"
            "About" = "about"
            "Courses" = "courses"
            "Course Detail" = "course"
            "News" = "news"
            "Contact" = "contact"
            "Login" = "login"
            "Register" = "register"
            "Verify Certificate" = "verify-certificate"
            "View Certificate" = "view-certificate"
        }
    }
    "TRAINEE" = @{
        "role" = "trainee"
        "pages" = @{
            "Dashboard" = "trainee-dashboard"
            "Profile" = "trainee-profile"
            "Evaluations" = "trainee-evaluations"
            "Certificates" = "trainee-certificates"
            "Messages" = "messages"
            "Notifications" = "notifications"
            "Course Room" = "course-room"
            "My Learning Report" = "my-learning-report"
        }
    }
    "INSTRUCTOR" = @{
        "role" = "instructor"
        "pages" = @{
            "Dashboard" = "instructor-dashboard"
            "Courses" = "instructor-courses"
            "Reports" = "instructor-reports"
            "Enrolments" = "instructor-enrolments"
        }
    }
    "ADMINISTRATOR" = @{
        "role" = "admin"
        "pages" = @{
            "Dashboard" = "admin-dashboard"
            "Users" = "admin-users"
            "User Detail" = "admin-user-detail"
            "Courses" = "admin-courses"
            "Course Detail" = "admin-course-detail"
            "Enrolments" = "admin-enrolments"
            "Enrolment Detail" = "admin-enrolment-detail"
            "Certificates" = "admin-certificates"
            "Certificate Logs" = "admin-certificate-logs"
            "Documentation" = "admin-documentation"
            "Evaluations" = "admin-evaluations"
            "Master Data" = "admin-master-data"
            "Website Settings" = "admin-website-settings"
            "Analytics" = "admin-analytics"
            "Analytics Detail" = "admin-analytics-detail"
            "Participants" = "admin-participants"
            "Participant Detail" = "admin-participant-detail"
            "System Settings" = "admin-system-settings"
            "Profile" = "admin-profile"
        }
    }
}

$outDir = "c:\xampp\htdocs\Itop_management_system_v6\Stitch_Export"
if (Test-Path $outDir) { Remove-Item $outDir -Recurse -Force }
New-Item -ItemType Directory -Force -Path $outDir | Out-Null

$uploadScript = "c:\xampp\htdocs\Itop_management_system_v6\upload_to_stitch.ps1"
$header = @"
`$configPath = `"C:\Users\ckuli\.gemini\config\mcp_config.json`"
`$config = Get-Content `$configPath | ConvertFrom-Json
`$env:STITCH_API_KEY = `$config.mcpServers.stitch.env.STITCH_API_KEY
Write-Host 'Starting bulk upload to Stitch...'
"@
$header | Set-Content $uploadScript -Encoding UTF8

foreach ($groupName in $roles.Keys) {
    $roleInfo = $roles[$groupName]
    $roleSlug = $roleInfo["role"]
    $pages = $roleInfo["pages"]

    $groupDir = Join-Path $outDir $groupName
    New-Item -ItemType Directory -Force -Path $groupDir | Out-Null

    $session = $null
    if ($roleSlug -ne "public") {
        Invoke-WebRequest -Uri "$baseUrl/login_as.php?role=$roleSlug" -UseBasicParsing -SessionVariable session | Out-Null
    }

    foreach ($pageName in $pages.Keys) {
        $pageSlug = $pages[$pageName]
        $url = "$baseUrl/index.php?page=$pageSlug"
        Write-Host "Scraping [$groupName] $pageName -> $url"
        
        $reqArgs = @{
            Uri = $url
            UseBasicParsing = $true
        }
        if ($session) {
            $reqArgs["WebSession"] = $session
        }

        try {
            $response = Invoke-WebRequest @reqArgs
            $html = $response.Content
            
            # Inline the CSS
            $html = $html -replace '<link[^>]+app\.css[^>]+>', "<style>
$cssContent
body.motion-ready main, body.motion-ready .main-content, .reveal-on-scroll {
    animation: none !important;
    opacity: 1 !important;
    transform: none !important;
}
</style>"
            
            $savePath = Join-Path $groupDir "$pageName.html"
            $html | Set-Content -Path $savePath -Encoding UTF8
            
            $stitchTitle = "[$groupName] $pageName"
            $cmd = "npx -y @_davideast/stitch-mcp upload -p 17799432416129995673 -f `"$savePath`" --title `"$stitchTitle`""
            Add-Content -Path $uploadScript -Value $cmd
            
        } catch {
            Write-Host "Failed to scrape $url"
        }
    }
}

Write-Host "Scraping complete! Run upload_to_stitch.ps1 to push to Stitch."
