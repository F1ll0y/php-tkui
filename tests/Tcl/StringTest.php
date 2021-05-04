<?php declare(strict_types=1);

namespace TclTk\Tests\Tcl;

use TclTk\Tcl;
use TclTk\Tests\TestCase;

class StringTest extends TestCase
{
    /** @test */
    public function string_must_be_quoted_in_curly_braces()
    {
        $str1 = Tcl::quoteString('test');
        $str2 = Tcl::quoteString('big string');

        $this->assertEquals('{test}', $str1);
        $this->assertEquals('{big string}', $str2);
    }

    /** @test */
    public function array_items_quoted()
    {
        $str = Tcl::arrayToList([100, 'item1', 'item 2']);

        $this->assertEquals('{{100} {item1} {item 2}}', $str);
    }
}