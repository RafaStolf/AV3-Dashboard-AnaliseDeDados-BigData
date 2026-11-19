# Brasil em Perspectiva Implementation Plan

> Execution: continue autonomously under the user's explicit authorization to implement. Use parallel agents for independent data and content work; integrate and review before completion. Do not commit, push or deploy without the applicable authorization.

**Goal:** Build a functional national Atlas dashboard, Markdown analyses and public documentation matching the approved editorial design.
**Architecture:** Laravel serves a Vue 3 TypeScript application and validated read APIs backed by SQLite. Versioned Markdown powers documentation and analyses; Cloudflare Tunnel connects the Oracle VPS at dashboard.stolner.com.
**Tech Stack:** Laravel, PHP 8.4+, Vue 3, TypeScript, Vite, ECharts, SQLite.
**Spec:** docs/especificacao.md

## Global Constraints

- Atlas census years 1991, 2000, 2010; municipalities grouped by UF and the five great regions.
- Approved ivory, navy and terracotta design with serif titles and sidebar.
- Sources on charts, articles, documentation and footer; no fabricated observations.
- Public docs are required; Notion is excluded.
- Keep secrets, runtime dependencies and database files out of Git and public paths.

## Review Focus

- Missing observations remain null; temporal comparisons require matched municipalities.
- Invalid filters, unknown territory IDs and unsafe content paths produce explicit safe results.
- Shareable URL filters survive refresh and back/forward navigation.
- Draft Markdown, scripts and private files cannot be exposed by document routes.
- Mobile navigation, search and chart resizing remain operable.

## Tasks

- [x] Runtime and foundation: install isolated PHP/Composer, create Laravel skeleton and Vue/Vite configuration; verify framework boot and production build.
- [x] Data pipeline: retrieve national Atlas indicators and territory metadata, retain provenance/checksums, validate and export normalized CSV with reproducible script. Import using a transactional Artisan command into SQLite.
- [x] Backend: typed observation responses, validated filters, statistics and safe content rendering/search. Feature tests cover invalid filters, known sample calculations, CSV export and draft/path isolation.
- [x] Frontend: sidebar shell, comparison, panorama/ranking, evolution, dimensions/distribution and association charts; shared filters and URL state; source annotations and table alternatives.
- [x] Content: Markdown analyses answering the five questions plus documentation categories, model, calculations, sources and operational guide. Render through allowlisted slugs and sanitized Markdown.
- [x] Deployment: production Docker/NGINX configuration, persistent SQLite, Cloudflare Tunnel example, setup/backup/restore instructions and environment sample for dashboard.stolner.com.
- [x] Validation: run backend tests, TypeScript and build, browser interactions at desktop/mobile, source/implementation visual comparison and independent final review. Fix material issues and report actual deployment status.

## Execution Ledger

Ruling: use a Laravel backend and Vue/Vite SPA with shared-origin API rather than Inertia; direct JSON contracts suit cached national datasets and documentation routes while retaining the requested stack. No runtime template shall replace Laravel or Vue.

Validation: 11 PHP tests / 52 assertions; 2 URL-state tests; 9 data regression tests; production build and typecheck; local Docker and Oracle ARM64 build/startup with 16,695 records; public HTTPS routes and private-file denial checks. Independent review identified four P2 findings; checksum/coverage, retained-metric semantics, derived HTML tables and trusted connector identity were corrected. Browser QA at desktop/mobile passed. A stale lazy chunk observed after a release change led to bounded automatic navigation recovery.


Production verification: https://dashboard.stolner.com returns the application; API confirms 5,565 rows / 5,564 valid values for 2010, 12 documentation pages and 5 analyses. Filtered São Paulo CSV has 645 rows in 2010. Public private-file checks returned 403/404. Oracle Docker services are running; SQLite initial backup and integrity/foreign-key checks passed. Existing unrelated containers remained running. No commit or push was performed.

