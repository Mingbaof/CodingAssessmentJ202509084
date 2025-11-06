## Install PHP

### MacOS:

- `brew install php@8.1`
- Then add it to PATH
- `php -v`

### Windows(Reference: https://www.youtube.com/watch?v=5LtyTo4KArk):

- Go to https://windows.php.net/download/
- Download `PHP 8.1 (8.1.33)`, `VS16 x64 Thread Safe (2025-Jul-02 09:11:40)` Zip file
- Go to `C:` drive and create a new folder named `php`
- Extract the entire contents of the `zip` file into `C:\php`
- Add PHP to PATH / `$env:PATH = [Environment]::GetEnvironmentVariable("PATH", "User") + ";" + [Environment]::GetEnvironmentVariable("PATH", "Machine")`
- In the `C:\php` folder, rename `php.ini-development` to `php.ini`
- `php -- version`

## Install Composer

### Windows:

- `https://getcomposer.org/download/`
- Download and run the `Composer-Setup.exe`
- Follow the instructions and verify with `compose --version`

### MacOS:

- `brew install composer`
- `composer --version`

## Install Node

### Windows/MacOS:

- `https://nodejs.org/en/download`
