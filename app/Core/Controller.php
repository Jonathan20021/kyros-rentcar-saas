<?php
namespace App\Core;

/**
 * Base controller with view/redirect/json helpers.
 */
abstract class Controller
{
    protected function view(string $view, array $data = [], ?string $layout = null): void
    {
        // Make flashes and old input available to every view.
        $data['_flashes'] = $data['_flashes'] ?? Session::getFlashes();
        $data['_auth']    = Auth::user();
        View::display($view, $data, $layout);
        Session::clearOld();
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }

    protected function back(): void
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? url('/');
        header('Location: ' . $ref);
        exit;
    }

    protected function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function abort(int $code, string $message = ''): void
    {
        http_response_code($code);
        View::display("errors/{$code}", ['message' => $message]);
        exit;
    }

    /** Validate request payload using the Validator; flash errors + old input and redirect back on failure. */
    protected function validateOrBack(array $data, array $rules, string $redirectTo): array
    {
        $validator = new Validator($data, $rules);
        if ($validator->fails()) {
            Session::set('_errors', $validator->errors());
            Session::flashInput($data);
            foreach ($validator->errors() as $msgs) {
                foreach ((array) $msgs as $m) { Session::flash('error', $m); }
            }
            $this->redirect($redirectTo);
        }
        return $validator->validated();
    }
}
