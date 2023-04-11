<?php

class DataFactory
{

    public function buildArrayToLoadData($table, $colum, $game_id){
        $arrayToLoadData = [
            "table" => $table,
            "colum" => $colum,
            "game_id" => $game_id,
        ];
        return $arrayToLoadData;
    }

    public function createArrayForInsert($table, $data)
    {
        $dataArrayForInsert = [
            "table" => $table,
            "data" => $data
        ];
        return $dataArrayForInsert;
    }

    public function createArrayForUpdate($table, $dataKeys, $data)
    {
        $temp = [];
        $dataArrayForUpdate = [
            "table" => $table,
            "data" => []
        ];

        for ($i = 0; $i < sizeof($dataKeys); $i++) {
            $temp[$dataKeys[$i]] = $data[$dataKeys[$i]];
        }

        $dataArrayForUpdate["data"] = $temp;
        return $dataArrayForUpdate;
    }

}