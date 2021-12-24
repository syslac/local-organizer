<?php

require_once "view/base/view_base.php";

class CCrudUi 
{
    private $setDisplayer;
    private $itemDisplayer;
    private $formDisplayer;

    public function __construct(IDisplaySet $d, IDisplayItem $i, IDisplayForm $f)
    {
        $this->setDisplayer = $d;
        $this->itemDisplayer = $i;
        $this->formDisplayer = $f;
    }

    public function render(array $set)
    {
        echo $this->setDisplayer->getSetHtml($set, $this->itemDisplayer);
    }

    public function renderEdit(object $obj, string $module)
    {
        echo $this->formDisplayer->getObjectEditForm($obj, $module);
    }
}

?>