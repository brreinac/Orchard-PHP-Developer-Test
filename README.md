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
4. Enable the two custom modules:
5. Build your Main navigation (Structure → Menus) with **Root A/Root B** and children.
6. Place the **Product of the Day** block (Structure → Block layout).
7. Add products at **Manage → Products**, flag up to 5 as featured.

## Features

- **Banner Switcher**  
- Swaps the banner background under Root A vs Root B menu branches.
- **Product of the Day**  
- Admin CRUD for products.  
- Flag up to 5 featured.  
- Random featured product block.  
- Click‑tracking table & stub for weekly cron report.

## Database Settings

vendor/bin/drush site:install standard
--db-url=mysql://user:pass@127.0.0.1/dbname
--site-name="Orchard" --account-name=admin --account-pass=admin --yes