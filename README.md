# OliveB2B

WordPress project for the OliveB2B B2B marketplace.

## Local Setup
- Docker-based environment with one-command start for WordPress + DB.
- WP admin reachable locally.
- Permalinks configured automatically.
- GeneratePress parent + child theme activatable after install.

## How To Run
1. Start containers: `docker compose up -d`
2. Open the site: `http://localhost:8080`
3. Open admin: `http://localhost:8080/wp-admin`

## Default Local Admin (from `.env`)
- Username: `admin`
- Password: `admin12345`
- Email: `admin@example.com`

## Configuration
- Create `.env` from `.env.example`, then adjust ports or admin credentials as needed.

## Seed Data
1. Start containers: `docker compose up -d`
2. Seed sample marketplace data:
   - `docker compose run --rm --user 0:0 --entrypoint wp wpcli oliveb2b seed --allow-root --path=/var/www/html`
3. Reset and reseed:
   - `docker compose run --rm --user 0:0 --entrypoint wp wpcli oliveb2b seed --reset --allow-root --path=/var/www/html`

## Repo Structure
- `docs/` reference documentation
- `wp-content/themes/generatepress-child/` child theme
- `wp-content/plugins/oliveb2b-core/` optional core plugin (project-specific)
