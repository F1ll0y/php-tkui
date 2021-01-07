<?php declare(strict_types=1);

namespace TclTk\Tests\Tcl;

use TclTk\Tests\TclInterp;
use TclTk\Tests\TestCase;

class SetVarTest extends TestCase
{
    use TclInterp;

    /** @test */
    public function check_set_var_by_eval()
    {
        $this->tcl->setVar($this->interp, 'testVar', NULL, 'testValue');

        $this->interp->eval('set testVar');
        $this->assertEquals('testValue', $this->interp->getStringResult());
    }

    /** @test */
    public function check_set_array_var_by_eval()
    {
        $this->tcl->setVar($this->interp, 'myArr', 'first', 'array value');

        $this->interp->eval('set myArr(first)');
        $this->assertEquals('array value', $this->interp->getStringResult());
    }

    /** @test */
    public function namespace_var()
    {
        $this->tcl->setVar($this->interp, '::var1', '', 'in the global');

        $this->interp->eval('set ::var1');
        $this->assertEquals('in the global', $this->interp->getStringResult());
    }
}