# PHP version list plugin for DA
This is a plugin written for DirectAdmin to list all the used PHP versions by the users.  
The interface is using Bootstrap.

It will show a summary of the used PHP version by # of domains.  
And a detailed overview of which PHP version(s) are used per domain.

## Requirements
* DirectAdmin 1.60.0 and up
* PHP 5.6 and up
* PHP function shell_exec should not be disabled (under the running the user)
* Custombuild 2.0 (for the different PHP versions installation)
* DirectAdmin via https (httpsocket is working via ssl://...)
* Max 4 different installed PHP versions, via Custombuild 2.0

## Installation
Log in as an admin on DirectAdmin and go to the Plugin Manager page.  
Click the add button and paste the url of the plugin package: https://wavoe.bitbucket.io/phpversionlist/phpversionlist.tar.gz  
Fill the other needed fields and choose if you want to install directly after uploading.

## Use of shell_exec (info)
From previous experiences I got already the remark why `shell_exec` should be allowed.
This plugin is written in PHP and shell_exec is used to read out the users configuration file to see which php version is configured per domain.  
There is no other way to do this AFAIK.  
The script is running under the DirectAdmin user (default: `diradmin`), so worst case you can maybe give the right to this user only to use shell_exec.
