# Football WordPress

Local WordPress setup for editing the `Football` theme with Docker.

## Start

```bash
docker compose up -d
```

Open:

- WordPress: http://localhost:8080
- WordPress admin: http://localhost:8080/wp-admin
- phpMyAdmin: http://localhost:8081

WordPress admin credentials are created during installation and are not stored in this repository.

Database credentials:

- Database: `wordpress`
- User: `wordpress`
- Password: `wordpress`
- Root password: `root`

## Theme editing

The local folder `themes/football` is mounted into WordPress as:

```text
/var/www/html/wp-content/themes/football
```

After WordPress installation, go to:

```text
Appearance -> Themes -> Football -> Activate
```

Any edits you make in `themes/football` are reflected in WordPress.

## Data plugin

The local folder `plugins/football-data` is mounted into WordPress as:

```text
/var/www/html/wp-content/plugins/football-data
```

The plugin is active and currently works in mock mode. It includes Carbon Fields for editable Russian meta fields.

Install plugin PHP dependencies after cloning:

```bash
cd plugins/football-data
composer install
```

Useful local endpoints:

- http://localhost:8080/wp-json/football-data/v1/status
- http://localhost:8080/wp-json/football-data/v1/mock/players
- http://localhost:8080/wp-json/football-data/v1/mock/fixtures
- http://localhost:8080/wp-json/football-data/v1/mock/bookmakers

## String translations

Polylang Pro is used for multilingual strings. Editable UI strings are registered in:

- `Football theme`
- `Football Data`

Open the WordPress admin area and go to:

```text
Languages -> String translations
```

If a new string does not appear immediately, open any admin page once after code changes. Polylang registers strings in the admin context.

## Planning

Project phases are documented in `phases/README.md`.

## Useful commands

```bash
docker compose up -d
docker compose logs -f wordpress
docker compose down
```

To remove WordPress and database data completely:

```bash
docker compose down -v
```
