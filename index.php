<?php

require_once("autoload.php");
$pathToSaveFile = "./gameData/save.json";
$gameId = 'GtMNOmIY';

if (isset($_POST["gameId"])) {
    $gameId = $_POST["gameId"];
}

$board = new Board($gameId);
$renderService = new RenderService();

function generatePlayerID()
{
    $playerID = 'P' . substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes(5))), 0, 7);
    return $playerID;
}

if (isset($_POST["submit"])) {
    if ((int)$_POST["playerCount"] < 5) {
        $board->addPlayerCount((int)$_POST["playerCount"]);
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
        $board->createPlayer($playerNames, generatePlayerID(), $i);
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
    <script defer src="./src/index.js"></script>
</head>

<body>
    <form action="index.php" method="post">
        <?php
        $renderService->renderToScreen($board);
        ?>

        <input type="hidden" name="gameId" value="<?php echo $board->getGameId(); ?>" />
        <input class="btn" id="newGame" type="submit" value="New Game" name="newGame">
    </form>
</body>

</html>