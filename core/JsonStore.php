<?php

namespace Core;

class JsonStore
{
    private string $filePath;

    public function __construct(string $filename)
    {
        $baseDir = __DIR__ . '/../storage/data/';

        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0755, true);
        }

        $this->filePath = $baseDir . $filename;

        if (!file_exists($this->filePath)) {
            file_put_contents($this->filePath, json_encode([], JSON_PRETTY_PRINT), LOCK_EX);
        }
    }

    public function readAll(): array
    {
        $content = file_get_contents($this->filePath);

        if ($content === false || $content === '') {
            return [];
        }

        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }

    public function find(string $id): ?array
    {
        foreach ($this->readAll() as $record) {
            if (($record['id'] ?? '') === $id) {
                return $record;
            }
        }
        return null;
    }

    public function findBy(string $field, mixed $value): array
    {
        return array_values(array_filter(
            $this->readAll(),
            fn(array $record) => ($record[$field] ?? null) === $value
        ));
    }

    public function create(array $data): array
    {
        $records = $this->readAll();

        $data['id']         = $this->generateUuid();
        $data['created_at'] = date('Y-m-d\TH:i:s\Z');
        $data['updated_at'] = date('Y-m-d\TH:i:s\Z');

        $records[] = $data;
        $this->writeAll($records);

        return $data;
    }

    public function update(string $id, array $data): ?array
    {
        $records = $this->readAll();
        $found   = false;

        foreach ($records as &$record) {
            if (($record['id'] ?? '') === $id) {
                unset($data['id'], $data['created_at']);
                $record = array_merge($record, $data);
                $record['updated_at'] = date('Y-m-d\TH:i:s\Z');
                $found = true;
                break;
            }
        }
        unset($record);

        if (!$found) {
            return null;
        }

        $this->writeAll($records);

        return $this->find($id);
    }

    public function delete(string $id): bool
    {
        $records  = $this->readAll();
        $filtered = array_values(array_filter(
            $records,
            fn(array $record) => ($record['id'] ?? '') !== $id
        ));

        if (count($filtered) === count($records)) {
            return false;
        }

        $this->writeAll($filtered);
        return true;
    }

    public function search(string $field, string $query): array
    {
        $query = mb_strtolower($query);

        return array_values(array_filter(
            $this->readAll(),
            fn(array $record) => isset($record[$field])
                && str_contains(mb_strtolower((string) $record[$field]), $query)
        ));
    }

    private function writeAll(array $records): void
    {
        $tmpFile = $this->filePath . '.tmp.' . getmypid();
        $json    = json_encode(array_values($records), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $written = file_put_contents($tmpFile, $json, LOCK_EX);

        if ($written === false) {
            @unlink($tmpFile);
            throw new \RuntimeException("Failed to write to store: {$this->filePath}");
        }

        if (!rename($tmpFile, $this->filePath)) {
            @unlink($tmpFile);
            throw new \RuntimeException("Failed to atomically replace store: {$this->filePath}");
        }
    }

    private function generateUuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return sprintf(
            '%s-%s-%s-%s-%s',
            bin2hex(substr($bytes, 0, 4)),
            bin2hex(substr($bytes, 4, 2)),
            bin2hex(substr($bytes, 6, 2)),
            bin2hex(substr($bytes, 8, 2)),
            bin2hex(substr($bytes, 10, 6))
        );
    }
}
