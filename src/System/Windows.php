<?php

declare(strict_types=1);

namespace Tkui\System;

class Windows extends OS
{
    public function defaultThemeName(): string
    {
        return 'vista';
    }

    public function tclSharedLib(): string
    {
        return dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'windows' . DIRECTORY_SEPARATOR . 'tcltk' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'tcl86t.dll';
    }

    public function tkSharedLib(): string
    {
        return dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'windows' . DIRECTORY_SEPARATOR . 'tcltk' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'tk86t.dll';
    }
}