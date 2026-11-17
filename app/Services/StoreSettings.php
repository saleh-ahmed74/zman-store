<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\StoreSetting;
use Illuminate\Support\Facades\Cache;

final class StoreSettings
{
    private const CACHE_KEY = 'store.settings.all';

    /** @return array<string, mixed> */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn (): array => StoreSetting::query()
            ->get(['key', 'value'])
            ->mapWithKeys(fn (StoreSetting $setting): array => [$setting->key => $setting->value])
            ->all());
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return data_get($this->all(), $key, $default);
    }

    /** @param array<string, mixed> $value */
    public function save(string $key, array $value, string $group = 'general'): StoreSetting
    {
        $setting = StoreSetting::query()->updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
        Cache::forget(self::CACHE_KEY);

        return $setting;
    }
}
