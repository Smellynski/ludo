<?php

class Base
{
   private Player $player;
   private $pos;

   public function __construct(Player $player)
   {
      $this->player = $player;
   }

   // getter and setter for $playerID
   public function getPlayerID(): Player
   {
      return $this->player;
   }
   public function setPlayerID(Player $player): void
   {
      $this->player = $player;
   }

   //getter and setter for $pos
   public function getPos(): array
   {
      return $this->pos;
   }
   public function setPos(array $pos): void
   {
      $this->pos = $pos;
   }
}
