# PHP version list plugin for DA
This is a plugin written for DirectAdmin to list all the used PHP versions by the users.\
The interface is using Bootstrap.

It will show a summary of the used PHP version by # of domains.\
And a detailed overview of which PHP version(s) are used per domain.

## Requirements
* DirectAdmin 1.59.5 and up
* PHP 5.6 and up
* PHP function shell_exec should not be disabled (under the running the user)
* Custombuild 2.0 (for the different PHP versions installation)
* DirectAdmin via https (httpsocket is working via ssl://...)
* Max 4 different installed PHP versions, via Custombuild 2.0