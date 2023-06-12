/* This file shows you how to create the database and tables for the game. */
/* its just a reference, you can use it to create the database and tables, or you can do it manually. */
/* If you do it manually, make sure you create the database and tables with the same names as the ones in this file. */

CREATE TABLE GameData (
   state VARCHAR(255),
   active_player VARCHAR(255),
   player_count int(11),
   game_id VARCHAR(255)
);

CREATE TABLE Players(
   player_name VARCHAR(255),
   player_id VARCHAR(255),
   color VARCHAR(255),
   game_id VARCHAR(255)
);

CREATE TABLE Pieces(
   piece_id VARCHAR(255),
   pos VARCHAR(255),
   owning_player_id VARCHAR(255),
   game_id VARCHAR(255)
);
