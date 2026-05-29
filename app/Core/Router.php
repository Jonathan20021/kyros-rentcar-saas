<?php
namespace App\Core;

/**
 * Lightweight regex router with named params ({slug}, {id}) and middleware.
 * Routes map to "Controller@method". Controllers are resolved in App\Controllers.
 */
class Router
{
    protected array $routes = [];

    public function add(string $method, string $pattern, $handler, array $middleware = []): void
    {
        $this->routes[] = [
            'method'     => strtoupper($method),
            'pattern'    => $pattern,
            'handler'    => $handler,
            'middleware' => $middleware,
            'regex'      => $this->compile($pattern),
        ];
    }

    public function get(string $p, $h, array $m = []): void    { $this->add('GET', $p, $h, $m); }
    public function post(string $p, $h, array $m = []): void   { $this->add('POST', $p, $h, $m); }
    public function put(string $p, $h, array $m = []): void    { $this->add('PUT', $p, $h, $m); }
    public function delete(string $p, $h, array $m = []): void { $this->add('DELETE', $p, $h, $m); }

    protected function compile(string $pattern): string
    {
        // {name} -> named capture of non-slash chars
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);
        return '#^' . $regex . '$#';
    }

    public function dispatch(Request $request): void
    {
        $uri    = $request->uri();
        $method = $request->method();
        $matchedUri = false;

        foreach ($this->routes as $route) {
            if (preg_match($route['regex'], $uri, $matches)) {
                $matchedUri = true;
                if ($route['method'] !== $method) {
                    continue;
                }

                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $request->setParams($params);

                // Run middleware chain (each may halt by exiting/redirecting)
                foreach ($route['middleware'] as $mw) {
                    $instance = is_string($mw) ? new $mw() : $mw;
                    $instance->handle($request);
                }

                $this->invoke($route['handler'], $request, $params);
                return;
            }
        }

        if ($matchedUri) {
            $this->abort(405, 'Metodo no permitido');
        } else {
            $this->abort(404, 'Pagina no encontrada');
        }
    }

    protected function invoke($handler, Request $request, array $params): void
    {
        if (is_callable($handler)) {
            echo $handler($request, $params);
            return;
        }

        [$class, $action] = explode('@', $handler);
        $fqcn = "App\\Controllers\\{$class}";

        if (!class_exists($fqcn)) {
            $this->abort(500, "Controlador no encontrado: {$class}");
        }
        $controller = new $fqcn();
        if (!method_exists($controller, $action)) {
            $this->abort(500, "Accion no encontrada: {$class}@{$action}");
        }

        // Pass positional named params to action after the request.
        $controller->$action($request, ...array_values($params));
    }

    public function abort(int $code, string $message = ''): void
    {
        http_response_code($code);
        $view = "errors/{$code}";
        $file = (Config::get('app.root_path') . '/app/Views/' . $view . '.php');
        if (is_file($file)) {
            View::display($view, ['message' => $message]);
        } else {
            echo "<h1>{$code}</h1><p>" . htmlspecialchars($message, ENT_QUOTES) . "</p>";
        }
        exit;
    }
}
