<?php declare(strict_types=1);

namespace Tkui\Components\Windows;

use Tkui\Interfaces\ApplicationInterface;
use Tkui\Widgets\Container;
use Tkui\Widgets\Widget;
use Tkui\Windows\BaseWindow;

/**
 * The main application window implementation.
 */
class Window extends AbstractWindow
{
    private int $positionX = 0;
    private int $positionY = 0;
    private int $width = 100;
    private int $height = 100;

    public function __construct(ApplicationInterface $app, string $title)
    {
        parent::__construct($app, $title);
    }
    /*
     *

        $this->bind("Configure", function($params){
            $this->positionX = (int)$params[0];
            $this->positionY = (int)$params[1];
            $this->width = (int)$params[2];
            $this->height = (int)$params[3];
        });
     */


    public function setBackground(string $color)
    {
        $this->getWindowManager()->setConfigure("background", $color);
    }

    public function setBorderWidth(int $width)
    {
        $this->getWindowManager()->setConfigure("borderwidth", $width);
    }

    public function setPos(int $x, int $y): void
    {
        $this->positionX = $x;
        $this->positionY = $y;
        $this->getWindowManager()->setPos($x, $y);
    }

    public function center(): void
    {
        $this->getWindowManager()->center($this->positionX, $this->positionY, $this->width, $this->height);
    }

    /**
     * @inheritdoc
     */
    protected function createWindow(): void
    {
        // Nothing to create. Main window is created automatically
        // during Tk initialization.
    }

    /**
     * @inheritdoc
     */
    public function id(): string
    {
        // The main window is single and don't need an id.
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getEval(): EvaluatorInterface
    {
        return $this->app;
    }

    /**
     * @inheritdoc
     */
    public function parent(): Container
    {
        return $this;
    }

    public function bindWidget(Widget $widget, string $event, ?callable $callback): Container
    {
        if ($callback === null) {
            $this->app->unbindWidget($widget, $event);
        } else {
            $this->app->bindWidget($widget, $event, $callback);
        }
        return $this;
    }
}