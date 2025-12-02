#!/bin/bash

###############################################################################
# Quick PHP Installation for Ubuntu/Debian
# Run this if you need to install PHP quickly
###############################################################################

echo "Installing PHP and required extensions..."
echo ""

# Update package list
sudo apt update

# Install PHP and extensions
sudo apt install -y \
    php \
    php-cli \
    php-mysql \
    php-pdo \
    php-mbstring \
    php-xml \
    php-json \
    php-curl

# Verify installation
if command -v php &> /dev/null; then
    echo ""
    echo "✓ PHP installed successfully!"
    php -v
    echo ""
    echo "Installed extensions:"
    php -m | grep -E "(pdo_mysql|json|mbstring)"
    echo ""
    echo "You can now run: ./dev-setup.sh"
else
    echo "✗ Installation failed"
    exit 1
fi
