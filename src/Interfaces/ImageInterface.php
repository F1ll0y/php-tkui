<?php declare(strict_types=1);

namespace Tkui\Interfaces;

use Stringable;

/**
 * Contract for graphic images.
 */
interface ImageInterface extends Stringable
{
    public function width(): int;

    public function height(): int;
}
