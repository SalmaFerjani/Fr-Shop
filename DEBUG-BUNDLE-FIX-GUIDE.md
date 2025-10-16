# DebugBundle Error Fix Guide

## Problem
You encountered a `ClassNotFoundError` for `Symfony\Bundle\DebugBundle\DebugBundle` because the dev dependencies were not properly installed.

## What I Did
1. **Temporarily disabled missing bundles** in `config/bundles.php`:
   - `Symfony\Bundle\DebugBundle\DebugBundle`
   - `Symfony\Bundle\WebProfilerBundle\WebProfilerBundle`
   - `Symfony\Bundle\MakerBundle\MakerBundle`

2. **Created an installation script** (`install_dev_dependencies.bat`) to help you install the missing dependencies.

## How to Fix Permanently

### Option 1: Use the Batch Script (Recommended)
1. Double-click `install_dev_dependencies.bat` in your project root
2. The script will automatically find PHP and Composer in your Laragon installation
3. Install the missing dev dependencies

### Option 2: Manual Installation
1. Open Command Prompt or PowerShell in your project directory
2. Run: `composer install --dev`
3. If composer is not in PATH, use the full path to PHP and Composer

### Option 3: Using Laragon Terminal
1. Open Laragon Terminal
2. Navigate to your project: `cd D:\laragon\www\BoutiqueProd`
3. Run: `composer install --dev`

## After Installation
Once the dependencies are installed, you can re-enable the bundles by uncommenting these lines in `config/bundles.php`:

```php
Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true],
Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
```

## Why This Happened
- Your project is in `dev` environment (`APP_ENV=dev`)
- The DebugBundle and other dev bundles are configured to load only in dev environment
- These bundles were not installed in the vendor directory
- Symfony tried to load them but couldn't find the classes

## Current Status
Your application should now work without the DebugBundle error. The core functionality will work, but you won't have:
- Debug toolbar
- Web profiler
- Symfony Maker commands

These features will be restored once you install the dev dependencies.
