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

## Frontend Submission
- Landing page (home): `http://localhost:8080/`
- Search page: `http://localhost:8080/marketplace-search/`
- Submit page: `http://localhost:8080/marketplace-submit/`
- My submissions page: `http://localhost:8080/marketplace-my-submissions/`
- Inbox page: `http://localhost:8080/marketplace-inbox/`
- Gutenberg shortcodes:
  - `[oliveb2b_offer_form]`
  - `[oliveb2b_rfq_form]`
  - `[oliveb2b_my_submissions]`
  - `[oliveb2b_my_inbox]`
  - `[oliveb2b_landing]`

## Logged-In Header Links
- Quick links are rendered for logged-in users in the header.
- Links are role/capability-based (`Search`, `Submit RFQ`, `Submit Offer`, `My Submissions`).
- Landing page v2 sections are powered by `[oliveb2b_landing]` and styled in plugin CSS.

## Direct Interaction
- Logged-in users can interact directly on single listing pages:
  - Respond to RFQs (supplier/professional capability)
  - Contact suppliers/offers (buyer or supplier/professional capability)
- Messages are emailed directly to listing owner and logged as private `olive_interaction` records.

## Repo Structure
- `docs/` reference documentation
- `wp-content/themes/generatepress-child/` child theme
- `wp-content/plugins/oliveb2b-core/` optional core plugin (project-specific)
