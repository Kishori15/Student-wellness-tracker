# Fix "rejected - fetch first" by pulling remote README then pushing
Set-Location "c:\xampp\htdocs\STUDENTWELLNESSTRACKER"

Write-Host "Pulling remote changes (GitHub README) and merging..." -ForegroundColor Cyan
git pull origin main --allow-unrelated-histories --no-edit

if ($LASTEXITCODE -eq 0) {
    Write-Host "Pushing to GitHub..." -ForegroundColor Cyan
    git push -u origin main
    if ($LASTEXITCODE -eq 0) {
        Write-Host "Done. Your repo is at https://github.com/Kishori15/Student-wellness-tracker" -ForegroundColor Green
    }
} else {
    Write-Host "Pull had issues. Run: git status" -ForegroundColor Yellow
}
Write-Host ""
Read-Host "Press Enter to close"
