$baseUrl = "http://localhost:8080"
$cssPath = "c:\xampp\htdocs\Itop_management_system\public\assets\css\app.css"
$cssContent = Get-Content $cssPath -Raw

$roles = @{
    "PUBLIC USER" = @{
        "role" = "public"
        "pages" = @{
            "Homepage" = "home"
            "Courses" = "courses"
            "Login" = "login"
            "Register" = "register"
            "About" = "about"
        }
    }
    "TRAINEE" = @{
        "role" = "trainee"
        "pages" = @{
            "Dashboard" = "trainee-dashboard"
            "Profile" = "trainee-profile"
            "Certificates" = "trainee-certificates"
            "Messages" = "messages"
        }
    }
    "INSTRUCTOR" = @{
        "role" = "instructor"
        "pages" = @{
            "Dashboard" = "instructor-dashboard"
            "Courses" = "instructor-courses"
            "Reports" = "instructor-reports"
        }
    }
    "ADMINISTRATOR" = @{
        "role" = "admin"
        "pages" = @{
            "Dashboard" = "admin-dashboard"
            "Users" = "admin-users"
            "Courses" = "admin-courses"
            "Settings" = "admin-system-settings"
        }
    }
}

$outDir = "c:\xampp\htdocs\Itop_management_system\Stitch_Export"
if (Test-Path $outDir) { Remove-Item $outDir -Recurse -Force }
New-Item -ItemType Directory -Force -Path $outDir | Out-Null

$uploadScript = "c:\xampp\htdocs\Itop_management_system\upload_to_stitch.ps1"
$header = @"
`$configPath = `"C:\Users\User\.gemini\config\mcp_config.json`"
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

