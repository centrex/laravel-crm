# agents.md

## Agent Guidance — laravel-package-laravel-crm

### Package Purpose
Template/scaffold for creating new Laravel packages in this monorepo. This directory should be copied and customized — it is not a functional package itself.

### When to Use This
When asked to create a new Laravel package in this monorepo:
1. Copy this directory to a new name (e.g., `laravel-newfeature`)
2. Follow the find-and-replace checklist in `CLAUDE.md`
3. Add the new directory as a git submodule in the parent repo
4. Update the parent `CLAUDE.md` package table

### Find-and-Replace Checklist When Creating a New Package
| Placeholder | Replace with |
|---|---|
| `Centrex` | `Centrex` |
| `Crm` | `YourPackageName` (PascalCase) |
| `laravel-crm` | `your-package-name` (kebab-case) |
| `package_description` | Actual one-line description |
| `This is my package laravel-crm` | Actual description in `composer.json` |
| `vendorname/laravel-crm` | `centrex/your-package-name` |

### Files to Rename After Copy
- `src/Crm.php` → `src/YourPackageName.php`
- `src/CrmServiceProvider.php` → `src/YourPackageNameServiceProvider.php`
- `src/Facades/Crm.php` → `src/Facades/YourPackageName.php`
- `config/laravel-crm.php` → `config/your-package-name.php`

### Files to Update After Rename
- `composer.json` — name, description, autoload namespace
- `src/YourPackageNameServiceProvider.php` — config key, migration path, view path
- `tests/TestCase.php` — service provider registration
- `workbench/` — update app config and providers

### Do Not
- Use this laravel-crm as-is in production — replace all placeholders first
- Add real logic to this directory — it's a template only
- Commit secrets or real API keys while scaffolding

### Verifying a New Package
After scaffolding, run from the new package directory:
```sh
composer install && composer test
```
All tests should pass on a fresh scaffold before adding real functionality.
