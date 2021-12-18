<?php

interface IDisplayable
{
    public function getDisplayableFields() : array;
    public function getDisplayableFormat(): string;
};

interface IDisplayItem 
{
    public function getItemHtml(IDisplayable $item): string;
}

interface IDisplaySet
{
    public function getSetHtml(array $set, IDisplayItem $i): string;
}

?>