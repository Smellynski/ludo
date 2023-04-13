<?php

class Player
{

    protected $name;
    private string $playerID;
    private array $pieces;
    private string $color;


    public function __construct($name, $playerID, $colorIndex)
    {
        $this->name = $name;
        $this->playerID = $playerID;
        $this->color = $this->getColorByIndex($colorIndex);
        $this->pieces = $this->addPieces();
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

    private function addPieces()
    {
        $piecesForPlayer = [
            "pieceOne" => "",
            "pieceTwo" => "",
            "pieceThree" => "",
            "pieceFour" => "",
        ];

        foreach ($piecesForPlayer as &$piece) {
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

    public function getColor(): string
    {
        return $this->color;
    }

    public function getPiecesAsString(): string
    {
        $pieces = "";
        foreach ($this->pieces as $piece) {
            $pieces .= $piece . ", ";
        }
        return $pieces;
    }
}
