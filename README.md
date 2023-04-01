# PHP version list plugin for DA

This is a plugin written for DirectAdmin to list all the used PHP versions by the users.  
The interface is using Bootstrap.

It will show a summary of the used PHP version by # of domains.  
And a detailed overview of which PHP version(s) are used per domain.

## Requirements

* DirectAdmin 1.648 and up
* DirectAdmin via https (httpsocket is working via ssl://...) (used port doesn't matter)
* PHP 7.4 and up
* Custombuild 2.0 (for the different PHP versions installation)
* Minimum 2 different installed PHP versions (else this plugin is not useful for you)
* Maximum 4 different installed PHP versions (via Custombuild 2.0)

## Installation

Log in as an admin on DirectAdmin and go to the Plugin Manager page.  
Click the add button and paste the url of the plugin package: https://wavoe.bitbucket.io/phpversionlist/phpversionlist.tar.gz  
Fill the other needed fields and choose if you want to install directly after uploading.

## HTTPSocket

Using [HTTPSocket communicating class](https://files.directadmin.com/services/all/httpsocket/) to communicate with the
DirectAdmin API.  
Version used: `3.0.4` + one customization done. See the curl CURLOPT_REFERER line.  
Modification done to avoid issues with the referer check in DA (The request was made without a referer header ...)  
If you use a login:passwd the issue will not be there but as we don't have the user password we have to work like this.

## Use of shell_exec (info)

In the past it was required that the `shell_exec` function was not in the list of `disable_functions` in your global php.ini file.  
The `shell_exec` function is used to read out the users configuration file to see which php version is configured per domain.  
Now the page using the `shell_exec` function is loading a php.ini file which is included in this plugin to be sure it's able to run.  
Credits for this tip goes to the DirectAdmin user zEitEr and to the Bitbucket user Kyle Adams for the fork and implementation.

## First PHP and Second PHP

In the past you could have 2 live versions (for example: file.php for 5.6, and file.php72 for 7.2 live on their site),
allow 2 at a time, where those 2 live versions can be chosen from any of the (max) 4 instances from the options.conf.  
This was possible when you had `php_version_selector=2` in your directadmin configuration.  
This is only supported for older skins. The Evolution skin will show following warning:
`php_version_selector=2 setting is not supported by the skin, please consider switching to php_version_selector=1 (default).`  
The expectation is that this feature will be removed in the future?

---

#### Create the plugin package

Line separator of each file in the scripts directory should be LF before creating the tar file.

```
> project root folder
> tar czvf phpversionlist.tar.gz --exclude='.idea' --exclude='.git' --exclude='phpversionlist.tar.gz' *
```
