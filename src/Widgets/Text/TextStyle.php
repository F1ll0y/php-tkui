<?php declare(strict_types=1);

namespace PhpGui\Widgets\Text;

use PhpGui\Color;
use PhpGui\Font;
use PhpGui\Options;
use PhpGui\Widgets\Consts\Justify;
use PhpGui\Widgets\Consts\Relief;
use PhpGui\Widgets\Consts\WrapModes;

/**
 * Text style.
 *
 * Style controls the way information is displayed on the text widget.
 * The display options may include font size, style, text justification, etc.
 *
 * @link https://www.tcl.tk/man/tcl8.6/TkCmd/text.htm#M43
 *
 * @property Color|string $background
 * @property string $bgstipple
 * @property int $borderWidth
 * @property bool $elide
 * @property string $fgstipple
 * @property Font $font
 * @property Color|string $foreground
 * @property string $justify
 * @property int $lmargin1
 * @property int $lmargin2
 * @property Color|string $lmarginColor
 * @property int|string $offset
 * @property bool $overstrike
 * @property Color|string $overstrikeFg
 * @property string $relief
 * @property int $rmargin
 * @property Color|string $rmarginColor
 * @property Color|string $selectBackground
 * @property Color|string $selectForeground
 * @property int $spacing1
 * @property int $spacing2
 * @property int $spacing3
 * @property string[] $tabs
 * @property string $tabStyle
 * @property bool $underline
 * @property Color|string $underlineFg
 * @property string $wrap
 */
class TextStyle implements Justify, Relief, WrapModes
{
    private Options $options;
    private string $name;
    private TextApiMethodBridge $bridge;

    /**
     * @param TextApiMethodBridge $bridge The underlying text style api.
     * @param string $name    The style name.
     * @param array  $options The style options.
     */
    public function __construct(TextApiMethodBridge $bridge, string $name, array $options = [])
    {
        $this->bridge = $bridge;
        $this->name = $name;
        $this->options = $this->createOptions()->mergeAsArray($options);
        $this->configure();
    }

    /**
     * Tag options.
     */
    protected function createOptions(): Options
    {
        return new Options([
            'background' => null,
            'bgstipple' => null,
            'borderWidth' => null,
            'elide' => null,
            'fgstipple' => null,
            'font' => null,
            'foreground' => null,
            'justify' => null,
            'lmargin1' => null,
            'lmargin2' => null,
            'lmarginColor' => null,
            'offset' => null,
            'overstrike' => null,
            'overstrikeFg' => null,
            'relief' => null,
            'rmargin' => null,
            'rmarginColor' => null,
            'selectBackground' => null,
            'selectForeground' => null,
            'spacing1' => null,
            'spacing2' => null,
            'spacing3' => null,
            'tabs' => null,
            'tabStyle' => null,
            'underline' => null,
            'underlineFg' => null,
            'wrap' => null, 
        ]);
    }

    /**
     * Configures the style based on its options.
     */
    protected function configure()
    {
        $this->callMethod('configure', ...$this->options->asStringArray());
    }

    /**
     * Calls the low-level styles API method.
     *
     * @return mixed
     */
    protected function callMethod(string $method, ...$args)
    {
        return $this->bridge->callMethod($method, $this->name, ...$args);
    }

    /**
     * Returns the style name.
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->options->$name;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->options->$name = $value;
        $this->configure();
    }

    /**
     * Clears the text style from the range of characters.
     */
    public function clear(TextIndex $from, TextIndex $to): self
    {
        $this->callMethod('remove', (string) $from, (string) $to);
        return $this;
    }

    /**
     * Applies the text style for the range of characters.
     */
    public function add(TextIndex $from, TextIndex $to): self
    {
        $this->callMethod('add', (string) $from, (string) $to);
        return $this;
    }

    /**
     * Deletes the style and removes style from all the characters in the text.
     */
    public function delete(): void
    {
        $this->callMethod('delete');
    }
}
