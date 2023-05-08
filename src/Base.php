<?php

class Base
{
   private Player $player;

   public function __construct(Player $player)
   {
      $this->player = $player;
   }
}
