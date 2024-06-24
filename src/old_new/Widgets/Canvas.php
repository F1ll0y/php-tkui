<?php declare(strict_types=1);

namespace Tkui\Widgets;

use Tkui\Options;
use Tkui\TclTk\Tcl;
use Tkui\TclTk\TclOptions;
use Tkui\TclTk\Variable;
use Tkui\Widgets\Common\ValueInVariable;
use Tkui\Widgets\Common\WithCallbacks;
use Tkui\Widgets\Consts\Justify;

/**
 * @link https://www.tcl.tk/man/tcl8.6/TkCmd/canvas.htm
 * 
 * @property bool $exportSelection
 * @property Justify $justify
 * @property int $height
 * @property callable|null $postCommand
 * @property string $state
 * @property Variable $textVariable
 * @property array $values TODO
 * @property int $width
 */
class Canvas extends TkWidget
{
    use WithCallbacks;

    protected string $widget = 'canvas';
    protected string $name = 'canvas';

    /** @var callable|null */
    private $postCommandCallback = null;

    public function __construct(Container $parent, array|Options $options = [])
    {
        parent::__construct($parent, $options);
    }

    public function setBackground(string $color)
    {
        $this->call('configure', "-background", $color);
    }

    public function setHighlightBackground(string $color)
    {
        $this->call('configure', "-highlightbackground", $color);
    }

    public function setBorderWidth(int $width)
    {
        $this->call('configure', "-borderwidth", $width);
    }

    /**
     * @inheritdoc
     */
    protected function createOptions(): Options
    {
        return new TclOptions([
            'closeEnough' => null,
            'confine' => null,
            'height' => null,
            'width' => null,
            'scrollRegion' => null,
            'state' => null,
            'xScrollIncrement' => null,
            'yScrollIncrement' => null,
        ]);
    }

    public function createARC($x1, $y1, $x2, $y2, $values){
        $data = array_merge([$x1, $y1, $x2, $y2], $values);

        print_r($data);
        $this->call('create', "arc", ...$data);
    }

    public function createRect($x, $y, $width, $height, ...$values){
        $this->call('create', "rect", $x, $y, $width, $height, "-fill", "black");
    }

    public function onButtonPress(callable $callback): self
    {
        $this->bind('ButtonPress', fn ($params) => $callback($this, $params));
        return $this;
    }
}