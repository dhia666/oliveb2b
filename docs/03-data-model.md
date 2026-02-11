# Data Model (Draft)

## Core Entities (CPTs)
- Supplier
  - Represents Supplier (Enterprise/Private) and Professional profiles.
- Offer
  - Listings published by suppliers/professionals.
- RFQ
  - Requests for quotation created by buyers.

## Taxonomies
- Country
- Region/City
- Industry / Category
- Supplier Type (Enterprise, Private, Professional)
- Employees (size ranges)
- Verification Status
- Languages

## Key Fields (Custom Fields)
- Supplier
  - Legal name, display name
  - Summary, description
  - Contact email/phone (gated)
  - Address, geo coordinates (for near-me radius)
  - Verification flag, verification date
  - Employees range
  - Website URL
- Offer
  - Title, summary, description
  - Associated Supplier (relationship)
  - Category/Industry
  - Countries served
  - Availability/lead time
- RFQ
  - Title, summary, description
  - Buyer (author)
  - Category/Industry
  - Country/region
  - Budget range (optional)
  - Deadline

## Search/Filters Alignment
- Filters will use Country, near-me radius (geo), Supplier Type, Employees, Verified, Industry.
- Results split into tabs with counts: Offers vs Suppliers.
