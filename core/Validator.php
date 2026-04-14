<?php

namespace Core;

class Validator
{
    private array $data;
    private array $rules;
    private array $labels = [];
    private array $errors = [];

    private function __construct(array $data, array $rules, array $labels = [])
    {
        $this->data   = $data;
        $this->rules  = $rules;
        $this->labels = $labels;
    }

    public static function make(array $data, array $rules, array $labels = []): self
    {
        return new self($data, $rules, $labels);
    }

    private function getLabel(string $field): string
    {
        $label = $this->labels[$field] ?? $field;
        return '<strong>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</strong>';
    }

    public function passes(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $ruleSet) {
            $rules = $this->normalizeRules($ruleSet);
            $rawValue = $this->data[$field] ?? null;
            $value = is_string($rawValue) ? trim($rawValue) : $rawValue;

            $isNullable = in_array('nullable', $rules, true);
            if ($isNullable && ($value === null || $value === '')) {
                continue;
            }

            foreach ($rules as $rule) {
                [$name, $arg] = $this->parseRule($rule);

                switch ($name) {
                    case 'nullable':
                        break;

                    case 'required':
                        if ($value === null || $value === '') {
                            $this->addError($field, "El campo {$this->getLabel($field)} es obligatorio.");
                        }
                        break;

                    case 'string':
                        if ($value !== null && !is_string($value)) {
                            $this->addError($field, "El campo {$this->getLabel($field)} debe ser texto.");
                        }
                        break;

                    case 'min':
                        $min = (int) $arg;
                        if (is_string($value) && mb_strlen($value) < $min) {
                            $this->addError($field, "El campo {$this->getLabel($field)} debe tener al menos {$min} caracteres.");
                        }
                        break;

                    case 'max':
                        $max = (int) $arg;
                        if (is_string($value) && mb_strlen($value) > $max) {
                            $this->addError($field, "El campo {$this->getLabel($field)} no debe exceder {$max} caracteres.");
                        }
                        break;

                    case 'in':
                        $allowed = $arg === '' ? [] : explode(',', $arg);
                        if (!in_array((string) $value, $allowed, true)) {
                            $this->addError($field, "El campo {$this->getLabel($field)} tiene un valor invalido.");
                        }
                        break;

                    case 'regex':
                        if (!is_string($value) || @preg_match($arg, $value) !== 1) {
                            $this->addError($field, "El campo {$this->getLabel($field)} tiene un formato invalido.");
                        }
                        break;
                }

                if (isset($this->errors[$field])) {
                    break;
                }
            }
        }

        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function first(?string $field = null): ?string
    {
        if ($field !== null) {
            return $this->errors[$field][0] ?? null;
        }

        foreach ($this->errors as $messages) {
            if (!empty($messages)) {
                return $messages[0];
            }
        }

        return null;
    }

    public function value(string $field, mixed $default = ''): mixed
    {
        $value = $this->data[$field] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }

        $this->errors[$field][] = $message;
    }

    private function normalizeRules(string|array $ruleSet): array
    {
        if (is_array($ruleSet)) {
            return array_values(array_filter(array_map('trim', $ruleSet), static fn ($r) => $r !== ''));
        }

        return array_values(array_filter(array_map('trim', explode('|', $ruleSet)), static fn ($r) => $r !== ''));
    }

    private function parseRule(string $rule): array
    {
        $parts = explode(':', $rule, 2);
        $name = strtolower(trim($parts[0] ?? ''));
        $arg = trim($parts[1] ?? '');

        return [$name, $arg];
    }
}
