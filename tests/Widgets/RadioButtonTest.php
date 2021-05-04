<?php declare(strict_types=1);

namespace TclTk\Tests\Widgets;

use TclTk\Tests\TestCase;
use TclTk\Variable;
use TclTk\Widgets\Buttons\RadioButton;

class RadioButtonTest extends TestCase
{
    /** @test */
    public function widget_created()
    {
        $varStub = $this->createStub(Variable::class);
        $varStub->method('__toString')->willReturn('var');

        $this->tclEvalTest(2, [
            ['ttk::radiobutton', $this->checkWidget('.rb'), '-text', '{Radio test}'],
            [$this->checkWidget('.rb'), 'configure', '-variable', 'var'],
        ]);
        $this->eval->method('registerVar')->willReturn($varStub);

        new RadioButton($this->createWindowStub(), 'Radio test', 1);
    }
}