<?php declare(strict_types=1);

namespace Tkui\Interpreter\TclTk;


use Tkui\Interfaces\ImageInterface;

/**
 * Tk implementation of Image.
 */
class TkImageInterface implements ImageInterface
{
    public function __construct(
        private readonly Interpreter $interp,
        public readonly string       $id,
    ) {
    }

    public function width(): int
    {
        $this->interp->eval("image width {$this->id}");
        return (int) $this->interp->getStringResult();
    }

    public function height(): int
    {
        $this->interp->eval("image height {$this->id}");
        return (int) $this->interp->getStringResult();
    }

    public function __toString(): string
    {
        return $this->id;
    }
}