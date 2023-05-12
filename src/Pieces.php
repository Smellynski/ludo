<?php

class Pieces
{
    private string $id;
    private Player $owningPlayer;
    private int $pos;
    private ?bool $inBase = true;
    private bool $inHome = false;


    public function __construct(Player $owningPlayer, $pos, $id = '')
    {
        $this->owningPlayer = $owningPlayer;
        if ($id == "") {
            $this->id = $this->generateID();
        } else {
            $this->id = $id;
        }
        $this->pos = $pos;
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
    public function generateID(): string
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

    /**
     * Get the value of pos
     */
    public function getPos()
    {
        return $this->pos;
    }

    /**
     * Set the value of pos
     *
     * @return  self
     */
    public function setPos($pos)
    {
        $this->pos = $pos;

        return $this;
    }

    /**
     * Get the value of inBase
     */
    public function getInBase()
    {
        return $this->inBase;
    }

    /**
     * Set the value of inBase
     *
     * @return  self
     */
    public function setInBase($inBase)
    {
        $this->inBase = $inBase;

        return $this;
    }

    /**
     * Get the value of inHome
     */
    public function getInHome()
    {
        return $this->inHome;
    }

    /**
     * Set the value of inHome
     *
     * @return  self
     */
    public function setInHome($inHome)
    {
        $this->inHome = $inHome;

        return $this;
    }
}
