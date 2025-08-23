param(
  [Parameter(Mandatory=$true)] [string]$BaseUrl,       # ex: http://localhost:8000/api
  [Parameter(Mandatory=$true)] [string]$Token,         # Bearer token
  [Parameter(Mandatory=$true)] [int]$CourseId,
  [string]$ContentType = 'video/mp4',
  [double]$Position = 12.34
)

$Headers = @{ Authorization = "Bearer $Token" }

Write-Host "1) Init upload..." -ForegroundColor Cyan
$initBody = @{ contentType = $ContentType } | ConvertTo-Json
$initResp = Invoke-RestMethod -Method POST -Uri "$BaseUrl/admin/cursos/$CourseId/video/upload-init" -Headers $Headers -ContentType 'application/json' -Body $initBody
$initResp | ConvertTo-Json -Depth 5

Write-Host "2) Complete upload..." -ForegroundColor Cyan
$completeResp = Invoke-RestMethod -Method POST -Uri "$BaseUrl/admin/cursos/$CourseId/video/upload-complete" -Headers $Headers
$completeResp | ConvertTo-Json -Depth 5

Write-Host "3) Get playback info..." -ForegroundColor Cyan
$playbackResp = Invoke-RestMethod -Method GET -Uri "$BaseUrl/admin/cursos/$CourseId/video/playback" -Headers $Headers
$playbackResp | ConvertTo-Json -Depth 5

Write-Host "4) Send playback progress..." -ForegroundColor Cyan
$progressBody = @{ position = $Position } | ConvertTo-Json
$progressResp = Invoke-RestMethod -Method POST -Uri "$BaseUrl/admin/cursos/$CourseId/video/progress" -Headers $Headers -ContentType 'application/json' -Body $progressBody
$progressResp | ConvertTo-Json -Depth 5

Write-Host "Done." -ForegroundColor Green
