#!/bin/sh

echo "Plugin has been updated!";

cd $DOCUMENT_ROOT; //this directory
cd ../..
chown -R diradmin:diradmin php_version_list_extended

cd php_version_list_extended
chmod -R 755 *

exit 0
#!/bin/sh
set -e

TARGET_NAME="php_version_list_extended"
PLUGINS_ROOT="/usr/local/directadmin/plugins"

echo "Plugin update script running..."

DOCROOT=${DOCUMENT_ROOT%/}
CURRENT_NAME=$(basename "$(dirname "$DOCROOT")")
PLUGIN_DIR="$PLUGINS_ROOT/$CURRENT_NAME"
TARGET_DIR="$PLUGINS_ROOT/$TARGET_NAME"

echo "Detected plugin directory: $PLUGIN_DIR (name: $CURRENT_NAME)"

# Enforce correct folder name
if [ "$CURRENT_NAME" != "$TARGET_NAME" ]; then
    echo "ERROR: Plugin directory name is incorrect."
    echo "Expected: $TARGET_NAME"
    echo "Found: $CURRENT_NAME"
    echo "DirectAdmin determines the folder name from the archive filename."
    echo "Please uninstall the plugin, rename your archive to '$TARGET_NAME.tar.gz' and install again."
    exit 1
fi

# Apply ownership and permissions
chown -R diradmin:diradmin "$PLUGIN_DIR"
chmod -R 755 "$PLUGIN_DIR"

echo "Plugin update completed for directory: $PLUGIN_DIR"
exit 0