<?php

class Player
{

    protected $name;
    private string $playerID;
    private string $color;
    private array $pieces;
    private Base $base;


    public function __construct($name, $playerID, $colorIndex)
    {
        $this->name = $name;
        $this->playerID = $playerID;
        $this->color = $this->getColorByIndex($colorIndex);
        $this->pieces = [];
        $this->base = new Base($this);
    }

    /**
     * @return string
     */
    private function getColorByIndex($colorIndex): string
    {
        $colors = [
            "red",
            "blue",
            "green",
            "yellow",
        ];
        return $colors[$colorIndex];
    }




    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getPlayerID(): string
    {
        return $this->playerID;
    }

    /**
     * @param string $playerID
     */
    public function setPlayerID(string $playerID): void
    {
        $this->playerID = $playerID;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * Get the value of pieces
     */
    public function getPieces()
    {
        return $this->pieces;
    }

    /**
     * Set the value of pieces
     *
     * @return  self
     */
    public function setPieces($pieces)
    {
        $this->pieces = $pieces;
    }

    /**
     * Get the value of base
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * Set the value of base
     *
     * @return  self
     */
    public function setBase($base)
    {
        $this->base = $base;
    }
}
