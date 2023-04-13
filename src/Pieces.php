<?php

class Pieces
{
    private $id;
    private $owningPlayerID;


    public function __construct($owningPlayerID)
    {
        $this->owningPlayerID = $owningPlayerID;
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
}
