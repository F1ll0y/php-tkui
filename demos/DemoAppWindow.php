<?php declare(strict_types=1);

use Tkui\Application;
use Tkui\DotEnv;
use Tkui\Image;
use Tkui\TclTk\TkAppFactory;
use Tkui\Windows\MainWindow;

require_once dirname(__DIR__) . '/vendor/autoload.php';

abstract class DemoAppWindow extends MainWindow
{
    const APP_NAME = 'PhpUI Demo';

    protected Application $app;
    private string $imageDir;

    public function __construct(string $title)
    {
        $factory = new TkAppFactory(self::APP_NAME);
        $this->app = $factory->createFromEnvironment(DotEnv::create(dirname(__DIR__)));
        parent::__construct($this->app, $title);
        $this->imageDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR;
        $this->setIcon();

        $this->bind("Configure", function($params){
           $this->windowResized($params[0], $params[1], $params[2], $params[3]);
        });
    }

    abstract protected function windowResized($x, $y, $width, $height): void;

    private function setIcon(): void
    {
        $this->getWindowManager()->setIcon(
            $this->loadImage('php-logo128.png'),
            $this->loadImage('php-logo256.png')
        );
    }

    public function run(): void
    {
        $this->app->run();
    }

    protected function loadImage(string $filename): Image
    {
        return $this->app->getImageFactory()->createFromFile($this->imageDir . $filename);
    }
}