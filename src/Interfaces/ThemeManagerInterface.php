<?php declare(strict_types=1);

namespace Tkui\Interfaces;

/**
 * The application theme manager.
 */
interface ThemeManagerInterface
{
    /**
     * Returns the list of available themes.
     *
     * return string[]
     */
    public function themes(): array;

    /**
     * Current theme name.
     */
    public function currentTheme(): string;

    /**
     * Switch to the specified theme.
     */
    public function useTheme(string $theme): self;
}