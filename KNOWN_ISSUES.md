# Known Issues — laravel-crm

_Last checked: 2026-08-02_

## Failing tests

No failing tests. `composer test:unit` (Pest, parallel) passes fully: 13 passed (41 assertions). Note the suite is thin relative to the surface area (WhatsApp messaging, templates, CLV calculator, email settings, activities, tags) — passing tests here don't imply broad coverage.

## Style / static-analysis debt

- `vendor/bin/pint --test` reports a clean pass — no style debt.
- `vendor/bin/rector --dry-run` reports **2 files** with pending refactors: `src/Http/Controllers/WhatsappController.php` (`CompactToVariablesRector` — replace `compact(...)` calls with explicit arrays) and `src/Services/WhatsappService.php` (`FlipTypeControlToUseExclusiveTypeRector`). Run `composer refacto` to apply. (This failure is what stops the chained `composer test` script before reaching lint/types/unit — each subsequent step was run individually to get the results below.)
- `vendor/bin/phpstan analyse` (level: `max`) reports **397 errors**, and `phpstan-baseline.neon` is present but **empty (0 entries)** — none of these are pre-accepted debt; all are live/unbaselined. The vast majority are the generic boilerplate class (missing generic type params on `belongsToMany()`/`morphToMany()`/`hasMany()`/`belongsTo()` relations, "no value type specified in iterable type array" on model constructors and service method signatures) spread across most models and services (`src/Models/Tag.php`, `WhatsappMessage.php`, `WhatsappTemplate.php`, `src/Services/ClvCalculator.php`, `WhatsappService.php`, `src/Support/EmailSettings.php`, etc.), plus repeated `updateOrCreate()`/`create()`/`where()` calls passed array keys phpstan can't match to model properties (e.g. `WhatsappMessage::create()` in `WhatsappService.php:80` flagged against `contact_id`, `message_body`, `phone`, `sent_by`, `status`, `template_id`, `type`, `wa_url`; similarly `Activity::create()` at line 95 and `EmailSetting::updateOrCreate()` in `EmailSettings.php:51-52`).
  - Two of these look alarming but were verified as tooling limitations, not real bugs: **`Access to an undefined property Centrex\Crm\Models\WhatsappMessage::$status`** (`src/Models/WhatsappMessage.php:57,62`) and **`Access to an undefined property Centrex\Crm\Models\WhatsappTemplate::$message_body`** (`src/Models/WhatsappTemplate.php:48`). Both columns genuinely exist (see `database/migrations/2026_05_05_000001_create_crm_whatsapp_tables.php` — `status` and `message_body` are both defined) and are listed in each model's `$fillable`. Larastan's database-backed property resolution most likely can't follow these models' dynamic `setConnection()` call (in `__construct()`) and custom table name (via the `AddTablePrefix`/`getTableSuffix()` trait) to introspect the real columns, so it treats the properties as undefined. No `@property` docblocks are present on either model to compensate — adding them would silence this specific false positive.

## TODO / FIXME markers

None found (`grep -rn "TODO\|FIXME" --include="*.php" src/ config/ database/ routes/` — no matches).

## Open GitHub issues

Not checked — the `gh` CLI is not installed in this environment.
