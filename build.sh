#!/bin/bash

# Configuration
PLUGIN_NAME="conference-manager"
VERSION=$(grep "Version:" conference-manager.php | awk '{print $NF}')
OUTPUT_FILE="${PLUGIN_NAME}-v${VERSION}.zip"

echo "📦 Packaging $PLUGIN_NAME version $VERSION..."

# Create a temporary directory for the build
TEMP_DIR="dist"
mkdir -p "$TEMP_DIR/$PLUGIN_NAME"

# List of files/folders to include
# Note: We exclude git files, docs, and build scripts themselves
rsync -av --progress . "$TEMP_DIR/$PLUGIN_NAME" --exclude ".git" --exclude ".gemini" --exclude "docs" --exclude "build.sh" --exclude "dist" --exclude "README.md"

# Navigate to dist and zip
cd "$TEMP_DIR"
zip -r "../$OUTPUT_FILE" "$PLUGIN_NAME"
cd ..

# Cleanup
rm -rf "$TEMP_DIR"

echo "✅ Build complete! Archive created: $OUTPUT_FILE"
chmod +x "$OUTPUT_FILE" 2>/dev/null
