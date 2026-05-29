<?php
namespace App\Core;

use RuntimeException;

/**
 * Simple PHP template renderer with layout support.
 * Views live in /app/Views and use plain PHP. Always escape output with e().
 */
class View
{
    protected static string $viewPath;
    protected static array $stacks = [];

    public static function setPath(string $path): void
    {
        self::$viewPath = rtrim($path, DIRECTORY_SEPARATOR);
    }

    /** Push content (e.g. page scripts) onto a named stack from within a view. */
    public static function push(string $stack, string $content): void
    {
        self::$stacks[$stack][] = $content;
    }

    /** Render and clear a named stack (call from the layout). */
    public static function stack(string $stack): string
    {
        $out = implode("\n", self::$stacks[$stack] ?? []);
        self::$stacks[$stack] = [];
        return $out;
    }

    /**
     * Render a view, optionally wrapped in a layout.
     * The view content is captured and exposed to the layout as $content.
     */
    public static function render(string $view, array $data = [], ?string $layout = null): string
    {
        $content = self::renderPartial($view, $data);

        if ($layout !== null) {
            $data['content'] = $content;
            return self::renderPartial("layouts/{$layout}", $data);
        }
        return $content;
    }

    /** Render a view file and return the output as a string. */
    public static function renderPartial(string $view, array $data = []): string
    {
        $file = self::$viewPath . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $view) . '.php';
        if (!is_file($file)) {
            throw new RuntimeException("Vista no encontrada: {$view}");
        }
        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        return (string) ob_get_clean();
    }

    /** Echo a rendered view (with optional layout) to the output buffer. */
    public static function display(string $view, array $data = [], ?string $layout = null): void
    {
        echo self::render($view, $data, $layout);
    }
}
