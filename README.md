# PHP version list plugin for DA
This is a plugin written for DirectAdmin to list all the used PHP versions by the users.\
The interface is using Bootstrap.

## Requirements
* DirectAdmin 1.57 and up
* PHP 5.6 and up
* PHP function shell_exec should not be disabled (under the running the user)
* Custombuild 2.0 (for the different PHP versions installation)
* DirectAdmin via https (httpsocket is working via ssl://...)
* Max 4 different installed PHP versions, via Custombuild 2.0