<?php declare(strict_types=1);

namespace Tkui\Components;

use Stringable;
use Tkui\Options;
use Tkui\Widgets\Container;

/**
 * Basic widget.
 */
interface WidgetInterface extends Stringable
{
    /**
     * Widget's path hierarchy including its id.
     */
    public function path(): string;

    /**
     * Unique widget id (without hierarchy and leading dot).
     */
    public function id(): string;

    /**
     * Parent widget.
     *
     * The last widget in the chain must be Window.
     */
    public function parent(): Container;
}