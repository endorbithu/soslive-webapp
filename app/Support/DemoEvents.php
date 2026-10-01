<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * Demó események a végoldal (`/e/{id}`) Google nélküli teszteléséhez – csak nem production környezetben.
 * Ugyanazt adják vissza, amit a Google Drive API adna: metaadatot (`name`, `modifiedTime`, `trashed`) és a
 * docs/EVENT_FORMAT.md szerinti JSON tartalmat.
 */
class DemoEvents
{
    public const PREFIX = 'demo-';

    /** Élő esemény: 30 másodpercenként új pozíció jön, így a polling is kipróbálható. */
    public const LIVE = 'demo-live-event-0000001';

    /** Lezárt esemény: stream, útvonal, üzenetek, képek. */
    public const ENDED = 'demo-ended-event-000001';

    /** Most indult esemény: még nincs stream és bejegyzés. */
    public const EMPTY = 'demo-empty-event-000001';

    /** Törölt esemény: a végoldalon „nem érhető el”. */
    public const DELETED = 'demo-deleted-event-0001';

    private const STREAM = 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8';

    /**
     * A dashboardon listázott demó események.
     *
     * @return list<array{id: string, label: string}>
     */
    public static function all(): array
    {
        return [
            ['id' => self::LIVE, 'label' => 'Élő esemény (30 mp-enként frissül)'],
            ['id' => self::ENDED, 'label' => 'Lezárt esemény'],
            ['id' => self::EMPTY, 'label' => 'Most indult esemény (még üres)'],
            ['id' => self::DELETED, 'label' => 'Törölt esemény'],
        ];
    }

    /**
     * @return array{name: string, modifiedTime: string, trashed: bool, content: array<string, mixed>}|null
     */
    public static function find(string $id, ?Carbon $now = null): ?array
    {
        $now = ($now ?? Carbon::now())->copy()->utc();

        return match ($id) {
            self::LIVE => self::live($now),
            self::ENDED => self::ended(),
            self::EMPTY => self::event('2026-09-29 14:03:22', '2026-09-29T14:03:22Z', ['v' => 1, 'stream' => '', 'entries' => []]),
            default => null, // a törölt és az ismeretlen esemény is 404, mint a Drive-on
        };
    }

    private static function live(Carbon $now): array
    {
        // 30 másodperces lépésekre kerekítve: a fájl ugyanabban a 30 mp-es ablakban nem változik, utána új pozíció jön.
        $anchor = $now->copy()->setTimestamp(intdiv($now->getTimestamp(), 30) * 30);
        $start = $anchor->copy()->subMinutes(15);

        $entries = [];
        for ($k = 0; $k <= 30; $k++) {
            $t = $start->copy()->addSeconds($k * 30);
            // Kör a belváros körül (óránként egy kör): a pozíció az abszolút időtől függ, így minden lépésnél változik.
            $angle = 2 * M_PI * (intdiv($t->getTimestamp(), 30) % 120) / 120;
            $entries[] = self::pos($t, 47.4979 + 0.006 * sin($angle), 19.0502 + 0.009 * cos($angle));

            if ($k === 4) {
                $entries[] = self::msg($t->copy()->addSeconds(5), 'Anna', 'Elindultam haza, kísérjetek figyelemmel.');
            }
            if ($k === 12) {
                $entries[] = self::img($t->copy()->addSeconds(10), 'https://picsum.photos/seed/soslive-1/640/360');
            }
            if ($k === 20) {
                $entries[] = self::msg($t->copy()->addSeconds(5), 'Anna', 'Valaki követ, a sarkon befordulok.');
            }
        }

        return self::event($start->format('Y-m-d H:i:s'), $anchor->toIso8601ZuluString(), [
            'v' => 1,
            'stream' => self::STREAM,
            'entries' => $entries,
        ]);
    }

    private static function ended(): array
    {
        $t = Carbon::parse('2026-09-28T19:12:05Z');
        $entries = [];
        foreach ([[47.5136, 19.0353], [47.5141, 19.0368], [47.5150, 19.0381], [47.5162, 19.0390]] as $i => [$lat, $lng]) {
            $entries[] = self::pos($t->copy()->addSeconds($i * 30), $lat, $lng);
        }
        $entries[] = self::msg($t->copy()->addSeconds(100), 'Anna', 'Minden rendben, hazaértem.');
        $entries[] = self::img($t->copy()->addSeconds(110), 'https://picsum.photos/seed/soslive-2/640/360');
        // Formailag hibás bejegyzések: a végoldalnak ki kell hagynia őket.
        $entries[] = ['t' => '2026-09-28T19:14:10Z', 'type' => 'img', 'url' => 'javascript:alert(1)'];
        $entries[] = ['t' => '2026-09-28T19:14:12Z', 'type' => 'unknown-type'];
        $entries[] = self::msg($t->copy()->addSeconds(130), '<b>HTML</b>', '<script>alert(1)</script> – szövegként kell megjelennie');

        return self::event('2026-09-28 19:12:05', '2026-09-28T19:14:15Z', [
            'v' => 1,
            'stream' => self::STREAM,
            'entries' => $entries,
        ]);
    }

    private static function event(string $start, string $modified, array $content): array
    {
        return ['name' => $start.'.json', 'modifiedTime' => $modified, 'trashed' => false, 'content' => $content];
    }

    private static function pos(Carbon $t, float $lat, float $lng): array
    {
        return ['t' => $t->toIso8601ZuluString(), 'type' => 'pos', 'lat' => round($lat, 6), 'lng' => round($lng, 6)];
    }

    private static function msg(Carbon $t, string $name, string $text): array
    {
        return ['t' => $t->toIso8601ZuluString(), 'type' => 'msg', 'name' => $name, 'text' => $text];
    }

    private static function img(Carbon $t, string $url): array
    {
        return ['t' => $t->toIso8601ZuluString(), 'type' => 'img', 'url' => $url];
    }
}
