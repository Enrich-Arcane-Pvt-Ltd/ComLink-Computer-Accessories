# AGENTS.md

Rules for any AI assistant working in this repository. Read this file fully before every task. If a rule here conflicts with a prompt, stop and ask.

---

## 0. Project snapshot (fill in per project)

- **Project:** [name and one-line purpose]
- **Stack:** PHP 8.3+, Laravel 12, Laravel Breeze, Inertia.js, React, TypeScript, Tailwind CSS v4, Filament 5, [MySQL 8 / PostgreSQL]
- **Current phase:** [update after each phase]
- **Docs to verify against:** Laravel 12 docs, Filament 5 docs, Tailwind CSS v4 docs. Do NOT write code from memory of older versions (v2/v3/v4 Filament, Tailwind v3, Laravel 10/11). If unsure of an API, check the docs or say so.

---

## 1. How to work (process rules)

1. **Plan before code.** For any feature, first output a file-by-file plan (path, class name, single responsibility) using the checklist in section 8. Wait for approval before writing code.
2. **One phase / one task at a time.** Do not build beyond what was asked.
3. **No new packages** without asking first.
4. **Do not rewrite or reformat unrelated code.** Keep diffs small and focused.
5. **After finishing**, report: files created/changed, assumptions made, anything unsure (especially Filament 5 or Tailwind v4 APIs), and how to test it.
6. **Only create a layer if the feature needs it.** A simple CRUD does not need a service class. Do not create empty or pass-through layers just to follow the structure.
7. **Never invent** requirements, columns, roles or APIs. Ask when the spec is unclear.

---

## 2. Architecture map: where code goes

| Kind of code | Location | Rule |
|---|---|---|
| Validation, request authorization | `app/Http/Requests` | Never use `$request->validate()` in a controller |
| Routing and responses only | `app/Http/Controllers` | Thin: call an action/service, return an Inertia response |
| Business logic, multi-step operations | `app/Actions` (single) or `app/Services` (group) | One public purpose per class, e.g. `RevealCredentialAction` |
| Fixed value sets (status, priority, type) | `app/Enums` | Backed enums with `label()`; reuse in migrations, models, Filament |
| Data access and relations | `app/Models` | Relations, casts (`casts()` method), scopes, accessors only. No business logic |
| Who can do what | `app/Policies` | One policy per model; every model exposed in UI has one |
| Reacting to model events (audit, cache) | `app/Observers` | Keep small; heavy work goes to jobs |
| Emails and in-app alerts | `app/Notifications` | One class per notification |
| Slow or retryable work | `app/Jobs` | Queue it; never run slow work in the request |
| Request-level checks (role, throttling) | `app/Http/Middleware` | Register in `bootstrap/app.php` (Laravel 12 has no `Kernel.php`) |
| Binding services, global setup | `app/Providers` | No feature logic |
| Settings and env-driven values | `config/*.php` | Never call `env()` outside config files |
| Pure helper functions | `app/Support` | Stateless, no database access |
| Custom exceptions | `app/Exceptions` | Domain-specific, descriptive names |
| Data transfer between layers | `app/DTOs` (only when passing 3+ related values) | Readonly classes |
| Admin UI Panel | `app/Filament/...` | UI only. Calls actions/services, no business logic |
| Client Frontend | `resources/js/Pages`, `Components`, `Hooks` | Inertia pages, React components, typed props |

**Dependency direction:** Controllers / Filament Panels → Actions / Services → Models. Never the reverse. Models never call services or controllers.

---

## 3. Coding standards

### PHP
- Follow **PSR-12** and format with **Laravel Pint** (`./vendor/bin/pint`) before finishing any task.
- Add `declare(strict_types=1);` to new PHP files.
- Type everything: parameter types, return types, property types. Avoid `mixed` unless unavoidable.
- Use `final` for Actions, Services, DTOs and other classes not designed for extension.
- Use constructor property promotion and `readonly` where appropriate.
- Prefer early returns over deep nesting. Keep methods short and single-purpose.
- No magic numbers or strings: use enums, class constants or config.
- Use named arguments or DTOs when a method has more than 3 parameters.
- Do not leave dead code, commented-out code, `dd()`, `dump()`, or debug logging.
- Run static analysis (**Larastan / PHPStan**) if installed, and fix errors rather than suppressing them.

### Naming
| Item | Convention | Example |
|---|---|---|
| Classes | `PascalCase` | `ProjectCredential` |
| Methods, variables | `camelCase` | `revealSecret()` |
| Actions | Verb + noun + `Action` | `RevealCredentialAction` |
| Form Requests | `Store/Update` + Model + `Request` | `StoreProjectRequest` |
| Enums | Singular noun, cases in `PascalCase` | `ProjectStatus::InProgress` |
| Policies | Model + `Policy` | `ProjectPolicy` |
| DB tables / columns | `snake_case` (plural tables, singular FKs like `project_id`) | `project_credentials`, `lead_developer_id` |
| Routes | `kebab-case` URLs, dot-notation names | `projects.credentials.reveal` |
| React Components / Pages | `PascalCase` | `ProjectDetailsModal.tsx`, `Dashboard.tsx` |

### Eloquent and database
- Use `casts()` method (Laravel 12 style) for casts, including enum casts and `encrypted`.
- Define `$fillable` explicitly; never use `$guarded = []`.
- **Prevent N+1:** eager load relations (`with()`), and consider `Model::preventLazyLoading()` in non-production.
- Every migration: foreign keys with constraints and explicit `onDelete` behavior, indexes on search/filter columns.
- **Never edit a migration that has been run or committed.** Add a new migration.
- Use database transactions (`DB::transaction`) for multi-step writes.

---

## 4. Filament 5 rules

- A Resource is **configuration, not logic**. Put form schemas, table definitions and infolists in their own classes.
- Custom actions call an **Action/Service class**; no business logic inline in closures.
- Authorization lives in **Policies** (and Filament Shield if used), not in scattered `->visible(fn () => ...)` closures.
- Eager load relations used in tables (`->with()` / `modifyQueryUsing`).
- Sensitive fields must never appear in table columns, tooltips, infolists by default, or exports.
- Use enums for select/badge options; do not hard-code option arrays.

---

## 5. Frontend rules (Inertia + React + TypeScript + Tailwind v4)

- **Pages** (`resources/js/Pages`) are thin orchestrators/containers. Keep them lean.
- **Components** (`resources/js/Components`): reusable, domain-focused UI pieces. One component per file.
- **Types** (`resources/js/types`): shared TypeScript interfaces matching backend DTOs/Resources. No `any`; use `unknown` and narrow if needed. Props must be strictly typed.
- All validation and authorization is enforced on the server (Breeze handles user routes, Filament handles admin).
- **Styling (Tailwind v4):** 
  - Configure design tokens exclusively via `@theme` inside `resources/css/app.css`. No legacy `tailwind.config.js`.
  - Base elements (`body`, headings) styled **only** in `@layer base`.
  - **Forbidden:** hard-coded hex colors, font names, or arbitrary Tailwind brackets (`text-[17px]`, `bg-[#123456]`).

---

## 6. Security rules

- **Secrets:** use the `encrypted` cast for credentials, tokens, keys. Never store them plain.
- **Audit logging:** every sensitive action writes an `audit_logs` entry through the audit service.
- **Authorization:** deny by default. Every model has a Policy; every route/action is authorized.
- **Files:** store sensitive files on a **private disk**; serve via authorized, audited download routes.
- **`.env`** is never committed; keep `.env.example` current. Do not expose secrets in Inertia props.

---

## 7. Testing & Quality Assurance

- Use **Pest** for backend testing.
- Required tests: Policies (each role allowed/denied), Actions/Services with business rules, encryption behavior, audit logging triggers.
- Run Pint (`./vendor/bin/pint`) before reporting a task as done.

---

## 8. Feature checklist (fill in BEFORE writing code)

    Feature: [name]
    Purpose / user story: [one line]
    Roles/Interface affected: [Filament Admin vs. Inertia User App]

    Layers needed (mark which apply and list files):
    [ ] Migration
    [ ] Model + relations
    [ ] Enum(s)
    [ ] Policy
    [ ] Form Request(s)
    [ ] Action / Service
    [ ] Controller / route (Inertia or API)
    [ ] Filament Resource / Schema / Table / Action
    [ ] Frontend: page / hook / component / type
    [ ] Tests

    Security check: secrets involved? audit needed? authorization defined?
    Assumptions / open questions: [list]

---

## 9. Definition of done

- [ ] Matches approved plan; no extra scope
- [ ] Code is in correct layers per section 2
- [ ] Pint passes cleanly
- [ ] Tests written and passing
- [ ] Authorization verified for each role
- [ ] No hard-coded design values or Tailwind arbitrary brackets
- [ ] Assumptions and uncertainties reported