<?php declare(strict_types=1);

namespace Tkui;

/**
 * The application instance.
 */
interface ApplicationInterface extends EvaluatorInterface, BindingsInterface
{
    /**
     * Process the application events.
     */
    public function run(): void;

    /**
     * Get the theme manager.
     */
    public function getThemeManager(): ThemeManagerInterface;

    /**
     * Get the font manager.
     */
    public function getFontManager(): FontManagerInterface;

    /**
     * Image factory.
     */
    public function getImageFactory(): ImageFactoryInterface;

    /**
     * Stop the application and free up resources.
     */
    public function quit(): void;

    public function addTimeout(int $delay, \Closure $cb): void;
}