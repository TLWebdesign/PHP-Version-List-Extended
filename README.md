# PHP Version List Extended plugin for DirectAdmin

This plugin provides a clean, Bootstrap-powered overview of all PHP versions used across domains and subdomains on a DirectAdmin server.  
It shows:

* A summary table with total counts and usage percentages
* A detailed breakdown per (sub)domain
* Direct links to user accounts
* Full reseller scoping support

This plugin is a modernised fork of the original **PHP Version List Extended** (v1.8.0) by **RealityHost**.  
Original source: https://bitbucket.org/wavoe/phpversionlist/src/master/

This fork is not affiliated with or endorsed by the original author.

---

## Acknowledgements

Special thanks to **RealityHost** for creating the original plugin from which this work is derived.  
This version includes substantial rewrites and enhancements to support modern DirectAdmin versions, dynamic PHP version discovery, and improved UI/UX.

---

## Changelog

### Version 1.0.2
* Added Sorting and Search (column|row) options to the domains table
* Fixed subdomain detection
* Improved layout to always be 100% width.

### Version 1.0.1
* Added reseller column to admin view.
* Added 15 min caching to the API call for the system info (was slowing the page load down by 0.8 sec)

### Version 1.0.0 — Initial Release (Fork)
* Removed second PHP version selector (deprecated in current DA versions)
* Added subdomain detection and inheritance logic
* Removed hard-coded maximum PHP version logic — now fully dynamic
* Added reseller view (shows own account + created users)
* Extended statistics table with percentage column
* Added direct link to open user account from within plugin
* Improved visual hierarchy and Bootstrap integration
* Numerous internal cleanups and code modernisations

---

## Requirements

* DirectAdmin 1.648 or higher
* DirectAdmin over HTTPS (httpsocket uses SSL; port does not matter)
* PHP 7.4 and above
* CustomBuild 2.0 (for PHP version installation)

---

## Installation

Log in as **Admin** in DirectAdmin and open the **Plugin Manager**.  
Click **Add**, then enter the package URL:

```
https://raw.githubusercontent.com/TLWebdesign/PHP-Version-List-Extended/refs/heads/master/php_version_list_extended.tar.gz
```

Fill in the required fields and install.

---

## Creating the plugin package

Ensure all files in the `scripts` directory use **LF** line endings.

```bash
tar czvf php_version_list_extended.tar.gz \
    --exclude='.*' \
    --exclude='version.html' \
    --exclude='php_version_list_extended.tar.gz' \
    *
```