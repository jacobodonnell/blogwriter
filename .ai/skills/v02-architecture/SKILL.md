---
name: v02-architecture
description: V0.2 architecture design for subscribers, monetization, newsletters, IndieAuth, and pages.
---

# V0.2 Architecture — Subscribers & Monetization

This skill documents the full V0.2 design so sessions have context when implementing these features.

## Subscriber Model

- Dedicated `subscribers` table, separate from the single-admin `users` table
- `Subscriber` model with `domain` (nullable), `email` (nullable), `name`, `stripe_id`, etc.
- Uses Laravel Cashier's `Billable` trait for Stripe integration
- A subscriber can authenticate via IndieAuth (domain-based) or magic link (email-based)

## Auth Architecture

- **Admin auth** stays on Fortify with the default `web` guard — no changes
- **Subscriber auth** uses a custom `subscriber` guard with its own middleware
- **IndieAuth client flow** is the primary subscriber login method (sign in with your domain)
- **Magic link email login** is the fallback for subscribers without a domain (requires SMTP/transactional email provider)
- Guards are completely separate — admin and subscriber sessions don't interfere

## IndieAuth Server

BlogWriter acts as an IndieAuth server for the blog owner's identity:

- **Metadata endpoint** (`/.well-known/oauth-authorization-server`) — discovery document
- **Authorization endpoint** (`/indieauth/authorize`) — handles authorization requests
- **Token endpoint** (`/indieauth/token`) — issues and verifies access tokens

IndieAuth is also used as a **subscriber login method** — subscribers prove identity by authenticating with their own IndieAuth server (BlogWriter acts as a client in this flow).

## Stripe via Laravel Cashier

- Direct Stripe integration using Laravel Cashier (no intermediary like Lemon Squeezy)
- **Checkout Sessions** for subscription creation
- **Customer Portal** for subscription management (cancel, update payment, etc.)
- **Webhook sync** to keep local subscription status in sync with Stripe
- Blog owner configures their own Stripe keys in settings
- Subscription plans/prices defined in Stripe Dashboard, referenced by price ID

## Newsletter — Buttondown Integration

- First-party integration with Buttondown as the delivery channel
- API-based: BlogWriter sends content to Buttondown, which handles email delivery
- `NewsletterProvider` interface for future providers (Mailchimp, ConvertKit, etc.)
- Blog owner configures Buttondown API key in settings
- Newsletters can be triggered when publishing articles (opt-in per article)

## Content Gating — Visibility Enum

Content visibility levels:

```php
enum Visibility: string
{
    case Everyone = 'everyone';       // Public, no auth required
    case Subscribers = 'subscribers'; // Any authenticated subscriber
    case Paid = 'paid';              // Subscribers with active paid subscription
}
```

- Applies to articles, photos, and future content types
- Default visibility: `everyone` (backwards compatible)
- Gated content shows teaser/excerpt to non-subscribers with a call-to-action
- Feed items respect visibility — authenticated feeds serve premium content

## Authenticated Feeds

- Standard public feeds continue serving `everyone` content
- Authenticated feed URLs (token-based) include subscriber/paid content
- IndieAuth ticketing protocol for feed reader access to gated content

## Pages System

- Settings-based (not a full CMS page builder)
- Two initial pages: **Home** and **About**
- Home page layout choices: editorial (text-focused), photo-driven (grid)
- Content stored in settings or a simple `pages` table
- Blade templates for each layout option

## Phase Dependency Order

Implementation should follow this order due to dependencies:

1. **Subscriber model + auth guard** — foundation for everything else
2. **IndieAuth server** — needed for subscriber IndieAuth login
3. **IndieAuth subscriber login + magic links** — subscriber authentication
4. **Visibility enum + content gating** — requires subscriber auth
5. **Stripe/Cashier integration** — requires subscriber model with Billable
6. **Buttondown newsletter** — can be built independently but benefits from subscriber list
7. **Authenticated feeds** — requires subscriber auth + visibility
8. **Pages system** — independent, can be built at any point
9. **Webmentions** — independent, can be built at any point

## Activation Triggers

Activate this skill when:
- Implementing any V0.2 feature (subscribers, payments, newsletters, IndieAuth, pages)
- Working with the `Subscriber` model or subscriber auth
- Building content gating or visibility logic
- Integrating Stripe/Cashier or Buttondown
- Setting up IndieAuth server endpoints
- Working on authenticated feeds or ticketing
- Building the pages system
