<?php

class Pieces
{
    private $id;
    private $owningPlayer;

    public function __construct($owningPlayer)
    {
        $this->id = uniqid();
        $this->owningPlayer = $owningPlayer;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

}