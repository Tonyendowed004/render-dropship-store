# Unirw CJ Dropshipping Store — PHP + MySQL starter

This is a security-conscious starter for a PHP/MySQL storefront designed for Render + Aiven and CJ Dropshipping API v2. It is **not yet a complete live-payment/automatic-fulfilment integration**: checkout deliberately saves orders as `pending` / `awaiting_payment`, and it does not submit unpaid orders to CJ. This avoids charging or shipping incorrectly.

## Included
- Customer registration/login, hashed passwords, session renewal, role checks, CSRF protection
- Store catalogue, search, cart, checkout form, customer order history
- Admin product entry with CJ product/variant IDs, supplier cost, retail price and visibility
- MySQL schema with order items, estimated gross profit and payment/fulfilment states
- CJ API token authentication and a generic API request helper
- Safe error handling (no raw supplier HTML dumped to customers)

## Deploy on Render + Aiven
1. Push the project folder to your Git repository and connect it to Render as a PHP web service, or use the included Dockerfile.
2. Run `schema.sql` once against your Aiven MySQL database using your preferred MySQL client. The app intentionally does not create tables on every storefront request.
3. Add Render environment variables: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, `CJ_API_KEY`, `STORE_NAME`, `STORE_CURRENCY`, and a long random `ADMIN_SETUP_KEY`. Add `CJ_PLATFORM_TOKEN` only if CJ supplied/required one. Configure Aiven TLS according to your Aiven connection settings and deployment environment.
4. Ensure PHP extensions `pdo_mysql` and `curl` are enabled. Do not commit real secrets or `.env` files.
5. Visit `/admin-setup.php`, enter the setup key and create the first admin. Immediately remove `ADMIN_SETUP_KEY` from Render after the admin is created. The setup page refuses creation if an admin already exists.
6. Sign in, add a product with the **actual CJ variant ID (`vid`)**, SKU, current supplier cost and retail price. Check the CJ variant/stock in your CJ account before publishing.
7. Test registration, cart, checkout, order history and admin on a staging deployment first.

## CJ API notes
- API token endpoint: `POST https://developers.cjdropshipping.com/api2.0/v1/authentication/getAccessToken` with JSON `{"apiKey":"..."}`.
- `CJ-Access-Token` authenticates API calls. CJ's order-create V2 docs also mention the optional `platformToken` header; configure it only if relevant to your account.
- Order creation requires real recipient country/city/province/address/contact fields and a valid `products` list with a real `vid` or SKU and quantity. Logistics and shipping origin must be selected from valid CJ account/API results.
- `cj_api.php` provides `cj_request()` but this starter does not yet call order creation. Do not call it until PayPal server-side capture/verification is implemented and tested, and you have chosen whether to create an order only (`payType=3`) or pay from CJ balance (`payType=2`). CJ documents that its old `cjPayUrl` is deprecated.
- Product query response schemas can vary by endpoint/filters. Implement product sync against the exact endpoint and response fields enabled for your CJ account; do not scrape the CJ website or send browser CSRF cookies to the API.

## Production checklist before taking real money
- Integrate PayPal Orders API server-side: create order, capture/verify with PayPal, verify amount/currency and unique order reference, and idempotently mark order paid.
- After verified payment only, create CJ order using the official endpoint and correct JSON field names; store CJ order ID and sanitized response. Add retry/idempotency handling so network retries cannot duplicate supplier orders.
- Select a valid CJ logistics service, shipping origin, variant, and stock for each destination. Add order status polling/webhooks and tracking updates.
- Recalculate retail totals from database only; never trust prices submitted by the browser. Add shipping/tax/refund policies, email notifications, rate limiting, password reset, admin audit logs and backups.
- Configure HTTPS, Aiven TLS, backups, privacy/terms/returns pages and production monitoring.

## Environment variables
See `.env.example`. Configure values in Render's Environment settings; `.env.example` is documentation only.
