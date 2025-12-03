# PHP Version List Extended plugin for DA

This is a plugin written for DirectAdmin to list all the used PHP versions by the users.  
The interface is using Bootstrap 5.x.

It will show a summary of the used PHP version by # of domains and the total.  
And a detailed overview of which PHP version(s) are used per (sub)domain.

This version is a fork from the original php version list extended plugin version 1.8.0 by RealityHost. You can find the original plugin at: https://bitbucket.org/wavoe/phpversionlist/src/master/

---

## Changelog

### Version 1.0.0 (intial release)
* Removed second php version as this is not used in DA anymore. 
* Added subdomains to the list
* Removed the maximum php version logic. It is now dynamic and will show the installed php versions.
* Added reseller view where a reseller is able to see the domains for the users he created and his own account.
* Extended the stats table with % column
* Added link to open user account directly from plugin.

---

## Requirements

* DirectAdmin 1.648 and up
* DirectAdmin via https (httpsocket is working via ssl://...) (used port doesn't matter)
* PHP 7.4 and up
* Custombuild 2.0 (for the different PHP versions installation)

## Installation

Log in as an admin on DirectAdmin and go to the Plugin Manager page.  
Click the add button and paste the url of the plugin package: https://raw.githubusercontent.com/TLWebdesign/PHP-Version-List-Extended/refs/heads/master/php_version_list_extended.tar.gz  
Fill the other needed fields and choose if you want to install directly after uploading.

---

#### Create the plugin package

Line separator of each file in the scripts directory should be LF before creating the tar file.

```
> project root folder
> tar czvf php_version_list_extended.tar.gz --exclude='.*' --exclude='version.html' --exclude='php_version_list_extended.tar.gz' *
```
