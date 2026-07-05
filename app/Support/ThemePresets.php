<?php

namespace App\Support;

class ThemePresets
{
    public static function options(): array
    {
        return collect(config('themes.presets', []))
            ->except('custom')
            ->mapWithKeys(fn (array $theme, string $key) => [$key => $theme['label']])
            ->all();
    }

    public static function preset(string $key): ?array
    {
        $preset = config("themes.presets.{$key}");

        if (! is_array($preset) || $key === 'custom') {
            return null;
        }

        return $preset;
    }

    public static function applyPreset(string $key): array
    {
        $preset = self::preset($key);

        if (! $preset) {
            return [];
        }

        return [
            'accent_color' => $preset['accent'],
            'secondary_color' => $preset['secondary'],
            'surface_color' => $preset['surface'],
            'surface_muted_color' => $preset['surface_muted'],
        ];
    }

    public static function detectPreset(
        string $accent,
        string $secondary,
        string $surface,
        string $surfaceMuted,
    ): string {
        foreach (config('themes.presets', []) as $key => $preset) {
            if ($key === 'custom') {
                continue;
            }

            if (
                self::normalizeColor($preset['accent']) === self::normalizeColor($accent)
                && self::normalizeColor($preset['secondary']) === self::normalizeColor($secondary)
                && self::normalizeColor($preset['surface']) === self::normalizeColor($surface)
                && self::normalizeColor($preset['surface_muted']) === self::normalizeColor($surfaceMuted)
            ) {
                return $key;
            }
        }

        return 'custom';
    }

    public static function description(string $key): ?string
    {
        return config("themes.presets.{$key}.description");
    }

    protected static function normalizeColor(string $color): string
    {
        return strtoupper(ltrim(trim($color), '#'));
    }
}
