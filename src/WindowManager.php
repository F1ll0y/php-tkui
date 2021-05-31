<?php declare(strict_types=1);

namespace TclTk;

/**
 * The application window manager.
 */
interface WindowManager
{
    /**
     * Sets the window title.
     */
    public function setTitle(string $title): self;

    /**
     * Sets the window state: normal, iconic, withdrawn, icon, zoomed.
     * But depends on underlying window manager.
     */
    public function setState(string $state): self;

    /**
     * Gets the window state.
     *
     * @see WindowManager::setState()
     */
    public function getState(): string;

    /**
     * Arrange for window to be iconified.
     */
    public function iconify(): self;

    /**
     * Arrange for window to be displayed in normal (non-iconified) form.
     */
    public function deiconify(): self;

    /**
     * Sets the maximum dimensions for the window.
     */
    public function setMaxSize(int $width, int $height): self;

    /**
     * Gets the maximum dimensions for the window.
     *
     * @return int[] The list of two width and height values.
     */
    public function getMaxSize(): array;

    /**
     * Sets the minimum dimensions for the window.
     */
    public function setMinSize(int $width, int $height): self;

    /**
     * Gets the minimum dimensions for the window.
     *
     * @return int[] The list of two width and height values.
     */
    public function getMinSize(): array;

    /**
     * Sets platform specific attribute associated with the window.
     *
     * @param mixed $value Depending on attribute.
     */
    public function setAttribute(string $attribute, $value): self;

    /**
     * Gets platform specific attribute associated with the window.
     *
     * @return mixed
     */
    public function getAttribute(string $attribute);

    /**
     * Places the window in a mode that takes up the entire screen, has no borders, and covers the general use area.
     */
    public function setFullScreen(): self;

    /**
     * Sets the window size.
     */
    public function setSize(int $width, int $height): self;

    /**
     * Gets the window size.
     *
     * @return int[] The list of width and height.
     */
    public function getSize(): array;

    /**
     * Sets the window position on the screen.
     */
    public function setPos(int $x, int $y): self;

    /**
     * Gets the window position on the screen.
     *
     * @return int[] The list of x and y window position.
     */
    public function getPos(): array;
}