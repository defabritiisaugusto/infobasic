<?php

use App\Utils\Response;
use App\Models\Tournament;
use App\Utils\Request;
use Pecee\SimpleRouter\SimpleRouter as Router;
use App\Models\TournamentTeam;
use App\Models\Round;
use App\Models\Game;

/**
 * GET /api/tournaments - Lista di tutti i tornei
 */
Router::get('/tournaments', function () {
    try {
        $tournaments = Tournament::all();
        Response::success($tournaments)->send();
    } catch (\Exception $e) {
        Response::error('Errore nel recupero della lista tornei: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});



/**
 * POST /api/tournaments - Crea nuovo torneo
 */
Router::post('/tournaments', function () {
    try {
        $request = new Request();
        $data = $request->json();

        $errors = Tournament::validate($data);
        if (!empty($errors)) {
            Response::error('Errore di validazione', Response::HTTP_BAD_REQUEST, $errors)->send();
        }

        $tournament = Tournament::create($data);

        Response::success($tournament, Response::HTTP_CREATED, "Torneo creato con successo")->send();
    } catch (\Exception $e) {
        Response::error('Errore durante la creazione del torneo: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * PUT/PATCH /api/tournaments/{id} - Aggiorna torneo
 */
Router::match(['put', 'patch'], '/tournaments/{id}', function ($id) {
    try {
        $request = new Request();
        $data = $request->json();

        $tournament = Tournament::find($id);
        if ($tournament === null) {
            Response::error('Torneo non trovato', Response::HTTP_NOT_FOUND)->send();
            return;
        }

        $errors = Tournament::validate(array_merge($tournament->toArray(), $data));
        if (!empty($errors)) {
            Response::error('Errore di validazione', Response::HTTP_BAD_REQUEST, $errors)->send();
            return;
        }

        $tournament->update($data);

        Response::success($tournament, Response::HTTP_OK, "Torneo aggiornato con successo")->send();
    } catch (\Exception $e) {
        Response::error('Errore durante l\'aggiornamento del torneo: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * DELETE /api/tournaments/{id} - Elimina torneo
 */
Router::delete('/tournaments/{id}', function ($id) {
    try {
        $tournament = Tournament::find($id);
        if ($tournament === null) {
            Response::error('Torneo non trovato', Response::HTTP_NOT_FOUND)->send();
            return;
        }

        $tournament->delete();

        Response::success(null, Response::HTTP_OK, "Torneo eliminato con successo")->send();
    } catch (\Exception $e) {
        Response::error('Errore durante l\'eliminazione del torneo: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});


// POST bracket

Router::post('/tournaments/{id_tournament}/generate-bracket', function ($id_tournament) {
    try {
        // 1️⃣ verifica torneo
        $tournament = Tournament::find($id_tournament);
        if ($tournament === null) {
            Response::error("Torneo non trovato", Response::HTTP_NOT_FOUND)->send();
            return;
        }

        // 2️⃣ recupera squadre
        $teams = TournamentTeam::where('id_tournament', $id_tournament);
        $teamIds = array_map(fn($t) => $t->id_team, $teams);

        if (count($teamIds) !== 8) {
            Response::error(
                "Il torneo deve avere esattamente 8 squadre",
                Response::HTTP_BAD_REQUEST
            )->send();
            return;
        }

        // 3️⃣ crea rounds
        $roundNames = [
            'Quarti di finale',
            'Semifinali',
            'Finale'
        ];

        $rounds = [];

        foreach ($roundNames as $name) {
            $round = new Round();
            $round->id_tournament = $id_tournament;
            $round->name = $name;
            $round->status = 'pending';
            $round->save();

            $rounds[] = $round;
        }

        // 4️⃣ quarti di finale
        $firstRound = $rounds[0];

        for ($i = 0; $i < 8; $i += 2) {
            $game = new Game();
            $game->id_round = $firstRound->id;
            $game->team1_id = $teamIds[$i];
            $game->team2_id = $teamIds[$i + 1];
            $game->goals_team1 = 0;
            $game->goals_team2 = 0;
            $game->save();
        }

        // 5️⃣ semifinali (vuote)
        $semiRound = $rounds[1];
        for ($i = 0; $i < 2; $i++) {
            $game = new Game();
            $game->id_round = $semiRound->id;
            $game->save();
        }

        // 6️⃣ finale (vuota)
        $finalRound = $rounds[2];
        $game = new Game();
        $game->id_round = $finalRound->id;
        $game->save();

        // 7️⃣ risposta OK
        Response::success(
            null,
            Response::HTTP_CREATED,
            "Bracket generato con successo"
        )->send();
        } catch (\Exception $e) {
            Response::error('Errore durante l\'iscrizione della squadra: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
        }
    });





           
