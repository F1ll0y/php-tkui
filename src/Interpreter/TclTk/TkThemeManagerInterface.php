<?php declare(strict_types=1);

namespace Tkui\Interpreter\TclTk;


use Tkui\Interfaces\ThemeManagerInterface;

/**
 * Implementation of Ttk style themes.
 *
 * @link https://www.tcl.tk/man/tcl8.6/TkCmd/ttk_style.htm
 */
class TkThemeManagerInterface implements ThemeManagerInterface
{
    public function __construct(
        private readonly Interpreter $interp,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function themes(): array
    {
        $this->call('theme', 'names');
        return $this->interp->getListResult();
    }

    /**
     * @inheritdoc
     */
    public function useTheme(string $theme): self
    {
        $this->call('theme', 'use', Tcl::quoteString($theme));
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function currentTheme(): string
    {
        $this->call('theme', 'use');
        return $this->interp->getStringResult();
    }

    protected function call(...$args)
    {
        $this->interp->eval('ttk::style ' . implode(' ', $args));
    }
}