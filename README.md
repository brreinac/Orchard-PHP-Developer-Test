# Orchard PHP Assessment (Drupal 10)

## Environment
- Drupal 10.x
- PHP 8.1+
- MySQL 8 / MariaDB 11
- Drush (optional)

## Installation

1. Clone or copy into your webroot.
2. `composer install`
3. Create DB and install Drupal:

vendor/bin/drush site:install standard
--db-url=mysql://user:pass@127.0.0.1/dbname
--site-name="Orchard" --account-name=admin --account-pass=admin --yes