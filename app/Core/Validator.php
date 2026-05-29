<?php
namespace App\Core;

/**
 * Server-side validator. Rules are pipe-separated, e.g. "required|email|max:150".
 * Supported: required, email, numeric, integer, min:n, max:n, in:a,b,c,
 *            confirmed, date, boolean, slug, same:field, regex:/.../
 */
class Validator
{
    protected array $data;
    protected array $rules;
    protected array $errors = [];
    protected array $validated = [];

    protected array $labels = [];

    public function __construct(array $data, array $rules, array $labels = [])
    {
        $this->data   = $data;
        $this->rules  = $rules;
        $this->labels = $labels;
        $this->run();
    }

    protected function label(string $field): string
    {
        return $this->labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }

    protected function run(): void
    {
        foreach ($this->rules as $field => $ruleStr) {
            $value = $this->data[$field] ?? null;
            $rules = is_array($ruleStr) ? $ruleStr : explode('|', $ruleStr);
            $isRequired = in_array('required', $rules, true);

            // Skip optional empty fields
            if (!$isRequired && ($value === null || $value === '')) {
                $this->validated[$field] = $value;
                continue;
            }

            // min/max compare numerically only when the field is explicitly numeric;
            // otherwise they compare string length (so digit-only phones aren't treated as numbers).
            $treatNumeric = in_array('numeric', $rules, true) || in_array('integer', $rules, true);

            foreach ($rules as $rule) {
                [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);
                if (!$this->applyRule($name, $field, $value, $param, $treatNumeric)) {
                    break; // stop at first error per field
                }
            }

            if (!isset($this->errors[$field])) {
                $this->validated[$field] = $value;
            }
        }
    }

    protected function fail(string $field, string $message): bool
    {
        $this->errors[$field][] = $message;
        return false;
    }

    protected function applyRule(string $name, string $field, $value, ?string $param, bool $treatNumeric = false): bool
    {
        $label = $this->label($field);
        switch ($name) {
            case 'required':
                if ($value === null || $value === '' || (is_array($value) && count($value) === 0)) {
                    return $this->fail($field, "El campo {$label} es obligatorio.");
                }
                break;
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return $this->fail($field, "El campo {$label} debe ser un correo valido.");
                }
                break;
            case 'numeric':
                if (!is_numeric($value)) {
                    return $this->fail($field, "El campo {$label} debe ser numerico.");
                }
                break;
            case 'integer':
                if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                    return $this->fail($field, "El campo {$label} debe ser un entero.");
                }
                break;
            case 'min':
                if ($treatNumeric ? ((float)$value < (float)$param) : (mb_strlen((string)$value) < (int)$param)) {
                    return $this->fail($field, $treatNumeric
                        ? "El campo {$label} debe ser mayor o igual a {$param}."
                        : "El campo {$label} debe tener al menos {$param} caracteres.");
                }
                break;
            case 'max':
                if ($treatNumeric ? ((float)$value > (float)$param) : (mb_strlen((string)$value) > (int)$param)) {
                    return $this->fail($field, $treatNumeric
                        ? "El campo {$label} debe ser menor o igual a {$param}."
                        : "El campo {$label} no debe exceder {$param} caracteres.");
                }
                break;
            case 'in':
                $allowed = explode(',', (string)$param);
                if (!in_array((string)$value, $allowed, true)) {
                    return $this->fail($field, "El valor de {$label} no es valido.");
                }
                break;
            case 'confirmed':
                if (($this->data[$field . '_confirmation'] ?? null) !== $value) {
                    return $this->fail($field, "La confirmacion de {$label} no coincide.");
                }
                break;
            case 'same':
                if (($this->data[$param] ?? null) !== $value) {
                    return $this->fail($field, "El campo {$label} no coincide.");
                }
                break;
            case 'date':
                if (strtotime((string)$value) === false) {
                    return $this->fail($field, "El campo {$label} debe ser una fecha valida.");
                }
                break;
            case 'boolean':
                if (!in_array((string)$value, ['0','1','true','false','on','off',''], true)) {
                    return $this->fail($field, "El campo {$label} es invalido.");
                }
                break;
            case 'slug':
                if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', (string)$value)) {
                    return $this->fail($field, "El campo {$label} debe ser un slug valido.");
                }
                break;
            case 'regex':
                if (!preg_match($param, (string)$value)) {
                    return $this->fail($field, "El campo {$label} tiene un formato invalido.");
                }
                break;
        }
        return true;
    }

    public function fails(): bool { return !empty($this->errors); }
    public function passes(): bool { return empty($this->errors); }
    public function errors(): array { return $this->errors; }
    public function validated(): array { return $this->validated; }
    public function firstError(): ?string
    {
        foreach ($this->errors as $msgs) { return $msgs[0]; }
        return null;
    }
}
