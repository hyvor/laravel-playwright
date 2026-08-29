# Unreleased
- `truncate()` now accepts an options object with `except` (keep these tables, truncate the rest). Requested table names are validated against the live schema listing and rejected (422) if unknown. Default behaviour (wipe all tables) is unchanged.

# 2.0.0
- Renamed PHP package from `hyvor/laravel-e2e` to `hyvor/laravel-playwright`
- Introduces a new NPM package `@hyvor/laravel-playwright` for the frontend
- Adds new methods for dynamic configuration

# 1.0.0
- Requires Laravel 11