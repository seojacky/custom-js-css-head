# Custom JS&CSS in Head

A lightweight WordPress plugin that allows you to add custom JavaScript and CSS code to the head section of individual posts and pages.

![WordPress](https://img.shields.io/badge/WordPress-4.0%2B-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.0%2B-purple.svg)
![License](https://img.shields.io/badge/License-GPL%20v2-green.svg)
![Version](https://img.shields.io/badge/Version-1.0-orange.svg)

## Description

Custom JS&CSS in Head is perfect for adding Schema.org markup, custom tracking codes, page-specific styles, or any other custom code that needs to be in the document head. The plugin provides a clean, intuitive interface directly in the WordPress post editor.

## Features

- ✅ Add custom JavaScript and CSS code to individual posts and pages
- ✅ Clean, intuitive meta box interface in the post editor
- ✅ Code is inserted into the `<head>` section with priority 11
- ✅ Works with all public post types
- ✅ Secure implementation with nonce verification
- ✅ Lightweight and performance-optimized
- ✅ No database bloat - uses standard WordPress meta fields

## Use Cases

- **Schema.org structured data markup** - Add JSON-LD structured data for better SEO
- **Custom tracking codes** - Google Analytics, Facebook Pixel, conversion tracking
- **Page-specific CSS styles** - Custom styles for individual pages
- **Custom meta tags** - Open Graph, Twitter Cards, and other meta information
- **Third-party integrations** - Chat widgets, analytics tools, marketing scripts
- **A/B testing scripts** - Page-specific testing code
- **Custom fonts loading** - Web font loading optimization

## Technical Details

- Uses WordPress meta boxes API for seamless integration
- Secure nonce verification for data saving
- Proper user capability checks (edit_post/edit_page)
- Clean code output without extra formatting
- Priority 11 execution in wp_head hook
- Follows WordPress coding standards
- Compatible with all public post types

## Code Example

The plugin adds a meta box to your post editor where you can insert code like:

```html
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "Your Article Title",
  "description": "Article description"
}
</script>
```

```css
<style>
.custom-page-style {
  background: linear-gradient(45deg, #ff6b6b, #4ecdc4);
}
</style>
```

## Requirements

- WordPress 4.0 or higher
- PHP 7.0 or higher

## Author

**seojacky**
- GitHub: [@seojacky](https://github.com/seojacky/)
- Plugin Repository: [custom-js-css-head](https://github.com/seojacky/custom-js-css-head/)

## License

This plugin is licensed under the GPL v2 or later.

```
Copyright (C) 2024 seojacky

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## Changelog

### 1.0
- Initial release
- Added meta box for custom JS/CSS input
- Implemented secure saving with nonce verification
- Added output functionality in head section with priority 11
- Support for all public post types
- Clean, monospace textarea for code input
- Proper user permission checks

## Support

If you find this plugin helpful, please consider:
- ⭐ Starring the repository
- 🐛 Reporting bugs via GitHub issues
- 💡 Suggesting new features
- 🤝 Contributing to the codebase

---

Made with ❤️ for the WordPress community
