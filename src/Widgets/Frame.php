<?php declare(strict_types=1);

namespace TclTk\Widgets;

/**
 * Implementation of Tk frame widget.
 *
 * @link https://www.tcl.tk/man/tcl8.6/TkCmd/frame.htm
 */
class Frame extends TkWidget
{
    protected string $widget = 'frame';
    protected string $name = 'fr';
}