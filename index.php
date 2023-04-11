<?php

require_once("autoload.php");
$pathToSaveFile = "./gameData/save.json";
$board = new Board($pathToSaveFile);
$renderService = new RenderService();

function generatePlayerID()
{
    $playerID = 'P' . substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes(5))), 0, 7);
    return $playerID;
}

if (isset($_POST["submit"])) {
    if ((int)$_POST["playerCount"] < 5) {
        $board->addPlayerCount((int)$_POST["playerCount"]);
        $board->addGameID('G' . substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes(5))), 0, 7));
        $board->setState(1);
    }
    $board->saveData();
}

if (isset($_POST["newGame"])) {
    $board->newGame();
}

if (isset($_POST["submitNamesOfPlayer"])) {
    for ($i = 0; isset($_POST['namePlayer' . $i]); $i++) {
        $playerNames = $_POST['namePlayer' . $i];
        $board->createPlayer($playerNames, generatePlayerID());
    }
    $board->setInitialActivePlayer();
    $board->setState(2);
    $board->saveData();
}

?>

<html>

<head>
    <title>Ludo</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <form action="index.php" method="post">
        <?php
        $renderService->renderToScreen($board);
        ?>
        <input class="btn" type="submit" value="New Game" name="newGame">
    </form>
</body>

</html>