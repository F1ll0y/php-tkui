<?php declare(strict_types=1);

namespace Tkui\Interpreter\TclTk;

use FFI;
use FFI\CData;
use Tkui\TclTk\Exceptions\TkException;

/**
 * Low-level interface to Tk FFI.
 */
class Tk
{
    private readonly CData $tkInterp;
    private readonly Interpreter $tclInterp;

    /**
     * @param FFI    $ffi    FFI to Tk library.
     * @param Interpreter $interp Tcl interpreter.
     */
    public function __construct(
        private readonly FFI $ffi,
        Interpreter          $interp,
    ) {
        $this->tclInterp = $interp;
        $this->tkInterp = $ffi->cast($ffi->type('Tcl_Interp*'), $interp->cdata());
    }

    public function init(): void
    {
        if ($this->ffi->Tk_Init($this->tkInterp) !== Tcl::TCL_OK) {
            throw new TkException("Couldn't init Tk library.");
        }
    }

    public function mainLoop(): void
    {
        $this->tclInterp->doOneEvent();
        //$this->ffi->Tk_MainLoop();
    }

    public function interp(): Interpreter
    {
        return $this->tclInterp;
    }

    public function mainWindow()
    {
        return $this->ffi->Tk_MainWindow($this->tkInterp);
    }

    public function destroy($win)
    {
        $this->ffi->Tk_DestroyWindow($win);
    }

    public function nameToWindow(string $pathName, $win)
    {
        $this->ffi->Tk_NameToWindow($this->tkInterp, $pathName, $win);
    }
}