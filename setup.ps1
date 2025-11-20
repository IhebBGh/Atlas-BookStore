# Symfony Project Setup Script
# Run this script in PowerShell to set up your project

Write-Host "=== Symfony Project Setup ===" -ForegroundColor Green
Write-Host ""

# Check if PHP is available
Write-Host "Checking PHP..." -ForegroundColor Yellow
$phpVersion = php -v 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: PHP is not found in PATH. Please ensure WAMP is running and PHP is in your PATH." -ForegroundColor Red
    exit 1
}
Write-Host "PHP found: $($phpVersion[0])" -ForegroundColor Green
Write-Host ""

# Check if Composer dependencies are installed
Write-Host "Checking dependencies..." -ForegroundColor Yellow
if (-not (Test-Path "vendor\autoload.php")) {
    Write-Host "Installing Composer dependencies..." -ForegroundColor Yellow
    composer install
    if ($LASTEXITCODE -ne 0) {
        Write-Host "ERROR: Failed to install dependencies." -ForegroundColor Red
        exit 1
    }
} else {
    Write-Host "Dependencies already installed." -ForegroundColor Green
}
Write-Host ""

# Create database
Write-Host "Creating database..." -ForegroundColor Yellow
php bin/console doctrine:database:create --if-not-exists
if ($LASTEXITCODE -ne 0) {
    Write-Host "WARNING: Database creation failed. It might already exist or MySQL is not running." -ForegroundColor Yellow
    Write-Host "Please ensure:" -ForegroundColor Yellow
    Write-Host "  1. WAMP MySQL service is running" -ForegroundColor Yellow
    Write-Host "  2. Database 'projec_db' exists in MySQL" -ForegroundColor Yellow
    Write-Host "  3. MySQL credentials in .env are correct" -ForegroundColor Yellow
} else {
    Write-Host "Database created successfully." -ForegroundColor Green
}
Write-Host ""

# Run migrations
Write-Host "Running database migrations..." -ForegroundColor Yellow
php bin/console doctrine:migrations:migrate --no-interaction
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: Migrations failed. Please check the error above." -ForegroundColor Red
    exit 1
}
Write-Host "Migrations completed successfully." -ForegroundColor Green
Write-Host ""

# Clear cache
Write-Host "Clearing cache..." -ForegroundColor Yellow
php bin/console cache:clear
Write-Host "Cache cleared." -ForegroundColor Green
Write-Host ""

Write-Host "=== Setup Complete! ===" -ForegroundColor Green
Write-Host ""
Write-Host "To start the development server, run one of these commands:" -ForegroundColor Cyan
Write-Host "  Option 1: php -S localhost:8000 -t public" -ForegroundColor White
Write-Host "  Option 2: symfony server:start (if Symfony CLI is installed)" -ForegroundColor White
Write-Host ""
Write-Host "Then visit: http://localhost:8000" -ForegroundColor Cyan

