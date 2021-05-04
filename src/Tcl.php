<?php declare(strict_types=1);

namespace TclTk;

use FFI;
use FFI\CData;
use TclTk\Exceptions\EvalException;
use TclTk\Exceptions\TclException;
use TclTk\Exceptions\TclInterpException;

/**
 * Low-level interface to Tcl FFI.
 */
class Tcl
{
    /**
     * Command status codes.
     */
    public const TCL_OK = 0;
    public const TCL_ERROR = 1;
    public const TCL_RETURN = 2;
    public const TCL_BREAK = 3;
    public const TCL_CONTINUE = 4;

    /**
     * @link https://www.tcl.tk/man/tcl8.6/TclLib/SetVar.htm#M5
     */
    const TCL_GLOBAL_ONLY = 1;
    const TCL_NAMESPACE_ONLY = 2;
    const TCL_APPEND_VALUE = 4;
    const TCL_LEAVE_ERR_MSG = 0x200;
    const TCL_LIST_ELEMENT = 8;

    private FFI $ffi;

    public function __construct(FFI $ffi)
    {
        $this->ffi = $ffi;
    }

    public function createInterp(): Interp
    {
        return new Interp($this, $this->ffi->Tcl_CreateInterp());
    }

    public function init(Interp $interp)
    {
        if ($this->ffi->Tcl_Init($interp->cdata()) != self::TCL_OK) {
            throw new TclException("Couldn't initialize Tcl interpretator.");
        }
    }

    /**
     * @param Interp $interp
     * @param string $script Tcl script.
     *
     * @return int Command status code.
     */
    public function eval(Interp $interp, string $script): int
    {
        $status = $this->ffi->Tcl_Eval($interp->cdata(), $script);
        if ($status != self::TCL_OK) {
            throw new EvalException($interp, $script);
        }
        return $status;
    }

    /**
     * Quote a string.
     *
     * When the string has [] characters it must be quoted otherwise
     * the data inside square brackets will be substituted by Tcl interp.
     */
    public static function quoteString(string $str): string
    {
        return '{' . $str . '}';
    }

    /**
     * Returns a string representation from Tcl_Obj structure.
     *
     * @link https://www.tcl.tk/man/tcl8.6/TclLib/StringObj.htm
     */
    public function getString($tclObj): string
    {
        return $this->ffi->Tcl_GetString($tclObj);
    }

    /**
     * Gets the Tcl eval result as a string.
     */
    public function getStringResult(Interp $interp): string
    {
        return $this->ffi->Tcl_GetString($this->ffi->Tcl_GetObjResult($interp->cdata()));
    }

    /**
     * Gets the Tcl eval result as a list of strings.
     *
     * @throws TclInterpException When FFI api call is failed.
     *
     * @return string[]
     */
    public function getListResult(Interp $interp): array
    {
        $interpCdata = $interp->cdata();
        $listObj = $this->ffi->Tcl_GetObjResult($interpCdata);
        $clen = FFI::new('int');
        if ($this->ffi->Tcl_ListObjLength($interpCdata, $listObj, FFI::addr($clen)) != self::TCL_OK) {
            throw new TclInterpException($interp, 'ListObjResult');
        }
        $len = (int) $clen->cdata;
        if ($len === 0) {
            return [];
        }
        $elements = [];
        for ($index = 0; $index < $len; $index++) {
            $elemObj = FFI::new($this->ffi->type('Tcl_Obj*'));
            if ($this->ffi->Tcl_ListObjIndex($interpCdata, $listObj, $index, FFI::addr($elemObj)) != self::TCL_OK) {
                throw new TclInterpException($interp, 'ListObjIndex');
            }
            $elements[] = $this->ffi->Tcl_GetString($elemObj);
        }
        return $elements;
    }

    /**
     * Creates a new Tcl command for the specified interpreter.
     *
     * @param Interp $interp     The TCL interpreter.
     * @param string $command    The command name.
     * @param callable $callback The command callback.
     *
     * @link https://www.tcl.tk/man/tcl8.6/TclLib/CrtObjCmd.htm
     */
    public function createCommand(Interp $interp, string $command, callable $callback)
    {
        // TODO: check return value ?
        $this->ffi->Tcl_CreateObjCommand($interp->cdata(), $command, $callback, NULL, NULL);
    }

    /**
     * @link https://www.tcl.tk/man/tcl8.6/TclLib/CrtObjCmd.htm
     * @throws TclInterpException When the command delete failed.
     */
    public function deleteCommand(Interp $interp, string $command)
    {
        if ($this->ffi->Tcl_DeleteCommand($interp->cdata(), $command) === -1) {
            throw new TclInterpException($interp, 'DeleteCommand');
        }
    }

    /**
     * Converts a PHP string to the Tcl object.
     */
    public function createStringObj(string $str): CData
    {
        return $this->ffi->Tcl_NewStringObj($str, strlen($str));
    }

    /**
     * Converts a PHP integer value to the Tcl object.
     */
    public function createIntObj(int $i): CData
    {
        return $this->ffi->Tcl_NewIntObj($i);
    }

    /**
     * Converts a PHP boolean value to the Tcl object.
     */
    public function createBoolObj(bool $b): CData
    {
        return $this->ffi->Tcl_NewBooleanObj($b);
    }

    /**
     * Converts a PHP float value to the Tcl object.
     */
    public function createFloatObj(float $f): CData
    {
        return $this->ffi->Tcl_NewDoubleObj($f);
    }

    public function getStringFromObj(CData $obj): string
    {
        return $this->ffi->Tcl_GetStringFromObj($obj, FFI::new('int*'));
    }

    public function getIntFromObj(Interp $interp, CData $obj): int
    {
        $val = FFI::new('long');
        if ($this->ffi->Tcl_GetLongFromObj($interp->cdata(), $obj, FFI::addr($val)) != self::TCL_OK) {
            throw new TclInterpException($interp, 'GetLongFromObj');
        }
        return $val->cdata;
    }

    public function getBooleanFromObj(Interp $interp, CData $obj): bool
    {
        $val = FFI::new('int');
        if ($this->ffi->Tcl_GetBooleanFromObj($interp->cdata(), $obj, FFI::addr($val)) != self::TCL_OK) {
            throw new TclInterpException($interp, 'GetBooleanFromObj');
        }
        return (bool) $val->cdata;
    }

    public function getFloatFromObj(Interp $interp, CData $obj): float
    {
        $val = FFI::new('double');
        if ($this->ffi->Tcl_GetDoubleFromObj($interp->cdata(), $obj, FFI::addr($val)) != self::TCL_OK) {
            throw new TclInterpException($interp, 'GetDoubleFromObj');
        }
        return $val->cdata;
    }

    /**
     * @param string $varName The Tcl variable name.
     * @param string|NULL $arrIndex When the variable is an array that will be the array index.
     * @param string|int|float|bool|NULL $value The variable value.
     *
     * @throws TclException      When value cannot be converted to the Tcl object.
     * @throws TclInterpException When FFI api call is failed.
     *
     * @link https://www.tcl.tk/man/tcl8.6/TclLib/SetVar.htm
     */
    public function setVar(Interp $interp, string $varName, ?string $arrIndex, $value)
    {
        if (is_string($value)) {
            $obj = $this->createStringObj($value);
        } elseif (is_int($value)) {
            $obj = $this->createIntObj($value);
        } elseif (is_float($value)) {
            $obj = $this->createFloatObj($value);
        } elseif (is_bool($value)) {
            $obj = $this->createBoolObj($value);
        } elseif ($value === NULL) {
            $obj = $this->createStringObj('');
        } else {
            throw new TclException(sprintf('Failed to convert PHP value type "%s" to Tcl object value.', gettype($value)));
        }

        $part1 = $this->createStringObj($varName);
        $part2 = $arrIndex ? $this->createStringObj($arrIndex) : NULL;
        $result = $this->ffi->Tcl_ObjSetVar2($interp->cdata(), $part1, $part2, $obj, self::TCL_LEAVE_ERR_MSG);
        if ($result === NULL) {
            throw new TclInterpException($interp, 'ObjSetVar2');
        }
        
        return $result;
    }

    /**
     * @throws TclInterpException When FFI api call is failed.
     * @link https://www.tcl.tk/man/tcl8.6/TclLib/SetVar.htm
     */
    public function getVar(Interp $interp, string $varName, ?string $arrIndex = NULL): CData
    {
        $part1 = $this->createStringObj($varName);
        $part2 = $arrIndex ? $this->createStringObj($arrIndex) : NULL;
        $result = $this->ffi->Tcl_ObjGetVar2($interp->cdata(), $part1, $part2, self::TCL_LEAVE_ERR_MSG);
        if ($result === NULL) {
            throw new TclInterpException($interp, 'ObjGetVar2');
        }
        return $result;
    }

    /**
     * @throws TclInterpException When FFI api call is failed.
     * @link https://www.tcl.tk/man/tcl8.6/TclLib/SetVar.htm
     */
    public function unsetVar(Interp $interp, string $varName, ?string $arrIndex = NULL): void
    {
        $arrIndex = $arrIndex === '' ? NULL : $arrIndex;
        $result = $this->ffi->Tcl_UnsetVar2($interp->cdata(), $varName, $arrIndex, self::TCL_LEAVE_ERR_MSG);
        if ($result !== self::TCL_OK) {
            throw new TclInterpException($interp, 'UnsetVar2');
        }
    }

    /**
     * Converts a PHP array to a Tcl list.
     */
    public static function arrayToList(array $input): string
    {
        return '{' . implode(' ', array_map([self::class, 'quoteString'], $input)) . '}';
    }
}
