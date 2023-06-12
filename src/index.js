document.addEventListener("keydown", function (event) {
  if (event.key == "k") {
    autoFillPlayerCount();
  }
  if (event.key == "g") {
    autoFillPlayerNames();
  }
  if (event.key == "h") {
    pressNewGame();
  }
});

function autoFillPlayerCount() {
  const playerCountInput = document.getElementById("playerCount");
  playerCountInput.value = 4;
  document.getElementById("submit").click();
}

function autoFillPlayerNames() {
  for (let i = 0; i < 4; i++) {
    document.getElementById("player" + i).value = "Player" + i;
    document.getElementById("submit").click();
  }
}
