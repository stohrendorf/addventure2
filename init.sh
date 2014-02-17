#!/bin/bash

if [ ! -e doctrine-bootstrap.php ]
then
  echo "Please edit \`doctrine-bootstrap.template.php' and rename it to \`doctrine-bootstrap.php',"
  echo "then execute this script again."
  exit 1
fi

if [ ! -e init.sql -o ! -e vendor ]
then
  echo "Downloading and installing dependencies..."
  ./composer.phar install
  echo "Generating proxy classes for database access..."
  php vendor/bin/doctrine orm:generate-proxies dao/proxies/
  echo "Creating SQL script..."
  php vendor/bin/doctrine orm:schema-tool:create --dump-sql > init.sql
  echo "Please use the generated \`init.sql' script to generate the database."
  exit 0
fi

echo "Nothing to be done."
