<?php

use Tkui\Layouts\Pack;
use Tkui\Widgets\Canvas;
use Tkui\Widgets\LabelFrame;

require_once dirname(__FILE__) . '/DemoAppWindow.php';

$demo = new class extends DemoAppWindow {
    protected Canvas $canvas;

    public function __construct()
    {
        parent::__construct('Canvas Demo');

        $this->getWindowManager()->setFullScreen();
        //$this->setBackground("black");
        //$this->setBorderWidth(0);
        //$this->getWindowManager()->setAttribute("fullscreen", 1);

        $this->canvas = new Canvas($this, ['width' => 800, 'height' => 600]);
        $this->canvas->createARC(200, 100, 100, 200, ["-fill", "red", "-start", "45"]);
        $this->canvas->setBackground("black");
        $this->canvas->setHighlightBackground("black");
        $this->canvas->setBorderWidth(0);

        $this->canvas->onButtonPress(function ($dummy, $params) {
            echo "test ";
        });

        $this->pack($this->canvas);
    }

    protected function windowResized($x, $y, $width, $height): void
    {
        $this->canvas->width = $width;
        $this->canvas->height = $height;
    }
};

$demo->run();