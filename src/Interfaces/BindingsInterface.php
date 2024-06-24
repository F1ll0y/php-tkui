<?php declare(strict_types=1);

namespace Tkui\Interfaces;

use Tkui\Components\WidgetInterface;

/**
 * Let you attach/detach widget bindings.
 */
interface BindingsInterface
{
    /**
     * Attach the binding to the widget.
     */
    public function bindWidget(WidgetInterface $widget, string $event, callable $callback): void;

    /**
     * Detach the widget binding.
     */
    public function unbindWidget(WidgetInterface $widget, string $event): void;
}