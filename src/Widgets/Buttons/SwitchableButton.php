<?php declare(strict_types=1);

namespace TclTk\Widgets\Buttons;

use TclTk\Options;
use TclTk\Variable;
use TclTk\Widgets\Widget;

/**
 * Parent for switch button classes.
 *
 * @property Variable $variable
 */
abstract class SwitchableButton extends GenericButton implements SelectableButton
{
    public function __construct(Widget $parent, array $options = [])
    {
        $var = isset($options['variable']);

        parent::__construct($parent, $options);

        if (! $var) {
            $this->variable = $this->window()->registerVar($this);
        }
    }

    /**
     * @inheritdoc
     */
    protected function initOptions(): Options
    {
        return parent::initOptions()->mergeAsArray([
            'indicatorOn' => null,
            'offRelief' => null,
            'selectColor' => null,
            'selectImage' => null,
            'tristateImage' => null,
            'tristateValue' => null,
            'variable' => null,
        ]);
    }

    /**
     * Selects the checkbutton.
     */
    public function select(): self
    {
        $this->call('select');
        return $this;
    }

    /**
     * Deselects the checkbutton.
     */
    public function deselect(): self
    {
        $this->call('deselect');
        return $this;
    }

    /**
     * @inheritdoc
     * @return bool
     */
    public function getValue()
    {
        return $this->variable->asBool();
    }

    /**
     * @inheritdoc
     */
    public function setValue($value): self
    {
        $this->variable->set($value);
        return $this;
    }
}