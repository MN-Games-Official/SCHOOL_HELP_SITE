<?php

declare(strict_types=1);

define('BASE_PATH', __DIR__);

// Load configuration
$config = require BASE_PATH . '/config/app.php';

// Error reporting
if ($config['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Timezone
date_default_timezone_set($config['timezone']);

// Session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', (string) $config['session_lifetime']);
    session_set_cookie_params($config['session_lifetime']);
    session_start();
}

// Require core files
require_once BASE_PATH . '/core/Session.php';
require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Middleware.php';
require_once BASE_PATH . '/core/Request.php';
require_once BASE_PATH . '/core/Response.php';
require_once BASE_PATH . '/core/Router.php';
require_once BASE_PATH . '/core/View.php';
require_once BASE_PATH . '/core/Validator.php';
require_once BASE_PATH . '/core/JsonStore.php';

// Autoload controllers
spl_autoload_register(function (string $class): void {
    $file = BASE_PATH . '/controllers/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }

    $serviceFile = BASE_PATH . '/services/' . $class . '.php';
    if (file_exists($serviceFile)) {
        require_once $serviceFile;
    }
});

try {
    // Initialise core services
    $session   = new \Core\Session();
    $userStore = new \Core\JsonStore('users.json');

    // Wrap JsonStore to provide the interface Auth expects
    $userService = new class($userStore) {
        private \Core\JsonStore $store;

        public function __construct(\Core\JsonStore $store)
        {
            $this->store = $store;
        }

        public function findByEmail(string $email): ?array
        {
            $results = $this->store->findBy('email', $email);
            return $results[0] ?? null;
        }

        public function create(array $data): array
        {
            return $this->store->create($data);
        }
    };

    $auth = new \Core\Auth($session, $userService);
    $middleware  = new \Core\Middleware($auth, $session);

    // Create router and register middleware handlers
    $router = new \Core\Router();
    $router->registerMiddleware('auth', [$middleware, 'auth']);
    $router->registerMiddleware('guest', [$middleware, 'guest']);
    $router->registerMiddleware('teacher', [$middleware, 'teacher']);
    $router->registerMiddleware('admin', [$middleware, 'admin']);
    $router->registerMiddleware('csrf', [$middleware, 'csrf']);

    // Load routes
    require BASE_PATH . '/config/routes.php';

    // Create request and dispatch
    $request = new \Core\Request();
    $router->dispatch($request->method(), $request->path());
} catch (\Throwable $e) {
    if ($config['debug']) {
        http_response_code(500);
        echo '<h1>500 — Internal Server Error</h1>';
        echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        http_response_code(500);
        echo '<h1>500 — Internal Server Error</h1>';
        echo '<p>Something went wrong. Please try again later.</p>';
    }
}
