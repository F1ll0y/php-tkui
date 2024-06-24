<?php declare(strict_types=1);

namespace Tkui\Interpreter\TclTk;

use FFI;
use FFI\CData;
use Tkui\Interpreter\TclTk\Exceptions\EvalException;
use Tkui\Interpreter\TclTk\Exceptions\TclException;
use Tkui\Interpreter\TclTk\Exceptions\TclInterpException;

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

    public function __construct(
        private readonly FFI $ffi,
    ) {
    }

    public function createInterpreter(): Interpreter
    {
        return new Interpreter($this, $this->ffi->Tcl_CreateInterp());
    }

    /**
     * @throws TclException
     */
    public function init(Interpreter $interpreter): void
    {
        $ret = $this->ffi->Tcl_Init($interpreter->cdata());
        if ($this->ffi->Tcl_Init($interpreter->cdata()) != self::TCL_OK) {
            throw new TclException("Couldn't initialize Tcl interpretator. (Return: ".$ret.")");
        }
    }

    public function doOneEvent(): void
    {
        $this->ffi->Tcl_DoOneEvent(1<<1);
    }

    /**
     * @param Interpreter $interp
     * @param string $script Tcl script.
     *
     * @return int Command status code.
     * @throws EvalException
     */
    public function eval(Interpreter $interp, string $script): int
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
    public function getString(CData $tclObj): string
    {
        return $this->ffi->Tcl_GetString($tclObj);
    }

    /**
     * Gets the Tcl eval result as a string.
     */
    public function getStringResult(Interpreter $interp): string
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
    public function getListResult(Interpreter $interp): array
    {
        $listObj = $this->ffi->Tcl_GetObjResult($interp->cdata());

        if (($len = $this->getListLength($interp, $listObj)) === 0) {
            return [];
        }

        $elements = [];
        for ($index = 0; $index < $len; $index++) {
            $elemObj = $this->getListIndex($interp, $listObj, $index);
            $elements[] = $this->ffi->Tcl_GetString($elemObj);
        }

        return $elements;
    }

    /**
     * Sets the Tcl result.
     *
     * @link https://www.tcl.tk/man/tcl8.6/TclLib/SetResult.html
     */
    public function setResult(Interpreter $interp, mixed $result): void
    {
        $this->ffi->Tcl_SetObjResult($interp->cdata(), $this->phpValueToObj($result));
    }

    /**
     * Creates a new Tcl command for the specified interpreter.
     *
     * @param Interpreter $interp     The TCL interpreter.
     * @param string $command    The command name.
     * @param callable $callback The command callback.
     *
     * @link https://www.tcl.tk/man/tcl8.6/TclLib/CrtObjCmd.htm
     */
    public function createCommand(Interpreter $interp, string $command, callable $callback)
    {
        // TODO: check return value ?
        $this->ffi->Tcl_CreateObjCommand($interp->cdata(), $command, $callback, NULL, NULL);
    }

    /**
     * @link https://www.tcl.tk/man/tcl8.6/TclLib/CrtObjCmd.htm
     * @throws TclInterpException When the command delete failed.
     */
    public function deleteCommand(Interpreter $interp, string $command)
    {
        // TODO: check the return value, why it's "-1" not TCL_OK ?
        if ($this->ffi->Tcl_DeleteCommand($interp->cdata(), $command) === -1) {
            $interp->throwInterpException('DeleteCommand');
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

    public function createListObj(): CData
    {
        return $this->ffi->Tcl_NewListObj(0, NULL);
    }

    public function getStringFromObj(CData $obj): string
    {
        return $this->ffi->Tcl_GetStringFromObj($obj, FFI::new('int*'));
    }

    /**
     * @throws TclInterpException
     */
    public function getIntFromObj(Interpreter $interp, CData $obj): int
    {
        $val = FFI::new('long');
        if ($this->ffi->Tcl_GetLongFromObj($interp->cdata(), $obj, FFI::addr($val)) != self::TCL_OK) {
            $interp->throwInterpException('GetLongFromObj');
        }
        return $val->cdata;
    }

    /**
     * @throws TclInterpException
     */
    public function getBooleanFromObj(Interpreter $interp, CData $obj): bool
    {
        $val = FFI::new('int');
        if ($this->ffi->Tcl_GetBooleanFromObj($interp->cdata(), $obj, FFI::addr($val)) != self::TCL_OK) {
            $interp->throwInterpException('GetBooleanFromObj');
        }
        return (bool) $val->cdata;
    }

    /**
     * @throws TclInterpException
     */
    public function getFloatFromObj(Interpreter $interp, CData $obj): float
    {
        $val = FFI::new('double');
        if ($this->ffi->Tcl_GetDoubleFromObj($interp->cdata(), $obj, FFI::addr($val)) != self::TCL_OK) {
            $interp->throwInterpException('GetDoubleFromObj');
        }
        return $val->cdata;
    }

    /**
     * @param string|int|float|bool|CData|null $value
     *
     * @throws TclInterpException
     */
    public function addListElement(Interpreter $interp, CData $listObj, $value): void
    {
        $obj = $this->phpValueToObj($value);
        if ($this->ffi->Tcl_ListObjAppendElement(null, $listObj, $obj) != self::TCL_OK) {
            $interp->throwInterpException('Tcl_ListObjAppendElement');
        }
    }

    /**
     * @throws TclInterpException
     */
    public function getListLength(Interpreter $interp, CData $listObj): int
    {
        $len = FFI::new('int');
        if ($this->ffi->Tcl_ListObjLength($interp->cdata(), $listObj, FFI::addr($len)) != self::TCL_OK) {
            $interp->throwInterpException('ListObjLength');
        }
        return $len->cdata;
    }

    /**
     * @throws TclInterpException
     */
    public function getListIndex(Interpreter $interp, CData $listObj, int $index): CData
    {
        $result = $this->ffi->new('Tcl_Obj*');
        if ($this->ffi->Tcl_ListObjIndex($interp->cdata(), $listObj, $index, FFI::addr($result)) != self::TCL_OK) {
            $interp->throwInterpException('ListObjIndex');
        }
        return $result;
    }

    /**
     * Converts a PHP value to Tcl Obj structure.
     *
     * @throws TclException When PHP value cannot be converted.
     */
    public function phpValueToObj(mixed $value): CData
    {
        return match (true) {
            is_object($value) => $this->phpObjectToTclObj($value),
            is_string($value) => $this->createStringObj($value),
            is_int($value)    => $this->createIntObj($value),
            is_float($value)  => $this->createFloatObj($value),
            is_bool($value)   => $this->createBoolObj($value),
            is_array($value)  => throw new TclException('Array cannot be converted to Tcl Obj'),
            $value === null   => $this->createStringObj(''),
        };
    }

    /**
     * Converts PHP object value to Tcl object.
     *
     * Warning:
     * Currently only CData object can be used otherwise it returns a Tcl empty Obj instance.
     */
    private function phpObjectToTclObj(object $object): CData
    {
        if ($object instanceof CData) {
            return $object;
        }

        // FIXME: Any other object cannot be converted to Tcl one.
        return $this->createStringObj('');
    }

    /**
     * @param string $varName The Tcl variable name.
     * @param string|NULL $arrIndex When the variable is an array that will be the array index.
     * @param string|int|float|bool|NULL|CData $value The variable value.
     *
     * @throws TclInterpException When FFI api call is failed.
     *
     * @link https://www.tcl.tk/man/tcl8.6/TclLib/SetVar.htm
     */
    public function setVar(Interpreter $interp, string $varName, ?string $arrIndex, $value)
    {
        $obj = $this->phpValueToObj($value);
        $part1 = $this->createStringObj($varName);
        $part2 = $arrIndex ? $this->createStringObj($arrIndex) : NULL;
        $result = $this->ffi->Tcl_ObjSetVar2($interp->cdata(), $part1, $part2, $obj, self::TCL_LEAVE_ERR_MSG);
        if ($result === NULL) {
            $interp->throwInterpException('ObjSetVar2');
        }
        return $result;
    }

    /**
     * @throws TclInterpException When FFI api call is failed.
     * @link https://www.tcl.tk/man/tcl8.6/TclLib/SetVar.htm
     */
    public function getVar(Interpreter $interp, string $varName, ?string $arrIndex = NULL): CData
    {
        $part1 = $this->createStringObj($varName);
        $part2 = $arrIndex ? $this->createStringObj($arrIndex) : NULL;
        $result = $this->ffi->Tcl_ObjGetVar2($interp->cdata(), $part1, $part2, self::TCL_LEAVE_ERR_MSG);
        if ($result === NULL) {
            $interp->throwInterpException('ObjGetVar2');
        }
        return $result;
    }

    /**
     * @throws TclInterpException When FFI api call is failed.
     * @link https://www.tcl.tk/man/tcl8.6/TclLib/SetVar.htm
     */
    public function unsetVar(Interpreter $interp, string $varName, ?string $arrIndex = NULL): void
    {
        $arrIndex = $arrIndex === '' ? NULL : $arrIndex;
        $result = $this->ffi->Tcl_UnsetVar2($interp->cdata(), $varName, $arrIndex, self::TCL_LEAVE_ERR_MSG);
        if ($result !== self::TCL_OK) {
            $interp->throwInterpException('UnsetVar2');
        }
    }

    /**
     * Converts a PHP array to a Tcl list.
     */
    public static function arrayToList(array $input): string
    {
        return '{' . implode(' ', array_map([self::class, 'quoteString'], $input)) . '}';
    }

    /**
     * Formats string to Tcl option.
     *
     * The Tcl option is a lower case string with leading dash.
     */
    public static function strToOption(string $name): string
    {
        return '-' . strtolower($name);
    }
}
