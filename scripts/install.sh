#!/bin/sh

echo "Plugin Installed!";

cd $DOCUMENT_ROOT; //this directory
cd ..

chown -R diradmin:diradmin *
chmod -R 755 *

exit 0;