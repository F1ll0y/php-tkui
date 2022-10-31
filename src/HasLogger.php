<?php declare(strict_types=1);

namespace Tkui;

use Psr\Log\LoggerInterface;

/**
 * Adds logger support.
 */
trait HasLogger
{
    private ?LoggerInterface $logger = null;

    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    public function setLogger(?LoggerInterface $logger): static
    {
        $this->logger = $logger;
        return $this;
    }

    protected function debug(string $message, array $context = []): void
    {
        $this->logger?->debug($message, $context);
    }

    protected function info(string $message, array $context = []): void
    {
        $this->logger?->info($message, $context);
    }
}