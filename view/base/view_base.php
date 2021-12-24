<?php

interface IDisplayable
{
    public function getDisplayableFields() : array;
    public function getDisplayableFormat(): string;
};

interface IDisplayItem 
{
    public function getItemHtml(object $item): string;
}

interface IDisplaySet
{
    public function getSetHtml(array $set, IDisplayItem $i): string;
}

interface IDisplayForm
{
    public function getObjectEditForm(object $item, string $module) : string;
}

interface IDisplayColumn
{
    public function getColumnEditForm(string $val, string $name, string $header = null) : string;
}

?>