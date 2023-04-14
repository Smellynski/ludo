<?php

class Pieces
{
    private $id;
    private Player $owningPlayer;


    public function __construct(Player $owningPlayer)
    {
        $this->owningPlayer = $owningPlayer;
        $this->id = $this->generateID();
    }


    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
    /**
     * @return string
     */
    private function generateID(): string
    {
        $pieceID =  'Piece' . substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes(5))), 0, 7);
        return $pieceID;
    }

    /**
     * Get the value of owningPlayer
     */
    public function getOwningPlayer()
    {
        return $this->owningPlayer;
    }

    /**
     * Set the value of owningPlayer
     *
     * @return  self
     */
    public function setOwningPlayer($owningPlayer)
    {
        $this->owningPlayer = $owningPlayer;

        return $this;
    }
}
