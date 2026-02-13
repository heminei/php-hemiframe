# Copilot instructions (php-hemiframe)

## Big picture
- This repo is a small PHP framework/library under `src/HemiFrame` (PSR-4 namespace `HemiFrame\\`).
- The two core primitives are:
  - Routing: `HemiFrame\Lib\Router` matches a request URI to a `{class, method, vars}` route.
  - DI: `HemiFrame\Lib\DependencyInjection\Container` constructs objects and injects dependencies.

## Key entry points & data flow
- Routing flow: `HemiFrame\Application::run()` delegates to `HemiFrame\Lib\Router::match()` and returns an array:
  - `['key', 'class', 'method', 'vars', 'lang', 'priority']` (see `Router::resetCurrentRoute()` / `Router::match()`).
- Router definitions:
  - Manual routes via `Router::setRoute([...])` (example: `examples/router.php`).
  - Auto-discovery via `Router::scanDirectory($path)` which reads:
    - PHP 8 attributes `#[HemiFrame\Lib\Routing\Attributes\Route(...)]` (example: `examples/src/Test.php`).
    - Docblock annotations `@Route({ ...json... })` (also in `examples/src/Test.php`).
  - URL vars use `{{name}}` or `{{name|number}}` (see regex replacements in `Router::match()`).

## Dependency injection conventions
- Prefer constructor injection with type-hints: `Container::get()` auto-wires non-builtin constructor params when no explicit `$arguments` are passed.
- Property injection is supported and is opt-in:
  - Add `#[HemiFrame\Lib\DependencyInjection\Attributes\Inject]` OR `@Inject` in the property docblock.
  - Use a typed property where possible (preferred); otherwise `@var` is parsed.
- Singleton behavior:
  - Via container rule: `['singleton' => true]` (examples: `examples/bootstrap.php`, `examples/dependencyInjection.php`).
  - Or mark the class (or parent class) with `#[...\Singleton]` or `@Singleton` (examples: `examples/src/TestSingleton.php`, `examples/src/AbstractSingleton.php`).
- Container rules you’ll see:
  - `instance`: factory closure returning an instance.
  - `instanceOf`: alias interface => concrete class.
  - `call`: method calls after construction.

## Caching integration
- `Router::scanDirectory()` supports caching discovered routes when a cache is set with `Router::setCache()`.
- Cache must implement `HemiFrame\Interfaces\Cache` (see `src/HemiFrame/Interfaces/Cache.php`).
- Implementations live in `src/HemiFrame/Lib/Cache/*` (many also implement `Psr\SimpleCache\CacheInterface`, e.g. `Lib\Cache\Memory`).

## Dev workflows (local)
- Requirements: PHP >= 8.1 (see `composer.json`).
- Static analysis: `composer run phpstan` (config: `phpstan.neon`, level 5).
- Note: on current HEAD, PHPStan reports a few issues in `src/HemiFrame/Lib/DependencyInjection/Container.php` and `src/HemiFrame/Template.php`.
- Formatting:
  - Check: `composer run php-cs-fixer` (dry-run)
  - Apply: `composer run php-cs-fixer-apply`
  - Rules: Symfony preset (see `.php-cs-fixer.dist.php`).
- Tests: `./vendor/bin/phpunit` (suite: `tests/unit`, bootstrap: `tests/bootstrap.php`).
- Debugging: VS Code launch config listens on Xdebug port 9000 (see `.vscode/launch.json`).

## Practical examples
- Run router matching demo: `php examples/router.php`.
- Run DI demo: `php examples/dependencyInjection.php`.

## When editing/adding code
- Keep namespaces PSR-4 aligned (`HemiFrame\\` => `src/HemiFrame`).
- When adding DI-friendly classes, type-hint dependencies (constructor/property) so the container can resolve them.
- When adding routes, prefer the `#[Route(...)]` attribute (repeatable) over docblock `@Route` unless you need compatibility.
