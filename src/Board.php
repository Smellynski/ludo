<?php

class Board
{
    private ?Player $activePlayer = null;
    private array $players = [];
    private GamePersisterService $gamePersisterService;
    private array $dataGrid;
    private int $countOfFields = 88;
    private $playerCount;
    private int $state = 0;
    private DataFactory $dataFactory;
    private string $gameID;


    public function __construct($pathToSaveFile)
    {
        $this->gamePersisterService = new GamePersisterService($pathToSaveFile);
        $this->dataFactory = new DataFactory();
        $this->initializeGame();
    }

    private function initializeGame()
    {
        if (!empty($this->players)) {
            $this->loadPlayersFromSavedData($this->gamePersisterService->loadData(
                $this->dataFactory->buildArrayToLoadData("Players", "player_name, player_id", $this->gameID["game_id"])
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


    public function createPlayer($name, $playerID)
    {
        $newPlayer = new Player($name, $playerID);
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
                'game_id' => $this->gameID,
            ];

            $keys = array_keys($playerData);
            $dataForPlayer = $this->dataFactory->createArrayForInsert("Players", $playerData);
            $arrayToLoad = [
                "table" => 'Players',
                "colum" => '*',
                "game_id" => $this->gameID,
            ];

            $this->gamePersisterService->insertData($dataForPlayer);
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
        $dataGrid = [];
        for ($i = 0; $i < $this->countOfFields; $i++) {
            $dataGrid[$i] = 0;
        }
        return $dataGrid;
    }

    public function generateHTMLForBoard()
    {
        $html = '<div class="playField">';
        foreach ($this->dataGrid as $key => $value) {
            if ($value == 0) {
                $html .= '<div class="field"></div>';
            }
            if ($value == 1) {
                $html .= '<div class="player"></div>';
            }
        }
        $html .= '</div>';

        return $html;
    }

    private function loadPlayersFromSavedData(array $savedPlayerData)
    {
        echo "hello";
        var_dump($savedPlayerData);
        foreach ($savedPlayerData as $savedPlayer) {
            $player = new Player($savedPlayer['player_name'], $savedPlayer['player_id']);
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
}
