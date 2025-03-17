#!/usr/bin/env bash

# Script to setup git hooks manually in case npm/husky setup fails

echo "Setting up git hooks..."

HOOK_DIR=".git/hooks"
HUSKY_DIR=".husky"

# Create hooks directory if it doesn't exist
mkdir -p "$HOOK_DIR"

# Check if Husky pre-commit hook exists
if [ -f "$HUSKY_DIR/pre-commit" ]; then
    echo "Found Husky pre-commit hook, copying to Git hooks directory..."
    cp "$HUSKY_DIR/pre-commit" "$HOOK_DIR/pre-commit"
    chmod +x "$HOOK_DIR/pre-commit"
    echo "✅ Pre-commit hook installed successfully."
else
    echo "⚠️ Could not find Husky pre-commit hook. Please run 'npm install' first."
    exit 1
fi

echo "Git hooks setup complete."