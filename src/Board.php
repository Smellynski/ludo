<?php

class Board
{
    private  $activePlayer = null;
    private array $players = [];
    private GamePersisterService $gamePersisterService;
    private array $pieces;
    private Base $base;
    private array $dataGrid;
    private $playerCount;
    private int $state = 0;
    private int $countOfFields = 53;
    private DataFactory $dataFactory;
    private string $gameID;
    private int $diceThrowCount;


    public function __construct($gameId = null)
    {
        $this->gamePersisterService = new GamePersisterService();
        if (is_null($gameId)) {
            $this->gameID = $this->gamePersisterService->createGame();
        } else {
            $this->gameID = $gameId;
        }

        $this->dataFactory = new DataFactory();
        $this->initializeGame();
    }


    private function initializeGame()
    {
        $rawData =  $this->gamePersisterService->loadData($this->dataFactory->buildArrayToLoadData("GameData", "*", $this->gameID));

        if (!empty($rawData)) {
            $data = $rawData[0];
            $this->state = $data["state"];
            $activePlayerId = $data["active_player"];
            $this->diceThrowCount = $data["dice_throw_count"];
            $this->playerCount = $data["player_count"];
            $this->gameID = $data["game_id"];
        }

        if (empty($this->players)) {
            $this->loadPlayersFromSavedData($this->gamePersisterService->loadData(
                $this->dataFactory->buildArrayToLoadData("Players", "player_name, player_id, color", $this->gameID)
            ), $this->gamePersisterService->loadData(
                $this->dataFactory->buildArrayToLoadData("Pieces", "piece_id, pos, owning_player_id, inBase, inHome", $this->gameID)
            ));
        }

        foreach ($this->players as $player) {
            if ($player->getPlayerID() == $activePlayerId) {
                $this->activePlayer = $player;
            }
        }
    }


    public function rollDice(int $choosenFigure)
    {
        $diceNumber = 3;
        $this->movePiece($diceNumber, $choosenFigure);
    }

    public function movePiece($diceNumber, $choosenFigure)
    {
        $pieces = $this->activePlayer->getPieces();
        $pieceToMove = $pieces[$choosenFigure - 1];
        if ($diceNumber == 6 && $pieceToMove->getInBase()) {
            $pieceToMove->setInBase(false);
            switch ($this->activePlayer->getColor()) {
                case "red":
                    $pieceToMove->setPos(0);
                    break;
                case "blue":
                    $pieceToMove->setPos(12);
                    break;
                case "green":
                    $pieceToMove->setPos(24);
                    break;
                case "yellow":
                    $pieceToMove->setPos(36);
                    break;
            }
        } else {
            if ($this->checkIfAllFiguresAreInBase()) {
                $this->diceThrowCount++;
                if ($this->diceThrowCount >= 3) {
                    $this->diceThrowCount = 0;
                    $this->nextActivePlayer();
                    return;
                }
                return;
            }

            $pos = $pieceToMove->getPos() + $diceNumber;
            if ($pos > $this->countOfFields) {
                $pos = $pos - $this->countOfFields;
            }

            if ($this->pusten($diceNumber, $pieceToMove)) {
                $this->nextActivePlayer();
                return;
            }

            if ($this->checkForThrow($pos)) {
                $this->dataGrid[$pos]->setInBase(true);
                $this->dataGrid[$pos]->setPos(0);
            }

            $pieceToMove->setPos($pos);
        }
        $this->nextActivePlayer();
    }

    //choosenFigure is normaly a int but in this case its a object Pieces
    private function pusten($diceNumber, Pieces $choosenFigure)
    {
        $piecesThatCanThrow = [];
        foreach ($this->activePlayer->getPieces() as $piece) {
            if ($this->checkForThrow($piece->getPos() + $diceNumber)) {
                $piecesThatCanThrow[] = $piece;
                foreach ($piecesThatCanThrow as $piece) {
                    if ($piece != $choosenFigure) {
                        $piece->setPos(0);
                        $piece->setInBase(true);
                    }
                }
            }
        }
    }

    private function checkForThrow($pos)
    {
        if (is_object($this->dataGrid[$pos])) {
            $owningPlayer = $this->dataGrid[$pos]->getOwningPlayer();
            if ($owningPlayer->getPlayerID() != $this->activePlayer->getPlayerID()) {
                return true;
            }
        }
        return false;
    }



    private function checkIfAllFiguresAreInBase()
    {
        $pieces = $this->activePlayer->getPieces();
        foreach ($pieces as $piece) {
            if (!$piece->getInBase()) {
                return false;
            }
        }
        return true;
    }

    public function setInitialActivePlayer()
    {
        $this->activePlayer = $this->players[0];
    }

    public function nextActivePlayer()
    {
        foreach ($this->players as $key => $player) {
            if ($player->getPlayerID() == $this->activePlayer->getPlayerID()) {
                $nextKey = ($key + 1) % $this->playerCount;
                $this->activePlayer = $this->players[$nextKey];
                break;
            }
        }
    }


    public function createPlayer($name, $playerID, $colorIndex)
    {
        $colors = [
            "red",
            "blue",
            "green",
            "yellow",
        ];
        $color = $colors[$colorIndex];
        $newPlayer = new Player($name, $playerID, $color);
        $this->addPlayer($newPlayer);
        $this->addPiecesToPlayer($newPlayer);
        $this->addBaseToPlayer($newPlayer);
    }

    private function checkIfInsert($table)
    {
        $arrayToLoad = [
            "table" => $table,
            "colum" => '*',
            "game_id" => $this->gameID,
        ];

        $data = $this->gamePersisterService->loadData($arrayToLoad);

        // Special case for Player
        if ($table == "Players") {
            if (sizeof($data) != $this->playerCount) {
                return true;
            }
        }

        // Special case for Pieces
        if ($table == "Pieces") {
            if (sizeof($data) != 4 * $this->playerCount) {
                return true;
            }
        }

        // Special case for Bases
        if ($table == "Bases") {
            if (sizeof($data) != 4) {
                return true;
            }
        }

        if (empty($data)) {
            return true;
        } else {
            return false;
        }
    }

    public function saveData()
    {
        $playerData = [];
        /**
         * @var $player Player
         */
        foreach ($this->players as $player) {
            $playerData = [
                'player_name' => $player->getName(),
                'player_id' => $player->getPlayerID(),
                'color' => $player->getColor(),
                'game_id' => $this->gameID,
            ];

            if ($this->checkIfInsert("Players")) {
                $this->gamePersisterService->insertData(
                    $this->dataFactory->createArrayForInsert("Players", $playerData)
                );
            } else {
                $this->gamePersisterService->updateData(
                    $this->dataFactory->createArrayForUpdate(
                        "Players",
                        array_keys($playerData),
                        $playerData,
                        ["colum" => "player_id", "value" => $player->getPlayerID()]
                    )
                );
            }

            $baseData = [
                'color' => $player->getColor(),
                'player_id' => $player->getPlayerID(),
                'game_id' => $this->gameID,
            ];

            if ($this->checkIfInsert("Bases")) {
                $this->gamePersisterService->insertData(
                    $this->dataFactory->createArrayForInsert("Bases", $baseData)
                );
            }

            $pieces = $player->getPieces();
            foreach ($pieces as $piece) {
                $pieceData =  [
                    'piece_id' => $piece->getId(),
                    "pos" => $piece->getPos(),
                    'owning_player_id' => $player->getPlayerID(),
                    'game_id' => $this->gameID,
                    "inBase" => $piece->getInBase() ? 1 : 0,
                    "inHome" => $piece->getInHome() ? 1 : 0,
                ];
                if ($this->checkIfInsert("Pieces")) {
                    $this->gamePersisterService->insertData(
                        $this->dataFactory->createArrayForInsert("Pieces", $pieceData)
                    );
                } else {
                    $this->gamePersisterService->updateData(
                        $this->dataFactory->createArrayForUpdate(
                            "Pieces",
                            array_keys($pieceData),
                            $pieceData,
                            ["colum" => "piece_id", "value" => $piece->getId()]
                        )
                    );
                }
            }
        }

        $gameData = [
            'state' => $this->getState(),
            'active_player' => $this->activePlayer != null ? $this->activePlayer->getPlayerID() : '',
            'player_count' => $this->playerCount ?: 0,
            'game_id' => $this->gameID,
            'dice_throw_count' => ($this->diceThrowCount ?? 0),
        ];

        if ($this->checkIfInsert("GameData")) {
            $this->gamePersisterService->insertData(
                $this->dataFactory->createArrayForInsert("GameData", $gameData)
            );
        } else {
            $this->gamePersisterService->updateData(
                $this->dataFactory->createArrayForUpdate("GameData", array_keys($gameData), $gameData)
            );
        }
    }

    public function addPlayerCount(int $playercount)
    {
        $this->playerCount = $playercount;
    }

    public function addGameID($game_id)
    {
        $this->gameID = $game_id;
    }

    public function addPlayer(Player $player)
    {
        $this->players[] = $player;
    }

    private function addBaseToPlayer(Player $player)
    {
        $this->base = new Base($player);
        $player->setBase($this->base);
    }

    private function addPiecesToPlayer(Player $player, $savedData = null)
    {
        if ($savedData == null) {
            for ($i = 0; $i < 4; $i++) {
                $piece = new Pieces($player, 0);
                $this->pieces[] = $piece;
                $player->addPiece($piece);
            }
        } else {
            $pieces = [];
            foreach ($savedData as $pieceData) {
                if ($pieceData['owning_player_id'] == $player->getPlayerID()) {
                    $piece = new Pieces($player, $pieceData['pos'], $pieceData['piece_id']);
                    $piece->setInBase($pieceData['inBase']);
                    $piece->setInHome($pieceData['inHome']);
                    $pieces[] = $piece;
                    $player->addPiece($piece);
                }
            }
            $this->pieces = $pieces;
        }
    }

    public function renderIntputsForPlayerCount(): string
    {
        return '<input type="number" id="playerCount" name="playerCount" placeholder="Anzahl Spieler">
                <input class="btn" id="submit" type="submit" value="Submit" name="submit">';
    }

    public function renderInputsForPlayerNames($playerCount)
    {
        $html = '';

        $inputs = [];
        for ($i = 0; $i < $playerCount; $i++) {
            $inputs[] = '<input type="text" id="player' . $i . '" name="namePlayer' . $i . '" placeholder="Name">';
        }

        $html .= implode('', $inputs);

        $html .= '
            </div>
                <input type="submit" id="submit" value="Submit Player Names" name="submitNamesOfPlayer">
           </div>';
        return $html;
    }

    public function getActivePlayer()
    {
        return $this->activePlayer;
    }

    private function placePieces()
    {
        $pieces = [];
        foreach ($this->players as $player) {
            $pieces = array_merge($pieces, $player->getPieces());
        }

        foreach ($pieces as $piece) {
            $piecePos = $piece->getPos();
            if (!$piece->getInBase()) {
                $this->dataGrid[$piecePos] = $piece;
            }
        }
    }

    private function generateDataGridForBoard()
    {
        for ($i = 0; $i < $this->countOfFields; $i++) {
            $this->dataGrid[$i] = 0;
        }
        $this->placePieces();
    }

    private function generateHTMLForBoard()
    {
        $this->generateDataGridForBoard();
        $html = '<div class="playField">';
        foreach ($this->dataGrid as $key => $value) {
            $html .= '<div class="field">';
            if (is_object($value)) {
                $html .= '<div class="piece"
                        style="color: ' . $value->getOwningPlayer()->getColor() . '; 
                        ">&#9817;</div>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    }

    private function generateHTMLForBase()
    {
        $html = '';
        foreach ($this->players as $player) {
            $html .= '<div class="specialField base">';
            foreach ($player->getPieces() as $piece) {
                $html .= '<div class="field startField">';
                if ($piece->getInBase()) {
                    $html .= '<div  class="piece"
                    style="color: ' . $player->getColor() . '; 
                    ">&#9817;</div>';
                }
                $html .= '</div>';
            }
            $html .= '</div>';
        }
        return $html;
    }

    private function generateHTMLForHome()
    {
        $html = '';
        foreach ($this->players as $player) {
            $html .= '<div class="specialField home">';
            foreach ($player->getPieces() as $piece) {
                $html .= '<div class="field homeField">';
                if ($piece->getInHome()) {
                    $html .= '<div  class="piece"
                    style="color: ' . $player->getColor() . '; 
                    ">&#9817;</div>';
                }
                $html .= '</div>';
            }
            $html .= '</div>';
        }
        return $html;
    }

    public function generateView()
    {
        $this->generateDataGridForBoard();
        $content = '';
        $content .= '<div class="board">';
        $content .= $this->generateHTMLForBase();
        $content .= $this->generateHTMLForBoard();
        $content .= $this->generateHTMLForHome();
        $content .= '</div>';
        $content .= '<input type="number" name="choosenFigure">';
        $content .= ' <input class="btn" id="rollDice" type="submit" value="roll Dice" name="rollDice">';
        return $content;
    }


    private function loadPlayersFromSavedData(array $savedPlayerData, $savedPiecesData)
    {
        foreach ($savedPlayerData as $savedPlayer) {
            $player = new Player($savedPlayer['player_name'], $savedPlayer['player_id'], $savedPlayer['color']);
            $this->addPlayer($player);
            $this->addPiecesToPlayer($player, $savedPiecesData);
            $this->addBaseToPlayer($player);
            $this->generateDataGridForBoard();
        }
    }

    private function getPiecesFromActiveplayerOnField()
    {
        $pieces = [];
        foreach ($this->activePlayer->getPieces() as $piece) {
            if (!$piece->getInBase() && !$piece->getInHome()) {
                $pieces[] = $piece;
            }
        }
        return $pieces;
    }

    public function generatePopup()
    {
        $pieces = $this->getPiecesFromActiveplayerOnField();
        $html = '<div class="popup">';
        $html .= '<h1>Choose a figure to throw</h1>';
        $html .= '<form action="index.php" method="post">';
        foreach ($pieces as $piece) {
            $html .= '<input type="radio" id="piece" name="choosenFigure" value="' . "Piece on Position " . $piece->getPos() . '">';
        }
        $html .= '</form>';
        $html .= '</div>';
    }

    public function newGame()
    {
        $this->gamePersisterService->resetDataForNewGame();
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState(int $state)
    {
        $this->state = $state;
    }

    /**
     * @return mixed
     */
    public function getPlayerCount(): mixed
    {
        return $this->playerCount;
    }

    public function getGameId()
    {
        return $this->gameID;
    }
}
