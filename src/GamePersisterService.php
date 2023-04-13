<?php

class GamePersisterService
{
    private PDO $databaseConnection;

    public function __construct()
    {
        $this->databaseConnection = $this->getPdo();
    }

    private function getPdo(): PDO
    {
        $env = [];
        $envFile = file_get_contents('./.env');
        $envLine = explode(PHP_EOL, $envFile);
        foreach ($envLine as $line) {
            list($key, $value) = explode('=', $line);
            $env[$key] = trim($value);
        }

        return new PDO(
            'mysql:dbname=' . $env['DB_DBNAME'] . ';host=' . $env['DB_HOST'],
            $env['DB_USER'],
            $env['DB_PW']
        );
    }


    /*public function resetDataForNewGame()
    {
        $data = [
            'gameData' => [
                "state" => 0,
                "playerCount" => 0,
                "activePlayer" => "",
                "dataGrid" => [],
            ],
            'playersData' => []
        ];
        $this->saveDataToFile($data);
    }*/

    public function updateData($dataToUpdate)
    {
        if (is_array($dataToUpdate["data"])) {
            $tableName = $dataToUpdate["table"];
            $sql = 'UPDATE ' . $tableName . ' SET ';
            $sqlUpdateValues = [];

            foreach ($dataToUpdate['data'] as $key => $value) {
                if (is_array($value)) {
                    $sqlUpdateValues[] = ' ' . $key . '="' . json_encode($value) . '"';
                } else {
                    $sqlUpdateValues[] = ' ' . $key  . '="' . $value . '"';
                }
            }
            $sql .= implode(",", $sqlUpdateValues);
            $this->databaseConnection->query($sql);
        }
    }

    /**
     * @throws Exception
     */
    public function createGame()
    {
        $gameID = 'G' . substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes(5))), 0, 7);
        $this->databaseConnection->query("INSERT INTO GameData (game_id) VALUES ('$gameID')");
        return $gameID;
    }


    public function insertData($dataToInsert)
    {
        if (is_array($dataToInsert["data"])) {
            $tableName = $dataToInsert["table"];
            $data = $dataToInsert["data"];
            $sql = "INSERT INTO $tableName VALUES ('" . implode("', '", $data) . "')";
        } else {
            $tableName = $dataToInsert["table"];
            $data = $dataToInsert["data"];
            $sql = "INSERT INTO $tableName VALUES ($data)";
        }
        try {
            $this->databaseConnection->query($sql);
        } catch (Exception $e) {
            var_dump($sql);
            var_dump(json_encode($e));
            die();
        }
    }

    public function loadData($requiredData): bool|array
    {
        if ($requiredData["game_id"] != null) {
            $sql = 'SELECT ' . $requiredData["colum"] . ' 
            FROM ' . $requiredData["table"] .  ' WHERE game_id = ' . '"' . $requiredData["game_id"] . '"' . ';';
        } else {
            $sql = 'SELECT ' . $requiredData["colum"] . ' 
            FROM ' . $requiredData["table"] . ';';
        }
        $data = $this->databaseConnection->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }
}
