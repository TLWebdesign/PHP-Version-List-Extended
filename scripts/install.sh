#!/bin/sh

# Canonical plugin directory name under /usr/local/directadmin/plugins
TARGET_NAME="php_version_list_extended"

echo "Plugin install script running..."

# Normalise DOCUMENT_ROOT
DOCROOT=${DOCUMENT_ROOT%/}

# Hard-coded DirectAdmin plugins root
PLUGINS_ROOT="/usr/local/directadmin/plugins"

# DOCUMENT_ROOT is typically .../plugins/<folder>/admin or /reseller or /user
# Get the current plugin folder name from the directory above DOCUMENT_ROOT
CURRENT_NAME=$(basename "$(dirname "$DOCROOT")")
PLUGIN_DIR="$PLUGINS_ROOT/$CURRENT_NAME"
TARGET_DIR="$PLUGINS_ROOT/$TARGET_NAME"

echo "Detected plugin directory: $PLUGIN_DIR (name: $CURRENT_NAME)"

# If the current folder name is not the canonical one, safely remove the incorrect install folder and abort
if [ "$CURRENT_NAME" != "$TARGET_NAME" ]; then
    echo "ERROR: Incorrect plugin filename detected."
    echo "Expected folder name: '$TARGET_NAME' but got '$CURRENT_NAME'."
    echo "DirectAdmin uses the archive filename to determine the folder name."

    echo "Performing safety checks before removal..."

    # SAFETY CHECK 1: PLUGIN_DIR must start with PLUGINS_ROOT
    case "$PLUGIN_DIR" in
        "$PLUGINS_ROOT"/*) ;;
        *)
            echo "ABORT: Safety check failed — plugin directory is outside the plugins root."
            echo "Refusing to delete: $PLUGIN_DIR"
            exit 1
            ;;
    esac

    # SAFETY CHECK 2: Prevent deletion if PLUGIN_DIR == PLUGINS_ROOT
    if [ "$PLUGIN_DIR" = "$PLUGINS_ROOT" ]; then
        echo "ABORT: Safety check failed — resolved folder equals the entire plugins directory."
        echo "Refusing to delete: $PLUGIN_DIR"
        exit 1
    fi

    echo "Safe to remove. Deleting incorrectly named installation directory: $PLUGIN_DIR"
    rm -rf "$PLUGIN_DIR"

    echo "Please rename your archive to '$TARGET_NAME.tar.gz' and install again."
    exit 1
fi

# Ensure correct ownership and permissions on the final plugin directory
chown -R diradmin:diradmin "$PLUGIN_DIR"
chmod -R 755 "$PLUGIN_DIR"

echo "Plugin installation completed for directory: $PLUGIN_DIR"

exit 0