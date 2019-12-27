#!/bin/sh

echo "Plugin Installed!";

cd $DOCUMENT_ROOT; //this directory
cd ../..
chown -R diradmin:diradmin phpversionlist

cd phpversionlist
chmod -R 755 *

exit 0;