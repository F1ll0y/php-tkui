<?php declare(strict_types=1);

use TclTk\Widgets\Buttons\Button;
use TclTk\Widgets\Frame;
use TclTk\Widgets\Label;
use TclTk\Widgets\LabelFrame;
use TclTk\Widgets\Notebook;
use TclTk\Widgets\NotebookTab;

require_once dirname(__FILE__) . '/DemoAppWindow.php';

$demo = new class extends DemoAppWindow
{
    public function __construct()
    {
        parent::__construct('Notebook demo');

        $l = new Label($this, '');
        $l->pack()->pad(2, 2)->fillX()->manage();

        $nb = new Notebook($this);
        $nb->add($this->createTab1($nb));
        $nb->add($this->createTab2($nb));
        $nb->add($this->createTab3($nb));
        $nb->pack()->expand()->fillBoth()->manage();
        $nb->onChanged(function (NotebookTab $tab) use ($l) {
            $this->showCurrentTabname($l, $tab->text);
        });
    }

    private function createTab1(Notebook $parent): NotebookTab
    {
        $f = new Frame($parent);
        $tab = new NotebookTab($f, 'First tab');

        // Setting up 'underline' will automatically enable
        // keyboard traversing for notebook widget.
        $tab->underline = 0;

        (new Button($f, 'Click to change tab text'))
            ->onClick(function () use ($tab) {
                $tab->text = 'Changed !';
            })->pack()->padY(8)->manage();

        (new Button($f, 'Switch to Second'))
            ->onClick(fn () => $parent->select(1))
            ->pack()->padY(8)->manage();

        return $tab;
    }

    private function createTab2(Notebook $parent): NotebookTab
    {
        $f = new LabelFrame($parent, 'Second frame');

        return new NotebookTab($f, 'Second tab', ['padding' => 4, 'underline' => 0]);
    }

    private function createTab3(Notebook $parent): NotebookTab
    {
        $f = new Frame($parent);
        $tab = new NotebookTab($f, 'Hide me');
        $tab->underline = 2;

        (new Button($f, 'Click to hide'))
            ->onClick(function () use ($parent, $tab) {
                $parent->hide($tab);
            })
            ->pack()->pady(8)->manage();

        return $tab;
    }

    protected function showCurrentTabname(Label $l, string $name = '')
    {
        $l->text = 'Current tab: ' . $name;
    }
};

$demo->run();