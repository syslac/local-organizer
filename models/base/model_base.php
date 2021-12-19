<?php

interface ITaggable 
{
    public function addTags(array $tags);
    public function getTags(): array;
};

interface IHasLinks
{
    public function addLink(string $link);
    public function getLinks(): array;
};

?>