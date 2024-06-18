<?php declare(strict_types=1);

namespace Tkui\Components\Windows;

use Tkui\Components\ContainerInterface;

/**
 * The application window.
 */
interface WindowInterface extends ContainerInterface, ShowAsModalInterface
{
    /**
     * The window manager instance.
     */
    public function getWindowManager(): WindowManagerInterface;

    /**
     * Close the window.
     *
     * The window cannot be accessible anymore.
     */
    public function close(): void;

    /**
     * Set the window menu.
     *
     * Will be appeared as a menu bar in top of the window.
     */
    public function setMenu(Menu $menu): self;

    public function setPos(int $x, int $y): void;

    public function center(): void;
}