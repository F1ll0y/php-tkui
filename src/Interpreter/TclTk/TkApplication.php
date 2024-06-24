<?php declare(strict_types=1);

namespace Tkui\Interpreter\TclTk;

use Stringable;
use Tkui\Components\WidgetInterface;
use Tkui\Interfaces\ApplicationInterface;
use Tkui\Interfaces\BindingsInterface;
use Tkui\Interfaces\FontManagerInterface;
use Tkui\Interfaces\ImageFactoryInterface;
use Tkui\Interfaces\ThemeManagerInterface;
use Tkui\TclTk\Exceptions\TclException;
use Tkui\TclTk\Exceptions\TclInterpException;
use Tkui\TclTk\Exceptions\TkException;
use Tkui\Traits\WithLogger;
use Tkui\Widgets\Widget;

/**
 * Main application.
 */
class TkApplication implements ApplicationInterface
{
    use WithLogger;

    private Tk $tk;
    private Interpreter $interpreter;
    private BindingsInterface $bindings;
    private ?TkThemeManagerInterface $themeManager;
    private TkFontManager $fontManager;
    private TkImageFactoryInterface $imageFactory;

    /**
     * @var Variable[]
     */
    private array $vars;

    /**
     * Widgets callbacks.
     *
     * Index is the widget path and value - the callback function.
     *
     * @var array<string, array{Widget, callable}>
     */
    private array $callbacks;

    /**
     * @var array<string, string|int|bool>
     */
    private array $argv = [];

    /**
     * @todo Create a namespace for window callbacks handler.
     */
    private const CALLBACK_HANDLER = 'PHP_tk_ui_Handler';

    /**
     * @param array $argv Arguments and values for the global "argv" interp variable.
     */
    public function __construct(Tk $tk, array $argv = [])
    {
        $this->tk = $tk;
        $this->argv = $argv;
        $this->interpreter = $tk->interp();
        $this->bindings = $this->createBindings();
        $this->themeManager = null;
        $this->fontManager = $this->createFontManager();
        $this->imageFactory = $this->createImageFactory();
        $this->vars = [];
        $this->callbacks = [];
        $this->createCallbackHandler();
    }

    protected function createBindings(): BindingsInterface
    {
        return new TkBindingsInterface($this->interpreter);
    }

    protected function createFontManager(): TkFontManager
    {
        return new TkFontManager($this->interpreter);
    }

    protected function createImageFactory(): TkImageFactoryInterface
    {
        return new TkImageFactoryInterface($this->interpreter);
    }

    /**
     * @inheritdoc
     */
    public function tclEval(...$args): string
    {
        // TODO: to improve performance not all the arguments should be quoted
        // but only those which are parameters. But this requires a new method
        // like this: tclCall($command, $method, ...$args)
        // and only $args must be quoted.
        $script = implode(' ', array_map(fn($arg) => $this->encloseArg($arg), $args));
        $this->interpreter->eval($script);

        return $this->interpreter->getStringResult();
    }

    /**
     * Initialization of ttk package.
     */
    protected function initTtk(): void
    {
        try {
            $this->interpreter->eval('package require Ttk');
            $this->themeManager = $this->createThemeManager();
        } catch (TclInterpException $e) {
            // TODO: ttk must be required ?
            $this->themeManager = null;
            $this->error('initTtk: ' . $e->getMessage());
        }
    }

    protected function createThemeManager(): TkThemeManagerInterface
    {
        return new TkThemeManagerInterface($this->interpreter);
    }

    /**
     * The application has ttk support.
     */
    public function hasTtk(): bool
    {
        return $this->themeManager !== null;
    }

    /**
     * Encloses the argument in the curly brackets.
     *
     * This function automatically detects when the argument
     * should be enclosed in curly brackets.
     *
     * @param mixed $arg
     * @see App::tclEval()
     *
     */
    protected function encloseArg($arg): string
    {
        if (is_string($arg)) {
            $chr = $arg[0];
            if ($chr === '"' || $chr === "'" || $chr === '{' || $chr === '[') {
                return $arg;
            }
            return (strpos($arg, ' ') === FALSE && strpos($arg, "\n") === FALSE) ? $arg : Tcl::quoteString($arg);
        } elseif (is_array($arg)) {
            // TODO: deep into $arg to check nested array.
            $arg = '{' . implode(' ', $arg) . '}';
        }
        return (string)$arg;
    }

    /**
     * Initializes Tcl and Tk libraries.
     */
    public function init(): void
    {
        $this->debug('app init');
        $this->interpreter->init();
        $this->setInterpArgv();
        $this->tk->init();
        $this->initTtk();
        $this->debug('end app init');
    }

    protected function setInterpArgv(): void
    {
        foreach ($this->argv as $arg => $value) {
            $this->interpreter->argv()->append($arg, $value);
        }
    }

    /**
     * Application's the main loop.
     *
     * Will process all the app events.
     */
    public function run(): void
    {
        $this->debug('run');
        while (1) {
            $this->tk->mainLoop();
            $this->checkTimers();

            usleep(1);
        }
    }

    public function tk(): Tk
    {
        return $this->tk;
    }

    /**
     * Quits the application and deletes all the widgets.
     */
    public function quit(): void
    {
        $this->debug('destroy');
        $this->tclEval('destroy', '.');
    }

    /**
     * Sets the widget binding.
     */
    public function bindWidget(WidgetInterface $widget, $event, $callback): void
    {
        $this->bindings->bindWidget($widget, $event, $callback);
    }

    /**
     * Unbinds the event from the widget.
     */
    public function unbindWidget(WidgetInterface $widget, $event): void
    {
        $this->bindings->unbindWidget($widget, $event);
    }

    public function bindings(): BindingsInterface
    {
        return $this->bindings;
    }

    /**
     * @throws TkException When ttk is not supported.
     */
    public function getThemeManager(): ThemeManagerInterface
    {
        if ($this->hasTtk()) {
            return $this->themeManager;
        }
        throw new TkException('ttk is not supported.');
    }

    protected function createCallbackHandler()
    {
        $this->interpreter->createCommand(self::CALLBACK_HANDLER, function (...$args) {
            $path = array_shift($args);
            // TODO: check if arguments are empty ?
            [$callback, $widget] = $this->callbacks[$path];
            array_unshift($args, $widget);
            return $callback(...$args);
        });
    }

    public function registerVar(Stringable|string $varName): Variable
    {
        $index = (string)$varName;
        if (!isset($this->vars[$index])) {
            // TODO: variable in namespace ?
            // TODO: generate an array index for access performance.
            $this->vars[$index] = $this->interpreter->createVariable($index);
        }
        return $this->vars[$index];
    }

    public function unregisterVar(Stringable|string $varName): void
    {
        $index = (string)$varName;
        if (!isset($this->vars[$index])) {
            throw new TclException(sprintf('Variable "%s" is not registered.', $index));
        }
        // Implicitly call of Variable's __destruct().
        unset($this->vars[$index]);
    }

    /**
     * @inheritdoc
     */
    public function registerCallback(Widget $widget, callable $callback, array $args = [], string $commandName = ''): string
    {
        // TODO: it would be better to use WeakMap.
        //       in that case it will be like this:
        //       $this->callbacks[$widget] = $callback;
        $index = $this->getWidgetCallbackIndex($widget, $commandName);
        $this->callbacks[$index] = [$callback, $widget];
        return trim(self::CALLBACK_HANDLER . ' ' . $index . ' ' . implode(' ', $args));
    }

    /**
     * @inheritdoc
     */
    public function unregisterCallback(Widget $widget, string $commandName = ''): void
    {
        $index = $this->getWidgetCallbackIndex($widget, $commandName);
        if (isset($this->callbacks[$index])) {
            unset($this->callbacks[$index]);
        }
    }

    private function getWidgetCallbackIndex(Widget $widget, string $commandName): string
    {
        return $widget . ($commandName ? '-' . $commandName : '');
    }

    /**
     * @inheritdoc
     */
    public function getFontManager(): FontManagerInterface
    {
        return $this->fontManager;
    }

    /**
     * Returns the name of gui type.
     */
    public function getGuiType(): GuiType
    {
        return GuiType::fromString((string)$this->tclEval('tk', 'windowingsystem'));
    }

    /**
     * Sets the gui scaling factor.
     *
     * @link https://www.tcl.tk/man/tcl8.6/TkCmd/tk.htm#M10
     */
    public function setScaling(float $value): static
    {
        $this->tclEval('tk', 'scaling', $value);
        return $this;
    }

    /**
     * Gets the gui scaling factor.
     *
     * @link https://www.tcl.tk/man/tcl8.6/TkCmd/tk.htm#M10
     */
    public function getScaling(): float
    {
        return (float)$this->tclEval('tk', 'scaling');
    }

    /**
     * @inheritdoc
     */
    public function getImageFactory(): ImageFactoryInterface
    {
        return $this->imageFactory;
    }


    /*
     *
     * Timing Functions
     *
     *
     *
     */
    private $timers = [];

    private function checkTimers()
    {
        $time = (int)(microtime(true) * 1000);
        $deleteTimer = [];
        foreach ($this->timers as $timerId => $timer) {
            if ($timer['target'] > $time) {
                continue;
            }

            if ($timer['type'] === "interval") {
                $timer['target'] = $time + $timer['delay'];
            } else {
                $deleteTimer[] = $timerId;
            }

            call_user_func($timer['cb']);
        }

        if(count($deleteTimer) > 0) {
            foreach ($deleteTimer as $timerId) {
                unset($this->timers[$timerId]);
            }
        }
    }

    public function addTimeout(int $delay, \Closure $cb): void
    {
        $this->addTimer("timeout", $delay, $cb);
    }

    public function addInterval(int $delay, \Closure $cb): void
    {
        $this->addTimer("interval", $delay, $cb);
    }

    public function addTimer(string $type, int $delay, \Closure $cb): void
    {
        $this->timers[] = ['type' => $type, 'delay' => $delay, 'target' => (int)(microtime(true) * 1000) + $delay, 'cb' => $cb];
    }
}
