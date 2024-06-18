<?php declare(strict_types=1);

namespace Tkui\Interpreter\TclTk;

use Tkui\ImageInterface;
use Tkui\ImageFactoryInterface;

/**
 * Tk implementation of Image Factory.
 */
class TkImageFactoryInterface implements ImageFactoryInterface
{
    public function __construct(
        private readonly Interp $interp,
    ) {
    }

    public function createFromFile(string $filename): ImageInterface
    {
        $this->interp->eval(sprintf('image create photo -file {%s}', $filename));
        return $this->createImage();
    }

    public function createFromBinary(string $data): ImageInterface
    {
        $encoded = base64_encode($data);
        $this->interp->eval(sprintf('image create photo -data {%s}', $encoded));
        return $this->createImage();
    }

    protected function createImage(): ImageInterface
    {
        $id = $this->interp->getStringResult();

        return new TkImageInterface($this->interp, $id);
    }
}