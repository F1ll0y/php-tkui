<?php declare(strict_types=1);

namespace Tkui\Interpreter\TclTk;


use Tkui\Components\WidgetInterface;
use Tkui\Interfaces\BindingsInterface;

class TkBindingsInterface implements BindingsInterface
{
    private Interpreter $interp;
    private array $callbacks = [];

    public function __construct(Interpreter $interp)
    {
        $this->interp = $interp;
    }

    public function __destruct()
    {
        foreach ($this->callbacks as $tag => $callbacks) {
            foreach ($callbacks as $event => $_) {
                $this->deleteCallback($tag, $event);
            }
        }
    }

    /**
     * @inheritdoc
     *
     * @link https://www.tcl.tk/man/tcl8.6/TkCmd/bind.htm
     */
    public function bindWidget(WidgetInterface $widget, string $event, callable $callback): void
    {
        $tag = $widget->path();
        $this->tkBind($tag, $event, $callback);
    }

    /**
     * @inheritdoc
     */
    public function unbindWidget(WidgetInterface $widget, string $event): void
    {
        $this->deleteCallback($widget->path(), $event);
    }

    protected function tkBind(string $tag, string $event, callable $callback)
    {
        $command = $this->createTclBindCallback($tag, $event, $callback);
        if (!($event[0] === '<' && substr($event, -1, 1) === '>')) {
            $tkEvent = '<' . $event . '>';
        } else {
            $tkEvent = $event;
        }
        if($tag === "."){
            $script = sprintf('bind %s %s %s', $tag, $tkEvent, "{" . $command . " %x %y %w %h}");
        } else {
            // canvas pointer coords
            $script = sprintf('bind %s %s %s', $tag, $tkEvent, "{" . $command . " \"[%W canvasx %x]\" \"[%W canvasy %y]\"}");
        }
        $this->interp->eval($script);
        $this->callbacks[$tag][$event] = $callback;
    }

    protected function deleteCallback(string $tag, string $event)
    {
        if (isset($this->callbacks[$tag][$event])) {
            unset($this->callbacks[$tag][$event]);
            $command = $this->tclCommandName($tag, $event);
            $this->interp->deleteCommand($command);
        }
    }

    protected function createTclBindCallback(string $tag, string $event, callable $callback): string
    {
        $command = $this->tclCommandName($tag, $event);
        $this->interp->createCommand($command, $callback);
        return $command;
    }

    protected function tclCommandName(string $tag, string $event): string
    {
        return 'PHP_Bind_' . str_replace('.', '_', $tag) . '_' . $event;
    }
}