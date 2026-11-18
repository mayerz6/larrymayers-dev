# Larry Mayers — Consulting Roadmap & Positioning Handoff

Last updated: 2026-11-16

## Purpose

This is the business-strategy counterpart to `PROJECT_HANDOFF.md`. That file
explains how the portfolio platform is built; this one explains **what it's
for** — turning `larrymayers.site`, Scoremark, and ChairSignal into a working
pipeline for an independent Systems & Data Engineering consulting practice,
not just a set of portfolio pieces. Read this before:

- Writing new copy for the site aimed at a prospective client.
- Deciding whether a new mini-project is worth building.
- Having an actual sales conversation with a Barbados/Caribbean SME.

Companion docs: `Career_Projects.md` (resume-grounded project write-ups,
source material for the public Projects page) and each mini-project's own
`ARCHITECTURE_HANDOFF.md`/`handoff.md` (Scoremark, ChairSignal, `etl/`).

## The mistake this roadmap corrects

ChairSignal's first version led with an unproven claim: "here's an
operational insight your dental practice is missing." That's a hard sell —
a skeptical owner can't verify it, and in dentistry specifically, most
practice-management software already reports cancellation/no-show rates
natively, so the claim competes with something the owner may already have
for free. See ChairSignal's own `chairsignal_insights_mvp_handoff.md`
Section 2 ("counter-hypothesis") for where this was first flagged, and the
assessment that followed it for the fuller critique.

**The value proposition that replaces it: time and trust, not insight.**
A business owner doesn't need to be convinced they have a data problem —
they already know they do, or they already want a website. The job is
removing the time cost of fixing it, not selling an epiphany.

## Market grounding (Barbados)

Per the Central Bank of Barbados's quarterly economic reviews, growth is
consistently driven by tourism, wholesale & retail trade, business & other
services, and construction, with Deloitte flagging "limited manufacturing"
as a structural vulnerability
([Central Bank of Barbados](https://www.centralbank.org.bb/news/economic-reviews/economic-review-january-march-2026),
[Deloitte](https://www.deloitte.com/content/dam/assets-zone3/cbc/en/docs/services/tax/2025/bb-2025-budget-breakdown.pdf)).
That means the addressable market concentrates in hospitality, retail, and
local services — not the kind of transaction volume that makes
enterprise-scale categories (supply-chain shrinkage analytics, demand
forecasting at chain-retailer scale) a realistic opener for a solo
consultant here.

**A real local precedent already exists**: [Rico A.J. Lewis](https://www.ricoajlewis.com/),
Barbados/Trinidad-based, runs two tracks side by side — BI/analytics for
larger regional clients (Ernst & Young, Guardian Life, Prestige Holdings
are in his case list) and web design for small/"micro" businesses. The
website track is the trust-building door-opener; the analytics track is
sold separately, to clients who already trust him. This roadmap adopts
that same two-track shape, paired with the **tiered engagement structure**
[Borrowed BI](https://borrowedbi.com/) uses (a solo fractional-BI
consultant): a cheap/free diagnostic, then a fixed-scope fix, then a
retainer — never sold out of order.

The hotel/villa revenue-management niche was explicitly considered and
**ruled out** for now: it's a real, validated Caribbean category
([Prevail Hospitality](https://prevailhospitality.com/),
[Profit Guest Services](https://www.profitguestservices.com/)), but
Barbados already has full-service villa/property managers
([Barbados Villa Services](https://www.barbadosvillaservices.com/),
[Blue Sky Luxury](https://www.blueskyluxury.com/barbados/property-management/))
bundling pricing work into their existing fee, and it requires hospitality
domain vocabulary (ADR, RevPAR, pickup/pace) not yet demonstrated anywhere
in this portfolio. Revisit only if a specific owner-operated property
without an existing manager surfaces.

## The two-track service model

### Track A — Website builds (the door-opener)

A small business already understands why it wants a working, mobile-
friendly site — no persuasion needed. Fixed price, fast turnaround,
concrete before/after. Built on the same PHP/HTML/CSS/vanilla-JS stack as
`larrymayers.site` itself, so every client site is also a live
demonstration of the stack, not just a claim about it.

**Purpose beyond the fee**: this is how you get embedded in a business
long enough to see its *actual* operational pain firsthand, instead of
guessing at it from the outside the way ChairSignal's fictional dataset
had to.

### Track B — Fractional BI / reporting rescue (the upsell, offered later)

Modeled directly on Borrowed BI's three tiers. Never offered cold — always
after Track A has already built trust with that specific client.

| Tier | What it is | What it costs | What it proves |
|---|---|---|---|
| 1. Reporting Reality Check | Review the client's *actual* spreadsheets/exports/reports; hand back a written list of what's broken and a 30/60/90-day plan. | Free or near-free | The business's own mess, not a hypothesis on fictional data |
| 2. Dashboard Rescue / Reporting Automation | Fixed-scope project: fix the one report nobody trusts, or kill a recurring manual export/copy-paste ritual. | Fixed fee, scoped in writing | A real, permissioned outcome — the case study ChairSignal's roadmap always needed and never had |
| 3. Retainer | Ongoing fractional support, e.g. 10–15 hrs/month. | Monthly | Only offered once Tier 1 or 2 has already delivered something real for that client |

Scoremark's actual engine (completeness/validity/uniqueness/consistency
checks, ground-truth-tested) is the credible technical backbone for Tier 1
diagnostics — it's already built, tested, and deployed; it just needs to
be pointed at a real client's export instead of a synthetic sample.

## The phased roadmap

1. **Now – month 2:** Approach 3–5 real Barbados small businesses (retail,
   dental, services) with the **website offer specifically** — not a BI
   pitch. Use Scoremark/ChairSignal as "how I think about data" proof
   points if asked, not as the opening pitch.
2. **Month 2–3:** With a site live and a relationship established, offer
   the free Reporting Reality Check to those same clients as a natural
   next question ("want me to take a look at how you're tracking X too,
   now that I've seen your systems?").
3. **Month 3–4:** Convert one real diagnostic into a paid Tier 2 fix —
   the first real, permissioned case study.
4. **Month 4+:** Offer a retainer only where a client's own Tier 2 result
   justifies it. Replace any remaining fictional-demo copy (ChairSignal)
   with the real, anonymized outcome once one exists.

## Where each existing project fits

- **larrymayers.site** — the hub. Projects/Resume pages (see the
  accordion UI work below) are the credibility layer a prospective client
  or recruiter checks before trusting either track.
- **Scoremark** — Track B's technical backbone (data-quality diagnostics),
  live and deployed. Keep improving it as the "this is what a Tier 1
  diagnostic actually runs on" proof, not as a stand-alone product pitch.
- **ChairSignal** — originally built as a direct-to-dental-practice sales
  page; reframed by this roadmap as a **methodology demonstration**
  (how metrics are defined, how caveats are stated honestly) rather than
  the sales motion itself. The actual dental sales motion is Track A/B
  above, informed by the vertical playbook below.
- **Cartana** (`public/dev-projects/cartana/`) — separate product, not
  currently part of this consulting roadmap; revisit if it becomes
  relevant to a specific client conversation.

## UI/credibility work completed under this roadmap

- Unified color tokens and fonts (Inter / Space Grotesk / JetBrains Mono,
  dark charcoal + blue primary + teal accent, taken from
  `public/assets/css/main.css`) across the main portfolio, Scoremark, and
  ChairSignal, so all three read as one consistent brand instead of three
  unrelated-looking sites.
- Resume and Projects pages now render as a one-liner "feed" —
  `<details>`/`<summary>` per entry, JS-animated open/close (see
  `public/assets/js/script.js`) — replacing a static card grid and a
  dead, never-wired-up accordion attempt that was left in the codebase
  from an earlier try. Resume entries now also show their duties list for
  the first time (data existed in the DB, was never rendered publicly).

## Vertical playbooks (elevator pitch + immediate paid "fires")

Each playbook below follows the same shape: a pitch that names a provable
problem (not an unprovable insight), then a short list of concrete,
fixed-fee fixes a prospective client can say yes to immediately —
Track A items first (website/presence), Track B items last (only pitched
after trust exists).

### Dentists

**Grounding**: dental practices lose patients at two provable points —
local search visibility and the "traffic-to-call gap" (a visitor finds
the site but doesn't call) — both well-documented, specific failure modes
([eseospace.com](https://eseospace.com/blog/local-seo-for-dental-and-medical-practices-2026-checklist/),
[dentaltown.com](https://www.dentaltown.com/channel/post/24466/local-seo-guide-for-dentists-what-works-in-2026-and-what-doesnt),
[gravitydental.com](https://gravitydental.com/dental-website-traffic-no-phone-calls/)).
Neither claim requires guessing at a practice's internal KPIs the way the
original ChairSignal pitch did — both are externally verifiable in minutes,
from the practice's own public footprint, before any conversation happens.

**Elevator pitch**: "Most dental practices I look at are losing new
patients in two places that have nothing to do with the dentistry itself:
people searching nearby can't find you, or they land on your site and
leave without calling. I can show you exactly where that's happening for
your practice in about fifteen minutes — free, before you spend anything."

**Immediate fires (fee-based):**
1. **NAP + Google Business Profile audit and fix** — find and correct
   inconsistent name/address/phone listings across directories, claim or
   complete the Google Business Profile (categories, hours, photos).
   Inconsistent NAP data is a documented, specific ranking killer, not a
   vague "do more SEO" gesture.
2. **Mobile + page-speed fix** — compress oversized images, repair
   mobile layout breakage, make the phone number a tap-to-call link
   visible without scrolling. Slow, broken-on-mobile sites are called out
   repeatedly across every source above as the single most common,
   independently-verifiable issue.
3. **Call-to-action repair** — one clear "Book Appointment" / "Call Now"
   action per page, instead of a vague contact page three clicks away.
   This is the specific, nameable fix for the "traffic but no calls" gap.
4. **Review-generation setup** — a templated post-visit review request
   (SMS/email template, or a QR code for the front desk) to build the
   steady review cadence Google's local ranking rewards, replacing
   whatever ad hoc or nonexistent process exists today.
5. **(Track B, offered later, not in the first conversation)** — once a
   Track A engagement has built trust, offer the Reporting Reality Check
   against the practice's *real* appointment export, using Scoremark's
   engine. This is the moment ChairSignal's original pitch arrives
   honestly: not "here's an insight," but "you already gave me the
   file — let me show you what's actually in it."

### Real estate / realty agencies

**Grounding**: the dominant, well-documented failure mode for small
agencies isn't a missing insight either — it's **lead leakage**: inquiries
from a website form, a property portal, WhatsApp, and ad campaigns each
land in a different place, often not tracked anywhere at all, and the
person who should be following up is instead manually re-entering data
between systems
([biztechcs.com](https://www.biztechcs.com/blog/how-the-saas-based-martech-platform-helped-solve-the-agencys-real-estate-marketing-problems/),
[hicronsoftware.com](https://hicronsoftware.com/blog/implementing-a-crm-for-real-estate/),
[casafari.com](https://www.casafari.com/insights/real-estate-crm-for-estate-agencies-2026/)).
A second common, independently-checkable failure: listings still marked
"available" on the website or a portal after they've actually sold or
gone under contract.

**Elevator pitch**: "Almost every agency I look at loses leads the exact
same way — someone inquires through your website, a portal, or WhatsApp,
and that inquiry doesn't make it into one place your team actually checks.
I can test your own contact forms and listings right now and show you
specifically where that's happening — no cost to look."

**Immediate fires (fee-based):**
1. **Lead-capture audit** — submit real test inquiries through every
   channel (website form, portal, WhatsApp) and confirm each one actually
   reaches someone, with a timestamp. Silently broken contact forms are a
   well-documented, specific, and shockingly common failure, not a
   hypothetical.
2. **Stale-listing sync check** — audit live listings across the agency's
   website and any portals/social pages against what's actually still
   on the market; fixed-fee cleanup of anything sold/under contract but
   still shown as available.
3. **One-inbox lead consolidation** — a simple automation or shared
   tracking view so website, portal, and WhatsApp inquiries land in one
   place with their source attached, replacing manual CSV downloads and
   the "which platform's number do we trust" problem multiple
   disconnected tools create.
4. **Listing photo / page-speed optimization** — compress and
   properly-size the large, uncompressed listing photos that are one of
   the most common, directly observable real-estate site performance
   problems.
5. **(Track B, offered later)** — once trust exists, a Reporting Reality
   Check against their actual lead/inquiry export: which channels and
   listings are really producing inquiries versus just traffic, reusing
   the same diagnostic approach as the dental vertical.

## Open questions / next steps

- Which 3–5 specific Barbados businesses are the first Track A targets?
- Pricing for Track A website builds — not yet set.
- Does the free Reporting Reality Check need a time cap (e.g., capped at
  2 hours of review) to stay sustainable at zero fee?
- First real case study is the single most valuable asset this roadmap
  can produce — prioritize getting to step 3 of the phased roadmap over
  any further tooling work.
