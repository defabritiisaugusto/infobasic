<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\TournamentTeam;
use App\Models\Round;
use App\Models\Game;
use App\Utils\Response;

class BracketService
{
    /**
     * Genera i quarti di finale randomicamente per un torneo a 8 squadre.
     * Restituisce un array con i dati creati oppure lancia eccezioni in caso di errore.
     */
    public static function generateForTournament(int $tournamentId): array
    {
        // 1) Verifica torneo esistente
        $tournament = Tournament::find($tournamentId);
        if ($tournament === null) {
            throw new \RuntimeException('Torneo non trovato');
        }

        // 2) Recupera le squadre iscritte al torneo
        $teams = TournamentTeam::where('id_tournament', $tournamentId);

        $count = count($teams);
        if ($count !== 8) {
            throw new \RuntimeException('Il torneo deve avere esattamente 8 squadre');
        }

        $teamIds = array_map(fn($tt) => $tt->id_team, $teams);
        
        // Shuffle randomico
        shuffle($teamIds);

        // 3) Crea il round dei quarti di finale
        $round = new Round();
        $round->id_tournament = $tournamentId;
        $round->name = 'Quarti di finale';
        $round->status = 'pending';
        $round->save();

        // 4) Crea i 4 quarti di finale
        $quarterGames = [];

        for ($i = 0; $i < 8; $i += 2) {
            $game = new Game();
            $game->id_round = $round->id;
            $game->id_team1 = $teamIds[$i];
            $game->id_team2 = $teamIds[$i + 1];
            $game->goals_team1 = 0;
            $game->goals_team2 = 0;
            $game->save();

            $quarterGames[] = $game;
        }

        return [
            'tournament' => $tournament,
            'round' => $round,
            'quarter_games' => $quarterGames,
        ];
    }

    /**
     * Crea le semifinali automaticamente quando tutti i 4 quarti hanno un vincitore
     */
    public static function createSemifinals(int $tournamentId): array
    {
        // 1) Recupera il PRIMO round dei quarti con i giochi corretti
        $rounds = Round::where('id_tournament', $tournamentId);
        $quarterRound = null;
        
        foreach ($rounds as $round) {
            if ($round->name === 'Quarti di finale') {
                // Verifica che questo round abbia 4 giochi
                $games = Game::where('id_round', $round->id);
                if (count($games) === 4) {
                    $quarterRound = $round;
                    break;
                }
            }
        }

        if ($quarterRound === null) {
            throw new \RuntimeException('Round dei quarti non trovato o non valido');
        }

        // 2) Recupera le 4 partite dei quarti
        $quarterGames = Game::where('id_round', $quarterRound->id);

        if (count($quarterGames) !== 4) {
            throw new \RuntimeException('Numero di quarti non valido');
        }

        // 3) Verifica che tutti i quarti abbiano un vincitore
        foreach ($quarterGames as $game) {
            if ($game->winner_team_id === null) {
                throw new \RuntimeException('Tutti i quarti devono essere completati prima di creare le semifinali');
            }
        }

        // 4) Crea il round delle semifinali
        $semiRound = new Round();
        $semiRound->id_tournament = $tournamentId;
        $semiRound->name = 'Semifinali';
        $semiRound->status = 'pending';
        $semiRound->save();

        // 5) Crea le 2 semifinali
        // Semifinale 1: vincitore Q0 vs vincitore Q1
        // Semifinale 2: vincitore Q2 vs vincitore Q3
        $semiGames = [];

        $semi1 = new Game();
        $semi1->id_round = $semiRound->id;
        $semi1->id_team1 = $quarterGames[0]->winner_team_id;
        $semi1->id_team2 = $quarterGames[1]->winner_team_id;
        $semi1->goals_team1 = 0;
        $semi1->goals_team2 = 0;
        $semi1->save();
        $semiGames[] = $semi1;

        $semi2 = new Game();
        $semi2->id_round = $semiRound->id;
        $semi2->id_team1 = $quarterGames[2]->winner_team_id;
        $semi2->id_team2 = $quarterGames[3]->winner_team_id;
        $semi2->goals_team1 = 0;
        $semi2->goals_team2 = 0;
        $semi2->save();
        $semiGames[] = $semi2;

        // 6) Collega i quarti alle semifinali
        $quarterGames[0]->next_game_id = $semi1->id;
        $quarterGames[0]->save();
        $quarterGames[1]->next_game_id = $semi1->id;
        $quarterGames[1]->save();
        $quarterGames[2]->next_game_id = $semi2->id;
        $quarterGames[2]->save();
        $quarterGames[3]->next_game_id = $semi2->id;
        $quarterGames[3]->save();

        return [
            'round' => $semiRound,
            'semi_games' => $semiGames,
        ];
    }

    /**
     * Crea la finale automaticamente quando entrambe le semifinali hanno un vincitore
     */
    public static function createFinal(int $tournamentId): array
    {
        // 1) Recupera il round delle semifinali
        $rounds = Round::where('id_tournament', $tournamentId);
        $semiRound = null;
        
        foreach ($rounds as $round) {
            if ($round->name === 'Semifinali') {
                $semiRound = $round;
                break;
            }
        }

        if ($semiRound === null) {
            throw new \RuntimeException('Round delle semifinali non trovato');
        }

        // 2) Recupera le 2 partite delle semifinali
        $semiGames = Game::where('id_round', $semiRound->id);

        if (count($semiGames) !== 2) {
            throw new \RuntimeException('Numero di semifinali non valido');
        }

        // 3) Verifica che entrambe le semifinali abbiano un vincitore
        foreach ($semiGames as $game) {
            if ($game->winner_team_id === null) {
                throw new \RuntimeException('Entrambe le semifinali devono essere completate prima di creare la finale');
            }
        }

        // 4) Crea il round della finale
        $finalRound = new Round();
        $finalRound->id_tournament = $tournamentId;
        $finalRound->name = 'Finale';
        $finalRound->status = 'pending';
        $finalRound->save();

        // 5) Crea la finale
        $finalGame = new Game();
        $finalGame->id_round = $finalRound->id;
        $finalGame->id_team1 = $semiGames[0]->winner_team_id;
        $finalGame->id_team2 = $semiGames[1]->winner_team_id;
        $finalGame->goals_team1 = 0;
        $finalGame->goals_team2 = 0;
        $finalGame->save();

        // 6) Collega le semifinali alla finale
        $semiGames[0]->next_game_id = $finalGame->id;
        $semiGames[0]->save();
        $semiGames[1]->next_game_id = $finalGame->id;
        $semiGames[1]->save();

        return [
            'round' => $finalRound,
            'final_game' => $finalGame,
        ];
    }

    /**
     * Ritorna l'intero bracket di un torneo: round + partite
     */
    public static function getBracket(int $tournamentId): array
    {
        $tournament = Tournament::find($tournamentId);
        if ($tournament === null) {
            throw new \RuntimeException('Torneo non trovato');
        }

        // Tutti i round del torneo
        $rounds = Round::where('id_tournament', $tournamentId);

        // Partite raggruppate per round
        $gamesByRound = [];
        foreach ($rounds as $round) {
            $gamesByRound[$round->id] = Game::where('id_round', $round->id);
        }

        return [
            'tournament'    => $tournament,
            'rounds'        => $rounds,
            'games_by_round'=> $gamesByRound,
        ];
    }
}
