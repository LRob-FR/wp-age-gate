# LRob - Age Gate

A professional WordPress age verification plugin with pre-configured presets for alcohol, adult content, and age-restricted products.

## Features

- **Pre-configured Message Templates**: Industry-specific presets for:
  - Alcohol & spirits
  - Adult content
  - Tobacco products
  - Cannabis & CBD
  - Gambling & betting
  - And more...
- **Country-Specific Decline URLs**: Automatic redirection to appropriate resources based on user's country
- **Smart Auto-Detection**: Automatically selects message template and country based on WordPress locale
- **Fully Customizable**: Every field can be edited after loading a preset
- **Modern UI**: Clean, accessible modal with focus management and keyboard navigation
- **Theme Integration**: 
  - Auto mode: Follows WordPress theme colors
  - Light/Dark presets
  - Custom colors with full control
- **Live Preview**: See changes instantly in the admin settings
- **Privacy-Focused**: Cookie-based verification (configurable duration)
- **Bot-Friendly**: Automatically bypasses age gate for search engine crawlers
- **Translation-Ready**: Fully translated in 17 languages

## Prerequisites

- **WordPress Version**: 6.8+
- **PHP Version**: 8.2+

## Installation

1. Download the latest release ZIP
2. Upload to WordPress via Plugins → Add New → Upload Plugin
3. Activate the plugin
4. Configure via Settings → Age Gate

## Usage

### Quick Start with Presets

1. Navigate to **Settings → Age Gate**
2. Go to the **Content** tab
3. Select a **message template** (e.g., "Alcohol - Wine & Spirits")
4. Select your **country** for appropriate decline redirect
5. Click the **Load button** (↻ icon)
6. Customize the loaded content if needed
7. Click **Save Changes**

### Customization

#### General Settings
- **Enable/Disable**: Toggle age gate on/off
- **Cookie Duration**: How many days the verification is remembered (default: 30)

#### Content & Messages
- **Minimum Age**: Required age for access
- **Title**: Main heading displayed to users
- **Message**: Explanation text (supports HTML)
- **Legal Notice**: Small print disclaimer
- **Button Labels**: Customize accept/decline button text
- **Decline URL**: Where users are redirected when declining

*Tip: Use `{age}` placeholder in any text field to display the minimum age*

#### Appearance
- **Theme**: Auto (follows site), Light, Dark, or Custom
- **Border Radius**: Modal corner roundness (0-50px)
- **Backdrop Blur**: Background blur intensity (0-25px)
- **Custom Colors**: Full control over background, text, and button colors

#### Tools
- **Invalidate Cookies**: Force all users to verify again

### Testing

Always test in a **private/incognito browser window** to see the age gate as new visitors would.

## Development

### Building a Release

Requirements:
- PHP CLI
- WP-CLI
- gettext (msgfmt)
- zip

```bash
# Install dependencies (Fedora/RHEL)
sudo dnf install php-cli wp-cli gettext zip

# Build release
cd lrob-age-gate/
./release.sh
```

The script will:
1. Generate translation strings from JSON presets
2. Create translation template (.pot)
3. Compile translations (.po → .mo)
4. Create a clean ZIP in `../releases/`

### Translation

The plugin includes professional translations in **17 languages**:

- **Bulgarian** (bg_BG)
- **Czech** (cs_CZ)
- **Danish** (da_DK)
- **German** (de_DE)
- **Greek** (el_GR)
- **Spanish** (es_ES)
- **Finnish** (fi_FI)
- **French** (fr_FR)
- **Croatian** (hr_HR)
- **Hungarian** (hu_HU)
- **Italian** (it_IT)
- **Norwegian** (nb_NO)
- **Dutch** (nl_NL)
- **Polish** (pl_PL)
- **Portuguese** (pt_PT)
- **Romanian** (ro_RO)
- **Swedish** (sv_SE)

**Adding a new language:**

```bash
# Create translation file
cp languages/lrob-age-gate-fr_FR.po languages/lrob-age-gate-ja_JP.po

# Edit and translate
nano languages/lrob-age-gate-ja_JP.po

# Build release (automatically compiles all .po files)
./release.sh
```

### Message Presets

Message templates are stored as JSON files in `/messages/` directory:

```json
{
  "message_code": "wine_spirits",
  "message_label": "Alcohol - Wine & Spirits",
  "min_age": 18,
  "title": "Age Verification Required",
  "message": "You must be {age} years or older to access this site.",
  "legal": "By entering, you confirm you are of legal drinking age."
}
```

Decline URLs are configured in `/messages/decline_urls.json`:

```json
[
  {
    "country_code": "FR",
    "message_code": "wine_spirits",
    "decline_url": "https://example.com/age-restriction-info"
  }
]
```

## Technical Details

- **Text Domain**: `lrob-age-gate`
- **Cookie Name**: `lrob_age_verified`
- **Option Name**: `lrob_age_gate_options`
- **Classes**: `LRob_AgeGate_Admin`, `LRob_AgeGate_Frontend`
- **Functions**: Prefixed with `lrob_agegate_`
- **Constants**: Prefixed with `LROB_AGEGATE_`

### Security Features

- Nonce verification on all admin actions
- Input sanitization with WordPress core functions
- XSS protection with proper escaping
- CSRF protection on cookie invalidation
- Bot detection to prevent SEO impact

### Accessibility

- ARIA labels and roles
- Keyboard navigation (Tab, Shift+Tab)
- Focus trap within modal
- ESC key disabled (age verification required)
- High contrast support

## Legal Notice

⚠️ **Important**: Laws regarding age verification vary by jurisdiction and change over time. You are responsible for ensuring compliance with your local regulations. The included presets are templates only and may not meet all legal requirements in your area. Always consult legal counsel and adapt content to your specific needs.

## Support

For support, please [open an issue](https://github.com/LRob-FR/wp-age-gate/issues) or [contact LRob directly](https://www.lrob.fr/contact/)

## Credits

**Developed with ❤️ by [LRob, WordPress specialist](https://www.lrob.fr/)**

## License

LRob - Age Gate  
Copyright (c) 2025 LRob

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <https://www.gnu.org/licenses/>.

For more details, see [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html).

## Changelog

### 1.0.0 - Initial Release
- Age verification modal with cookie-based memory
- Pre-configured message templates for various industries
- Country-specific decline URL redirection
- Auto-detection of language/country from WordPress locale
- Theme integration (Auto/Light/Dark/Custom)
- Live preview in admin panel
- Bot detection for SEO protection
- Accessibility features (ARIA, keyboard nav, focus management)
- Translations in 17 languages
- Customizable appearance (colors, blur, border radius)
- Cookie duration control
- Admin tools for testing (cookie invalidation)
