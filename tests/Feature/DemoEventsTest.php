<?php

namespace Tests\Feature;

use App\Support\DemoEvents;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DemoEventsTest extends TestCase
{
    use RefreshDatabase;

    private function asProduction(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
    }

    public function test_demo_ids_are_valid_event_ids(): void
    {
        foreach (DemoEvents::all() as $demo) {
            $this->assertMatchesRegularExpression('/^demo-[A-Za-z0-9_-]{15,95}$/', $demo['id']);
            $this->get(route('event', $demo['id']))->assertOk()->assertSee($demo['id']);
        }
    }

    public function test_metadata_and_content_like_drive(): void
    {
        $this->getJson(route('dev.drive', DemoEvents::ENDED).'?fields=name,modifiedTime,trashed')
            ->assertOk()
            ->assertExactJson([
                'name' => '2026-09-28 19:12:05.json',
                'modifiedTime' => '2026-09-28T19:14:15Z',
                'trashed' => false,
            ]);

        $this->getJson(route('dev.drive', DemoEvents::ENDED).'?alt=media')
            ->assertOk()
            ->assertJsonPath('v', 1)
            ->assertJsonPath('entries.0.type', 'pos')
            ->assertJsonPath('stream', 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8');

        $this->getJson(route('dev.drive', DemoEvents::EMPTY).'?alt=media')
            ->assertOk()
            ->assertExactJson(['v' => 1, 'stream' => '', 'entries' => []]);
    }

    public function test_live_event_changes_every_30_seconds(): void
    {
        $at = fn (string $time) => DemoEvents::find(DemoEvents::LIVE, Carbon::parse($time));

        $a = $at('2026-10-01T12:00:01Z');
        $this->assertSame('2026-10-01T12:00:00Z', $a['modifiedTime']);
        $this->assertSame($a, $at('2026-10-01T12:00:29Z'));

        $b = $at('2026-10-01T12:00:30Z');
        $this->assertSame('2026-10-01T12:00:30Z', $b['modifiedTime']);
        $this->assertSame('2026-10-01T12:00:30Z', last($b['content']['entries'])['t']);
        $this->assertNotEquals(
            array_slice(last($a['content']['entries']), 2),
            array_slice(last($b['content']['entries']), 2),
            'az utolsó pozíció 30 mp múlva máshol van',
        );

        $types = array_count_values(array_column($b['content']['entries'], 'type'));
        $this->assertSame(['pos' => 31, 'msg' => 2, 'img' => 1], $types);
    }

    public function test_deleted_and_unknown_ids_are_404(): void
    {
        $this->getJson(route('dev.drive', DemoEvents::DELETED))->assertNotFound()->assertJsonPath('error.code', 404);
        $this->getJson(route('dev.drive', 'demo-nincs-ilyen-esemeny-01'))->assertNotFound();
    }

    public function test_demo_config_and_links_only_outside_production(): void
    {
        $this->get('/')->assertSee('"demoUrl":"\/app\/dev\/drive\/files"', false)->assertSee('/e/'.DemoEvents::LIVE);
        $this->get('/dashboard')->assertSee('Demó események');

        $this->asProduction();
        $this->get('/')->assertDontSee('demoUrl')->assertDontSee('Demó események');
        $this->get('/dashboard')->assertDontSee('Demó események');
        $this->getJson(route('dev.drive', DemoEvents::ENDED))->assertNotFound();
    }
}
