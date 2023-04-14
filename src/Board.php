<?php

class Board
{
    private $activePlayer = null;
    private array $players = [];
    private GamePersisterService $gamePersisterService;
    private Pieces $pieces;
    private Base $base;
    private array $dataGrid;
    private $playerCount;
    private int $state = 0;
    private int $countOfFields = 121;
    private DataFactory $dataFactory;
    private string $gameID;


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

        $rawData = $this->gamePersisterService->loadData($this->dataFactory->buildArrayToLoadData("GameData", "*", $this->gameID));

        if (!empty($rawData)) {
            $data = $rawData[0];
            $this->state = $data["state"];
            $this->activePlayer = $data["active_player"];
            $this->playerCount = $data["player_count"];
            $this->gameID = $data["game_id"];
        }

        if (!empty($this->players)) {
            $this->loadPlayersFromSavedData($this->gamePersisterService->loadData(
                $this->dataFactory->buildArrayToLoadData("Players", "player_name, player_id, color, pieces", $this->gameID)
            ));
        }
    }
    private function rollDice()
    {
        return rand(1, 6);
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


    public function createPlayer($name, $playerID, $colorIndex /*$color = int*/)
    {
        $newPlayer = new Player($name, $playerID, $colorIndex);
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
                    $this->dataFactory->createArrayForUpdate("Players", array_keys($playerData), $playerData)
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
            var_dump($pieces);
            foreach ($pieces as $piece) {
                $pieces =  [
                    'piece_id' => $piece,
                    "pos" => 0,
                    'owning_player_id' => $player->getPlayerID(),
                    'game_id' => $this->gameID,
                ];
                if ($this->checkIfInsert("Pieces")) {
                    $this->gamePersisterService->insertData(
                        $this->dataFactory->createArrayForInsert("Pieces", $pieces)
                    );
                } else {
                    $this->gamePersisterService->updateData(
                        $this->dataFactory->createArrayForUpdate("Pieces", array_keys($pieces), $pieces)
                    );
                }
            }
        }

        $gameData = [
            'state' => $this->getState(),
            'active_player' => $this->activePlayer != null ? $this->activePlayer->getPlayerID() : '',
            'player_count' => $this->playerCount ?: 0,
            'game_id' => $this->gameID,
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

    private function addPiecesToPlayer(Player $player)
    {
        $piecesForPlayer = [
            "pieceOne" => "",
            "pieceTwo" => "",
            "pieceThree" => "",
            "pieceFour" => "",
        ];

        foreach ($piecesForPlayer as $piece) {
            $piece = new Pieces($player);
        }
        var_dump($piecesForPlayer);
        $player->setPieces($piecesForPlayer);
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

    private function generateDataGridForBoard()
    {
        $dataGrid = [];
        for ($i = 0; $i < $this->countOfFields; $i++) {
            $dataGrid[$i] = 0;
        }
        $this->dataGrid = $dataGrid;
    }

    public function generateHTMLForBoard()
    {
        $this->generateDataGridForBoard();
        $html = '<div class="playField">';
        foreach ($this->dataGrid as $value) {
            switch ($value) {
                case 0:
                    $html .= '<div class="field">';
                    break;
                case 1:
                    $html .= '<div class="field normalField">';
                    break;
                case 2:
                    $html .= '<div class="field startField">';
                    break;
                case 3:
                    $html .= '<div class="field finishField">';
                    break;
            }
            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    }



    private function loadPlayersFromSavedData(array $savedPlayerData)
    {
        foreach ($savedPlayerData as $savedPlayer) {
            $player = new Player($savedPlayer['player_name'], $savedPlayer['player_id'], $savedPlayer['color']);
            $this->addPlayer($player);
        }
    }

    public function newGame()
    {
        $this->gamePersisterService->resetDataForNewGame();
        //$this->initializeGame();
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
