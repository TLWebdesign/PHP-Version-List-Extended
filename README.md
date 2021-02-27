# PHP version list plugin for DA
This is a plugin written for DirectAdmin to list all the used PHP versions by the users.  
The interface is using Bootstrap.

It will show a summary of the used PHP version by # of domains.  
And a detailed overview of which PHP version(s) are used per domain.

## Requirements
* DirectAdmin 1.61.5 and up
* PHP 5.6 and up
* Custombuild 2.0 (for the different PHP versions installation)
* DirectAdmin via https (httpsocket is working via ssl://...)
* Max 4 different installed PHP versions, via Custombuild 2.0

## Installation
Log in as an admin on DirectAdmin and go to the Plugin Manager page.  
Click the add button and paste the url of the plugin package: https://wavoe.bitbucket.io/phpversionlist/phpversionlist.tar.gz  
Fill the other needed fields and choose if you want to install directly after uploading.

## Use of shell_exec (info)
In the past it was required that the `shell_exec` function was not in the list of `disable_functions` in your global php.ini file.  
The `shell_exec` function is used to read out the users configuration file to see which php version is configured per domain.  
Now the page using the `shell_exec` function is loading a php.ini file which is included in this plugin to be sure it's able to run.  
Credits for this tip goes to the DirectAdmin user zEitEr and to the Bitbucket user Kyle Adams for the fork and implementation.
