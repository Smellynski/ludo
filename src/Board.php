<?php

class Board
{
    private $activePlayer = null;
    private array $players = [];
    private GamePersisterService $gamePersisterService;
    private array $dataGrid;
    private int $countOfFields = 88;
    private $playerCount;
    private int $state = 0;
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

            $dataForPlayer = $this->dataFactory->createArrayForInsert("Players", $playerData);
            $this->gamePersisterService->insertData($dataForPlayer);

            $pieces = $player->getPieces();
            foreach ($pieces as $piece) {
                $pieces =  [
                    'piece_id' => $piece,
                    "pos" => 0,
                    'owning_player_id' => $player->getPlayerID(),
                    'game_id' => $this->gameID,
                ];
                $dataForPieces = $this->dataFactory->createArrayForInsert("Pieces", $pieces);
                $this->gamePersisterService->insertData($dataForPieces);
            }
        }

        $gameData = [
            'state' => $this->getState(),
            'active_player' => $this->activePlayer != null ? $this->activePlayer->getPlayerID() : '',
            'player_count' => $this->playerCount ?: 0,
            'game_id' => $this->gameID,
        ];

        $arrayToLoad = [
            "table" => 'GameData',
            "colum" => '*',
            "game_id" => $this->gameID,
        ];

        $dataForGame = $this->dataFactory->createArrayForInsert("GameData", $gameData);

        if (empty($this->gamePersisterService->loadData($arrayToLoad))) {
            $this->gamePersisterService->insertData($dataForGame);
        } else {
            $this->gamePersisterService->updateData(
                $this->dataFactory->createArrayForUpdate($arrayToLoad["table"], array_keys($gameData), $gameData)
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

    public function renderIntputsForPlayerCount(): string
    {
        return '<input type="number" name="playerCount" placeholder="Anzahl Spieler">
                <input class="btn" type="submit" value="Submit" name="submit">';
    }

    public function renderInputsForPlayerNames($playerCount)
    {
        $html = '';

        $inputs = [];
        for ($i = 0; $i < $playerCount; $i++) {
            $inputs[] = '<input type="text" name="namePlayer' . $i . '" placeholder="Name">';
        }

        $html .= implode('', $inputs);

        $html .= '
            </div>
                <input type="submit" value="Submit Player Names" name="submitNamesOfPlayer">
           </div>';
        return $html;
    }

    public function getActivePlayer()
    {
        return $this->activePlayer;
    }

    private function generateDataGridForBoard()
    {
        $dataGrid = [
            [2, 2, 0, 0, 1, 1, 1, 0, 0, 2, 2],
            [2, 2, 0, 0, 1, 3, 1, 0, 0, 2, 2],
            [0, 0, 0, 0, 1, 3, 1, 0, 0, 0, 0],
            [0, 0, 0, 0, 1, 3, 1, 0, 0, 0, 0],
            [1, 1, 1, 1, 1, 3, 1, 1, 1, 1, 1],
            [1, 3, 3, 3, 3, 0, 3, 3, 3, 3, 1],
            [1, 1, 1, 1, 1, 3, 1, 1, 1, 1, 1],
            [0, 0, 0, 0, 1, 3, 1, 0, 0, 0, 0],
            [0, 0, 0, 0, 1, 3, 1, 0, 0, 0, 0],
            [2, 2, 0, 0, 1, 3, 1, 0, 0, 2, 2],
            [2, 2, 0, 0, 1, 1, 1, 0, 0, 2, 2],
        ];
        $this->dataGrid = $dataGrid;
    }

    public function generateHTMLForBoard()
    {
        $this->generateDataGridForBoard();
        $html = '<div class="playField">';
        for ($y = 0; $y < sizeof($this->dataGrid); $y++) {
            for ($x = 0; $x < sizeof($this->dataGrid); $x++) {
                if ($this->dataGrid[$y][$x] == 0) {
                    $html .= '<div class="field"></div>';
                } elseif ($this->dataGrid[$y][$x] == 1) {
                    $html .= '<div class="field normalField"></div>';
                } elseif ($this->dataGrid[$y][$x] == 2) {
                    $html .= '<div class="field startField"></div>';
                } elseif ($this->dataGrid[$y][$x] == 3) {
                    $html .= '<div class="field finishField"></div>';
                }
            }
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
        //$this->gamePersisterService->resetDataForNewGame();
        $this->initializeGame();
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
