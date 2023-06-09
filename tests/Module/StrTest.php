<?php

declare(strict_types=1);

namespace Fp4\PHP\Test\Module;

use Fp4\PHP\Module\PHPUnit as Assert;
use Fp4\PHP\Module\Psalm as Type;
use Fp4\PHP\Module\Str;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function Fp4\PHP\Module\Functions\pipe;

final class StrTest extends TestCase
{
    #[Test]
    public static function from(): void
    {
        pipe(
            Str\from('str'),
            Type\isSameAs('string'),
            Assert\same('str'),
        );
    }

    #[Test]
    public static function fromNonEmpty(): void
    {
        pipe(
            Str\fromNonEmpty('str'),
            Type\isSameAs('non-empty-string'),
            Assert\same('str'),
        );
    }

    #[Test]
    public static function prepend(): void
    {
        pipe(
            Str\from('val'),
            Str\prepend('pref-'),
            Type\isSameAs('non-empty-string'),
            Assert\same('pref-val'),
        );
    }
}
