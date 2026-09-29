<?php

namespace Tests\Unit;

use App\Support\ListField;
use PHPUnit\Framework\TestCase;

class ListFieldTest extends TestCase
{
    public function test_emails_are_split_normalized_and_deduped(): void
    {
        $this->assertSame(['a@x.hu', 'b@y.hu'], ListField::emails(" A@x.hu, b@y.hu;\na@x.hu "));
        $this->assertSame([], ListField::emails(null));
    }

    public function test_phones_keep_inner_spaces(): void
    {
        $this->assertSame(['+36 20 123 4567', '+36301234567'], ListField::phones(" +36 20  123 4567 ,+36301234567\n"));
    }

    public function test_join(): void
    {
        $this->assertSame('a, b', ListField::join(['a', 'b']));
        $this->assertNull(ListField::join([]));
    }
}
