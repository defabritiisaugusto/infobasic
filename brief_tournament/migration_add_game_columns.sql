-- Aggiungi colonne mancanti alla tabella games

ALTER TABLE games ADD COLUMN team1_id INTEGER;
ALTER TABLE games ADD COLUMN team2_id INTEGER;
ALTER TABLE games ADD COLUMN goals_team1 INTEGER DEFAULT 0;
ALTER TABLE games ADD COLUMN goals_team2 INTEGER DEFAULT 0;
ALTER TABLE games ADD COLUMN winner_team_id INTEGER;

-- Aggiungi Foreign Keys per le squadre
ALTER TABLE games
ADD CONSTRAINT fk_games_team1
FOREIGN KEY (team1_id) REFERENCES teams(id) ON DELETE SET NULL;

ALTER TABLE games
ADD CONSTRAINT fk_games_team2
FOREIGN KEY (team2_id) REFERENCES teams(id) ON DELETE SET NULL;

ALTER TABLE games
ADD CONSTRAINT fk_games_winner
FOREIGN KEY (winner_team_id) REFERENCES teams(id) ON DELETE SET NULL;
