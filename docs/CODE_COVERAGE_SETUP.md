# Code Coverage Setup with Xdebug

## Installing Xdebug on macOS

### 1. Install Xdebug via PECL

```bash
# Check PHP version
php -v

# Install Xdebug
pecl install xdebug

# If you get errors, try with sudo
sudo pecl install xdebug
```

### 2. Enable Xdebug in php.ini

```bash
# Find the php.ini file
php --ini

# Edit the php.ini file (or create an xdebug.ini file in conf.d/)
# Add these lines:
```

Content to add to `php.ini` or `/opt/homebrew/etc/php/8.4/conf.d/ext-xdebug.ini`:

```ini
[xdebug]
zend_extension="xdebug.so"
xdebug.mode=coverage,debug
xdebug.start_with_request=yes
```

### 3. Verify Installation

```bash
php -v
# Should display "with Xdebug v3.x.x"

php -m | grep xdebug
# Should display "xdebug"
```

### 4. Restart PHP-FPM (if using Valet)

```bash
valet restart
```

## Usage with Pest

### Generate HTML Coverage Report

```bash
./vendor/bin/pest --coverage --coverage-html coverage
```

### Generate Text Coverage Report

```bash
./vendor/bin/pest --coverage
```

### With Minimum Coverage Threshold

```bash
./vendor/bin/pest --coverage --min=80
```

### Parallel Mode with Coverage

```bash
./vendor/bin/pest --parallel --coverage
```

## Configuration in phpunit.xml

Add this section to your `phpunit.xml.dist`:

```xml
<coverage processUncoveredFiles="true">
    <include>
        <directory suffix=".php">src</directory>
    </include>
    <exclude>
        <directory>src/Testing</directory>
        <file>src/BlogrServiceProvider.php</file>
    </exclude>
    <report>
        <html outputDirectory="coverage-report"/>
        <text outputFile="php://stdout" showUncoveredFiles="true"/>
    </report>
</coverage>
```

## Alternative: PCOV (Faster than Xdebug)

PCOV is faster for code coverage than Xdebug:

```bash
# Install PCOV
pecl install pcov

# Configure in php.ini or conf.d/pcov.ini
[pcov]
extension=pcov.so
pcov.enabled=1
pcov.directory=/path/to/your/project/src
```

Then disable Xdebug for coverage and use PCOV:

```bash
# Temporarily disable Xdebug
php -d xdebug.mode=off ./vendor/bin/pest --coverage
```

## Troubleshooting

### "No code coverage driver available"

- Check that Xdebug or PCOV is installed: `php -m | grep -E 'xdebug|pcov'`
- Check configuration: `php -i | grep -E 'xdebug|pcov'`
- Check that `xdebug.mode` includes `coverage`

### Xdebug slows everything down

- Use PCOV instead for coverage
- Or disable Xdebug except for tests:

```bash
# Create an alias in ~/.zshrc
alias php-no-xdebug="php -d xdebug.mode=off"
alias pest="php -d xdebug.mode=off ./vendor/bin/pest"
```

### Permission denied during installation

```bash
# Use sudo
sudo pecl install xdebug

# Or install via Homebrew
brew install php@8.4
brew install php@8.4-xdebug
```

## Useful Commands

```bash
# Check Xdebug version
php -v

# List PHP modules
php -m

# Complete Xdebug info
php -i | grep xdebug

# Test Xdebug
php -r "var_dump(extension_loaded('xdebug'));"

# Generate complete coverage report
./vendor/bin/pest --coverage --coverage-html=coverage --coverage-clover=coverage.xml
```
