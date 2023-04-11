<?php

class Player
{

    protected $name;
    private string $playerID;
    private array $pieces;


    public function __construct($name, $playerID)
    {
        $this->name = $name;
        $this->playerID = $playerID;
        $this->pieces = $this->addPieces();
    }

    private function addPieces(){
        $piecesForPlayer = [
            "pieceOne" => "",
            "pieceTwo" => "",
            "pieceThree" => "",
            "pieceFour" => "",
        ];

        foreach ($piecesForPlayer as &$piece){
            $piece = (new Pieces($this->playerID))->getId();
        }
        return $piecesForPlayer;
    }

    /**
     * @return array
     */
    public function getPieces(): array
    {
        return $this->pieces;
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
}