<?php declare(strict_types=1);

namespace Tkui;

interface ImageFactoryInterface
{
    public function createFromFile(string $filename): ImageInterface;

    public function createFromBinary(string $data): ImageInterface;
}
