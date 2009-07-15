#!/bin/bash

# This script creates symlinks from the addon files and directories to the appropriate locations in the EE installation.
# It enables us to keep the addon in a single folder in the root of the site, and manage it as a separate git submodule.

addon_dir_path=`pwd`

echo "Enter the path to your ExpressionEngine installation, without a trailing slash (e.g. /var/www/html/mysite.com), and press ENTER:"
read ee_path
echo "Enter your 'system' folder name, and press ENTER:"
read ee_system_folder

# Delete any existing symlinks.
rm "$ee_path"/"$ee_system_folder"/extensions/ext.sl_comment_redirect.php
rm "$ee_path"/"$ee_system_folder"/language/english/lang.sl_comment_redirect.php

# Create the symlinks.
ln -s "$addon_dir_path"/system/extensions/ext.sl_comment_redirect.php "$ee_path"/"$ee_system_folder"/extensions/ext.sl_comment_redirect.php
ln -s "$addon_dir_path"/system/language/english/lang.sl_comment_redirect.php "$ee_path"/"$ee_system_folder"/language/english/lang.sl_comment_redirect.php