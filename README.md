# php-hemiframe

PHP micro framework/library focused on two primitives:

- Routing: `HemiFrame\Lib\Router`
- Dependency injection: `HemiFrame\Lib\DependencyInjection\Container`

## Requirements

- PHP >= 8.1

## Install

```bash
composer require hemiframe/hemiframe
```

The library is PSR-4 autoloaded under the `HemiFrame\\` namespace (see `composer.json`).

## Quick start

### Routing (manual)

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$router = new HemiFrame\Lib\Router();

$router->setRoute([

    'key' => 'users',
    'url' => '/users',
    'controller' => '/App/Controllers/Users',
]);

$router->setRequestUri('/users');
var_dump($router->match());
```

Route variables:

- `{{name}}` (string-ish)
- `{{name|number}}` (digits only)

### Routing (auto-discovery)

`Router::scanDirectory($path)` inspects PHP files and discovers routes via either:

- PHP 8 attributes: `#[HemiFrame\Lib\Routing\Attributes\Route(url: ..., key: ...)]`
- Docblock annotation: `@Route({"url": "/...", "key": "..."})`

Example (see `examples/src/Test.php`):

```php
use HemiFrame\Lib\Routing\Attributes\Route;

class Test
{
    #[Route(url: '/comments', key: 'comments')]
    public function comments() {}
}
```

### Dependency injection

The DI container is `HemiFrame\Lib\DependencyInjection\Container`.

- Constructor injection: if constructor params are type-hinted (non-builtin), the container auto-wires them.
- Property injection (opt-in): add `#[HemiFrame\Lib\DependencyInjection\Attributes\Inject]` or `@Inject`.
- Singletons:
  - per-rule: `['singleton' => true]`
  - per-class: `#[HemiFrame\Lib\DependencyInjection\Attributes\Singleton]` (also inherited from parent)
  - docblock: `@Singleton` (also inherited from parent)

Container rules:

- `instance`: factory closure returning an object
- `instanceOf`: alias interface => concrete
- `call`: invoke methods after construction

Runnable DI example: `php examples/dependencyInjection.php`.

### Application entry point

`HemiFrame\Application::run()` delegates to `Router::match()` and returns:

```php
['key', 'class', 'method', 'vars', 'lang', 'priority']
```

## Caching (Router scan)

`Router::scanDirectory()` can cache discovered routes when a cache is set via `Router::setCache()`.

- Cache interface: `HemiFrame\Interfaces\Cache`
- Implementations: `src/HemiFrame/Lib/Cache/*` (e.g. in-memory `HemiFrame\Lib\Cache\Memory`)

## Template engine

`HemiFrame\Template` is a small string/file template parser.

- Variables: `{{name}}` or `{{object.field}}`
- Loops: `<wLoop id="items"> ... </wLoop>` via `Template::setLoop()`
- Switchers: `<wSwitcher id="type"><case value="..."></case></wSwitcher>` via `Template::setSwitcher()`

## Development

```bash
./vendor/bin/phpunit
composer run phpstan
composer run php-cs-fixer
composer run php-cs-fixer-apply
```

Notes:

- PHPStan runs at level 5 (`phpstan.neon`).
- On current HEAD, PHPStan reports a few issues in `src/HemiFrame/Lib/DependencyInjection/Container.php` and `src/HemiFrame/Template.php`.
- VS Code debug config is in `.vscode/launch.json` (Xdebug port 9000).

## Examples

```bash
php examples/router.php
php examples/dependencyInjection.php
```
