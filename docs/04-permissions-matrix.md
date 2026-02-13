# Permissions Matrix (Draft)

## Access Levels
- Guest: brief summaries only.
- Logged-in: full details and contact visibility.

## Role Capabilities
| Capability | Guest | Buyer | Supplier (Enterprise) | Supplier (Private) | Professional | Admin |
|---|---|---|---|---|---|---|
| View summaries | Yes | Yes | Yes | Yes | Yes | Yes |
| View full details | No | Yes | Yes | Yes | Yes | Yes |
| View supplier identity/contact | No | Yes | Yes | Yes | Yes | Yes |
| Create RFQ | No | Yes | No | No | No | Yes |
| Create Offer | No | No | Yes | Yes | Yes | Yes |
| Respond to RFQ | No | No | Yes | Yes | Yes | Yes |
| Edit own profile | No | Yes | Yes | Yes | Yes | Yes |
| Manage others | No | No | No | No | No | Yes |

## Notes
- No admin intervention in buyer-supplier interactions.
- No payments or commissions.
- Implementation status:
  - Roles created in plugin: `olive_supplier_enterprise`, `olive_supplier_private`, `olive_professional`, `olive_buyer`.
  - Offer creation capability: `create_olive_offers` granted to supplier/professional roles + admin.
  - RFQ creation capability: `create_olive_rfqs` granted to buyer role + admin.
  - Guest gating on single Supplier/Offer/RFQ pages: summary only + login prompt.
  - Frontend shortcodes for submissions: `[oliveb2b_offer_form]`, `[oliveb2b_rfq_form]`.
