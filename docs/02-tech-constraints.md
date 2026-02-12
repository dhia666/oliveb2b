# Tech Constraints

## Fixed Stack (Non-Negotiable)
- WordPress
- Theme: GeneratePress (free) + child theme only
- Editor: Gutenberg only (no page builders)
- Search: Relevanssi (free)
- SEO: Rank Math (free)
- Cache: WP Fastest Cache
- Security: WP Cerber
- No subscription/annual-license plugins

## Plugin Inventory (Approved Only)
- GeneratePress (free): version TBD (install and record)
- Relevanssi (free): 4.26.0
  - Reason: Better on-site search and relevance control.
- Rank Math (free): 1.0.263
  - Reason: SEO metadata, schema, and sitemap generation.
- WP Fastest Cache: 1.4.6
  - Reason: Simple page caching for performance.
- WP Cerber: 9.6.11
  - Reason: Security, login protection, and hardening.
  - Source: Installed from vendor ZIP (plugin closed on wordpress.org).
- Polylang (free): 3.7.7
  - Reason: Multilingual management with free/non-subscription option.

## Multilingual
- Language switcher UI is implemented in `oliveb2b-core` with 22 languages (Arabic RTL included).
- Language URLs are wired to Polylang when active; fallback uses `/[lang]/` paths.

## Local Dev
- Docker-based local environment preferred.
- One-command start for WordPress + DB.

## Notes
- Dhia 5-2-206
