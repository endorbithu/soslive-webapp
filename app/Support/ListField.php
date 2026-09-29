<?php

namespace App\Support;

/**
 * Elválasztott listák (email címek, telefonszámok) normalizálása.
 */
class ListField
{
    /**
     * Email címek: vessző, pontosvessző, szóköz vagy sortörés választja el őket.
     *
     * @return list<string>
     */
    public static function emails(?string $value): array
    {
        return self::parse($value, '/[\s,;]+/', lowercase: true);
    }

    /**
     * Telefonszámok: csak vessző, pontosvessző vagy sortörés választ el (a szóköz a számon belül megengedett).
     *
     * @return list<string>
     */
    public static function phones(?string $value): array
    {
        return self::parse($value, '/\s*[,;\n]+\s*/', lowercase: false);
    }

    /**
     * @return list<string>
     */
    public static function parse(?string $value, string $separator, bool $lowercase): array
    {
        $items = preg_split($separator, trim((string) $value), -1, PREG_SPLIT_NO_EMPTY);
        $items = array_map(fn ($item) => preg_replace('/\s+/', ' ', trim($item)), $items);
        if ($lowercase) {
            $items = array_map('mb_strtolower', $items);
        }

        return array_values(array_unique($items));
    }

    public static function join(array $items): ?string
    {
        return $items ? implode(', ', $items) : null;
    }
}
