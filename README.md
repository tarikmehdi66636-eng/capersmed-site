# StackRecipes — Case Studies / Architecture Teardowns

WordPress drop-in that adds the `/case-studies/` section to **stackrecipes.com**, plus the first
published teardown.

Everything lives in `stackrecipes-case-studies/`, packaged as a plugin rather than as loose theme
files so it survives theme updates. Every template is still overridable from the theme (see below).

---

## What it adds

| Piece | Where |
| --- | --- |
| `case_study` post type, archived at `/case-studies/` | `includes/class-cpt.php` |
| `case_study_topic` taxonomy, at `/case-studies/topic/<term>/` | `includes/class-cpt.php` |
| "At a glance" meta panel (vertical, baseline cost, outcome, cutover window…) | `includes/class-meta.php` |
| Archive template — intro, topic filter, teardown cards | `templates/archive-case_study.php` |
| Single template — breadcrumb, at-a-glance panel, prose, related | `templates/single-case_study.php` |
| `/retail/` CTA banner + `[case_study_cta]` shortcode | `includes/class-cta.php`, `templates/parts/cta-retail.php` |
| Section stylesheet (loaded only on this section) | `assets/css/case-studies.css` |
| Shipped teardowns, published on activation | `content/*.html` |

### Published teardowns

**`/case-studies/independent-wine-merchant-lightspeed-migration/`**
*Case Breakdown: Migrating a Specialty Wine & Spirits Merchant Off Lightspeed* — the baseline
(2 registers, 1,800 SKUs, £168/month), the hardware audit (keep the Star TSP143 printers, replace
the rented readers with Stripe WisePOS E), UK VAT split across one receipt, the 48-hour cutover,
and the three-year P&L table.

**`/case-studies/apparel-boutique-shopify-pos-pro-migration/`**
*Case Breakdown: Migrating an Apparel Boutique off Shopify POS Pro to Single-Database
Omnichannel* — the stacked subscription baseline ($105 + $89 + $65 = $259/month), why webhook
sync double-sold the same dress, the move to Odoo 18 Community on a private VPS, untangling 3,400
variant SKUs (42 duplicate barcodes included), the hardware audit that kept 75% of the counter,
the Sunday-to-Tuesday cutover, and the three-year comparison ($9,324 vs $1,629).

---

## Install

1. Copy `stackrecipes-case-studies/` into `wp-content/plugins/`.
2. Activate **StackRecipes — Case Studies / Architecture Teardowns**.
3. Visit **Settings → Permalinks** once if `/case-studies/` 404s. (Activation flushes rewrites; this
   is only needed when the files are deployed by copy without an activation cycle.)

Activation publishes any shipped teardown that has no post at its slug. It never overwrites an
existing post, so re-activating is safe.

### Publishing teardowns added after activation

The activation hook only fires on activation, so a plugin **updated in place** never publishes
teardowns added since. After deploying an update that ships a new teardown:

```bash
sudo -u www-data HOME=/tmp wp stackrecipes seed
```

It publishes only what is missing and reports what it skipped. Deactivating and reactivating the
plugin does the same thing from the admin screens.

### Serving the section from `/blueprints/` instead

Edit one constant in `stackrecipes-case-studies.php`:

```php
define( 'SRCS_SLUG', 'blueprints' );
```

then re-save permalinks. The archive, the single URLs and the taxonomy all follow.

### Deploying to a live server

The four steps are scripted in `bin/deploy-stackrecipes.sh` — run it from the
repo root:

```bash
sudo ./bin/deploy-stackrecipes.sh
```

Paths are overridable if your layout differs:

```bash
SRC=/srv/capersmed-site/stackrecipes-case-studies \
WP=/var/www/stackrecipes.com \
FPM=php8.3-fpm \
sudo -E ./bin/deploy-stackrecipes.sh
```

It refuses to run if the plugin or the WordPress install is not where it
expects, rather than half-deploying.

Or run the same steps by hand:

```bash
# Copy in. On a re-deploy prefer rsync, so files removed from the plugin are
# removed from the server too — cp -r merges and leaves stale files behind.
sudo rsync -a --delete /path/to/capersmed-site/stackrecipes-case-studies/ \
  /var/www/stackrecipes.com/wp-content/plugins/stackrecipes-case-studies/

sudo chown -R www-data:www-data \
  /var/www/stackrecipes.com/wp-content/plugins/stackrecipes-case-studies

cd /var/www/stackrecipes.com

# HOME is set because WP-CLI as www-data otherwise warns about ~/.wp-cli/cache.
sudo -u www-data HOME=/tmp wp plugin activate stackrecipes-case-studies
sudo -u www-data HOME=/tmp wp rewrite flush

sudo systemctl reload php8.3-fpm   # purge OPcache for the web SAPI

# On an update that ships a new teardown, activation does not re-run:
sudo -u www-data HOME=/tmp wp stackrecipes seed
```

Activation already flushes rewrites, so `wp rewrite flush` is belt and braces
rather than required.

**Check the permalink structure first.** On plain permalinks (`?p=123`) the
section 404s no matter how often you flush:

```bash
sudo -u www-data HOME=/tmp wp rewrite structure   # must not be empty
```

Then verify the deploy landed:

```bash
# The teardown exists and is published
sudo -u www-data HOME=/tmp wp post list --post_type=case_study \
  --fields=ID,post_status,post_name

# The permalink resolves
curl -sI https://stackrecipes.com/case-studies/ | head -1
curl -sI https://stackrecipes.com/case-studies/independent-wine-merchant-lightspeed-migration/ | head -1
```

Both should return `200`. If the archive 200s but the teardown 404s, the post
did not seed — re-run activation, or create it in the editor and paste
`content/independent-wine-merchant-lightspeed-migration.html` into a code block.

---

## Theming

The plugin templates are a fallback. They step aside for the theme in two ways:

1. **Standard hierarchy** — a `single-case_study.php` or `archive-case_study.php` in the theme wins
   outright.
2. **Partial override** — copy any file from `templates/` into
   `your-theme/stackrecipes/case-studies/` and edit it there:

   ```
   your-theme/stackrecipes/case-studies/archive-case_study.php
   your-theme/stackrecipes/case-studies/single-case_study.php
   your-theme/stackrecipes/case-studies/parts/cta-retail.php
   ```

### Typography and colour

`assets/css/case-studies.css` inherits the theme's font stack and only sets rhythm, measure and
contrast. Retune the whole section by overriding the tokens in the theme stylesheet:

```css
.srcs {
	--srcs-ink: #14181d;
	--srcs-muted: #5b6672;
	--srcs-line: #e3e7ec;
	--srcs-accent: #0f4c81;
	--srcs-measure: 42rem;   /* reading column */
	--srcs-wide: 68rem;      /* tables + timeline break out to this */
}
```

Prose sits in a two-column grid: normal content holds the reading measure, and anything with
`class="srcs-wide"` (the P&L table, the cutover timeline) spans the full container, flush left.

### Content classes

Teardown bodies use a small vocabulary:

| Class | Use |
| --- | --- |
| `srcs-lede` | Opening paragraph |
| `srcs-note` | Caveat callout — first `<strong>` becomes the heading line |
| `srcs-timeline` on `<ol>` | Cutover steps; each `<li>` opens with `<span class="srcs-timeline__when">` |
| `srcs-table-wrap` + `srcs-table` | Scrollable comparison tables; `is-total` on a row, `srcs-pos` / `srcs-neg` on a cell |
| `srcs-table--text` | Text tables (hardware audits) — keeps every column left-aligned |
| `<small>` inside a cell | Muted qualifier under a figure |
| `srcs-wide` | Break the block out of the reading measure |

---

## CTA

The `/retail/` banner renders automatically at the foot of the archive and of every teardown.

### Per-teardown copy

Use the **CTA banner** box in the sidebar of the post editor (heading, body, button label, button
URL). Blank fields fall back to the site-wide copy. This is the right place for teardown-specific
wording — it keeps the banner out of the post body, where `wpautop` mangles multi-line shortcode
attributes and the banner would render twice.

### Shortcode

For placing the banner elsewhere — a landing page, a sidebar:

```
[case_study_cta]
[case_study_cta title="Running an apparel boutique on Shopify POS Pro?" button_url="/retail/" button_label="Explore turnkey omnichannel setup"]
```

Canonical attributes are `heading`, `body`, `primary_label`, `primary_url`, `secondary_label`,
`secondary_url`. These aliases are also accepted, because they are what people reach for:

| Alias | Maps to |
| --- | --- |
| `title` | `heading` |
| `subtitle`, `text` | `body` |
| `button_label`, `cta_label` | `primary_label` |
| `button_url`, `cta_url` | `primary_url` |

Naming a primary button without a secondary one renders a single button, rather than letting the
default second button tag along.

If a teardown body does contain the shortcode, the template's automatic banner is suppressed for
that post so only one renders.

### Site-wide copy

```php
add_filter( 'srcs_cta_defaults', function ( $defaults ) {
	$defaults['primary_url'] = home_url( '/retail/hardware-check/' );
	return $defaults;
} );
```

---

## Adding the next teardown

Either write it in the editor, or ship it with the plugin:

1. Drop the body HTML in `content/<slug>.html`.
2. Add an entry to `SRCS_Seeder::entries()` with the slug, title, excerpt, topics and meta.

The seeder skips any slug that already exists, so shipped teardowns and hand-written ones coexist.

---

## Editorial conventions in the first teardown

Worth keeping for the ones that follow:

- **Every claim is costed or caveated.** The P&L table names what it excludes (card processing
  fees, hardware capex) rather than quietly improving the answer.
- **Currency is stated, not blended.** The `$399` setup fee is shown in dollars and converted at a
  named indicative rate.
- **The closing section says who should not do this.** A teardown that never disqualifies anyone
  reads as a sales page.
