#!/bin/sh

echo "Plugin has been updated!";

cd $DOCUMENT_ROOT; //this directory
cd ../..
chown -R diradmin:diradmin php_version_list_extended

cd php_version_list_extended
chmod -R 755 *

exit 0