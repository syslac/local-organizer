<?php

require_once "view/base/view_base.php";

class CCrudUi 
{
    private $setDisplayer;
    private $itemDisplayer;

    public function __construct(IDisplaySet $d, IDisplayItem $i)
    {
        $this->setDisplayer = $d;
        $this->itemDisplayer = $i;
    }

    public function render(array $set)
    {
        echo $this->setDisplayer->getSetHtml($set, $this->itemDisplayer);
    }
}

?>