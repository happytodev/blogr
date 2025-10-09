# Blogr Theme Switcher Setup Guide

This guide explains how to properly configure the Blogr theme switcher (light/dark/auto mode) in your Laravel application.

## Requirements

- Laravel 10+
- Filament 4.0+
- Tailwind CSS v4
- Alpine.js 3.x

## Installation Steps

### 1. Install Alpine.js

```bash
npm install alpinejs
```

### 2. Configure Alpine.js in your application

Edit `resources/js/app.js`:

```javascript
import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Blogr Theme Switcher Component (required for light/dark/auto mode)
Alpine.data('themeSwitch', () => ({
    theme: localStorage.getItem('theme') || 'auto',
    
    init() {
        this.applyTheme();
        
        // Watch for system preference changes when in auto mode
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (this.theme === 'auto') {
                this.applyTheme();
            }
        });
    },
    
    setTheme(newTheme) {
        this.theme = newTheme;
        localStorage.setItem('theme', newTheme);
        this.applyTheme();
    },
    
    applyTheme() {
        const isDark = this.theme === 'dark' || 
                      (this.theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        
        if (isDark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
}));

Alpine.start();
```

### 3. Configure Tailwind CSS v4 for dark mode

**⚠️ CRITICAL**: This step is **required** for the theme switcher to work.

Edit `resources/css/app.css` and add the dark mode variant:

```css
@import 'tailwindcss';

@plugin "@tailwindcss/typography";

/* Include Blogr package views */
@source '../../vendor/happytodev/blogr/resources/views/**/*.blade.php';
@source '../views/vendor/blogr/**/*.blade.php';

/* Your theme customizations */
@theme {
    /* Your custom theme variables */
}

/* REQUIRED: Dark mode variant for Blogr theme switcher */
@variant dark (.dark &);
```

### 4. Build your assets

```bash
npm run build
```

## How It Works

### Theme Modes

- **☀️ Light Mode**: Forces light theme regardless of system preference
- **🌙 Dark Mode**: Forces dark theme regardless of system preference
- **🌓 Auto Mode**: Follows system preference (default)

### System Preference Detection

The "Auto" mode uses the browser's `window.matchMedia('(prefers-color-scheme: dark)')` API to detect your operating system's theme preference.

**To enable system dark mode:**

- **Windows 11**: Settings → Personalization → Colors → Choose your mode → Dark
  - ⚠️ **Important**: Windows 11 does NOT have automatic light/dark switching based on time of day (unlike macOS)
  - The browser will detect the current Windows theme (Light or Dark), not a time-based schedule
  - **For automatic switching on Windows**, you need third-party apps like:
    - [Auto Dark Mode](https://github.com/AutoDarkMode/Windows-Auto-Night-Mode) (Free, Open Source)
    - Windows Auto Dark (alternative app)
    
- **Windows 10**: Settings → Personalization → Colors → Choose your color → Dark

- **macOS**: System Settings → Appearance → Dark
  - ✅ **Automatic switching available**: System Settings → Appearance → Auto
  - macOS can automatically switch between light and dark based on sunset/sunrise times

- **Linux (GNOME)**: Settings → Appearance → Style → Dark

**How it works:**
- When you select **Auto mode** in Blogr, the website checks `window.matchMedia('(prefers-color-scheme: dark)').matches`
- If your OS is set to Dark mode → returns `true` → website shows dark theme
- If your OS is set to Light mode → returns `false` → website shows light theme
- The browser automatically updates when you change your OS theme (no page refresh needed)

### Persistence

The selected theme is saved in `localStorage` and persists across browser sessions.

## Troubleshooting

### Dark mode doesn't visually apply

**Cause**: Missing `@variant dark (.dark &);` in your CSS.

**Solution**: Add the dark variant to `resources/css/app.css` and rebuild with `npm run build`.

### Auto mode doesn't detect system preference

**Diagnosis**: Open browser DevTools (F12) → Console, run:
```javascript
window.matchMedia('(prefers-color-scheme: dark)').matches
```

- If it returns `false` but you're in dark mode → Check your OS dark mode settings
- If it returns `true` but the UI stays light → Missing Tailwind dark variant configuration

**Solution**: 
1. Enable dark mode in your operating system settings
2. Refresh the browser page (Ctrl+F5)
3. Verify the dark variant is configured in your CSS

### Theme doesn't persist

**Cause**: JavaScript errors preventing localStorage writes.

**Solution**: Check browser console (F12) for errors, ensure Alpine.js loaded correctly.

### Alpine.js not loading

**Cause**: Build issues or incorrect import.

**Solution**: 
```bash
npm install alpinejs
npm run build
```

Check that `import Alpine from 'alpinejs'` is present in your `app.js`.

## Testing the Theme Switcher

1. Open your blog in the browser
2. Open DevTools Console (F12)
3. Click the theme buttons (☀️ 🌓 🌙)
4. You should see console logs:
   - `🔧 ThemeSwitch component initialized`
   - `🎯 Setting theme to: [mode]`
   - `✨ Applying theme: [mode] → [LIGHT/DARK]`

If you see these logs but the UI doesn't change → Tailwind dark variant configuration issue.

If you don't see any logs → Alpine.js not loading properly.

## Reference Implementation

See the stub files in `stubs/` directory:
- `app.js.stub` - Complete Alpine.js setup
- `app.css.stub` - Complete Tailwind CSS v4 configuration

## Support

If you encounter issues not covered here, please:
1. Clear all caches: `php artisan optimize:clear`
2. Clear browser cache and localStorage
3. Check the troubleshooting section in README.md
4. Open an issue on GitHub with browser console logs
