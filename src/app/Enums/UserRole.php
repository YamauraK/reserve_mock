<?php

namespace App\Enums;

final class UserRole
{
    public const HQ = 'hq'; // 本店（全権）
    public const STORE = 'store'; // 店舗（自店舗のみ等）

    public static function labels(): array
    {
        return [
            self::HQ => '本店',
            self::STORE => '店舗',
        ];
    }

    public static function options(): array
    {
        return array_map(
            fn($k, $v) => ['value' => $k, 'label' => $v],
            array_keys(self::labels()),
            self::labels()
        );
    }
}
