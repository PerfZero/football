<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Football_Data_Mock_Repository
{
    private ?array $data = null;

    public function all(string $type): array
    {
        $data = $this->data();

        return isset($data[$type]) && is_array($data[$type]) ? $data[$type] : [];
    }

    public function find(string $type, string|int $id): ?array
    {
        foreach ($this->all($type) as $item) {
            if ((string) ($item['id'] ?? '') === (string) $id) {
                return $item;
            }
        }

        return null;
    }

    public function types(): array
    {
        return array_keys($this->data());
    }

    private function data(): array
    {
        if ($this->data !== null) {
            return $this->data;
        }

        $file = FOOTBALL_DATA_DIR . 'data/mock-data.json';
        if (!file_exists($file)) {
            $this->data = [];

            return $this->data;
        }

        $decoded = json_decode((string) file_get_contents($file), true);
        $this->data = is_array($decoded) ? $decoded : [];

        return $this->data;
    }
}
