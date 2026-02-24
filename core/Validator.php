<?php

namespace Core;

class Validator
{
    private array $errorBag = [];

    public function validate(array $data, array $rules): self
    {
        $this->errorBag = [];

        foreach ($rules as $field => $ruleSet) {
            $ruleList = is_array($ruleSet) ? $ruleSet : explode('|', $ruleSet);
            $value    = $data[$field] ?? null;

            foreach ($ruleList as $rule) {
                $this->applyRule($field, $value, $rule, $data);
            }
        }

        return $this;
    }

    public function errors(): array
    {
        return $this->errorBag;
    }

    public function fails(): bool
    {
        return !empty($this->errorBag);
    }

    private function applyRule(string $field, mixed $value, string $rule, array $data): void
    {
        $params = [];
        if (str_contains($rule, ':')) {
            [$rule, $paramStr] = explode(':', $rule, 2);
            $params = explode(',', $paramStr);
        }

        $label = ucfirst(str_replace('_', ' ', $field));

        switch ($rule) {
            case 'required':
                if ($value === null || $value === '' || $value === []) {
                    $this->addError($field, "{$label} is required.");
                }
                break;

            case 'email':
                if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "{$label} must be a valid email address.");
                }
                break;

            case 'min':
                $min = (int) ($params[0] ?? 0);
                if (is_string($value) && mb_strlen($value) < $min) {
                    $this->addError($field, "{$label} must be at least {$min} characters.");
                }
                break;

            case 'max':
                $max = (int) ($params[0] ?? 0);
                if (is_string($value) && mb_strlen($value) > $max) {
                    $this->addError($field, "{$label} must not exceed {$max} characters.");
                }
                break;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if (($data[$confirmField] ?? null) !== $value) {
                    $this->addError($field, "{$label} confirmation does not match.");
                }
                break;

            case 'unique':
                $storeName = $params[0] ?? '';
                $storeField = $params[1] ?? $field;
                if ($storeName && $value !== null && $value !== '') {
                    $store   = new JsonStore($storeName . '.json');
                    $matches = $store->findBy($storeField, $value);
                    if (!empty($matches)) {
                        $this->addError($field, "{$label} already exists.");
                    }
                }
                break;

            case 'in':
                if ($value !== null && $value !== '' && !in_array($value, $params, true)) {
                    $this->addError($field, "{$label} must be one of: " . implode(', ', $params) . ".");
                }
                break;
        }
    }

    private function addError(string $field, string $message): void
    {
        $this->errorBag[$field][] = $message;
    }
}
