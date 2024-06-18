<?php declare(strict_types=1);

namespace Tkui\Components\Windows;

/**
 * Window or widget that can be shown as modals.
 */
interface ShowAsModalInterface
{
    /**
     * @return mixed The modal result.
     */
    public function showModal(): void;
}
