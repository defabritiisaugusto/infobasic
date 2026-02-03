<?php

use App\Utils\Response;
use App\Models\Game;
use App\Utils\Request;
use Pecee\SimpleRouter\SimpleRouter as Router;

/**
 * GET /api/matches - Lista di tutte le partite
 */
Router::get('/games', function () {
    try {
        $games = Game::all();
        Response::success($games)->send();
    } catch (\Exception $e) {
        Response::error('Errore nel recupero della lista partite: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * GET /api/matches/{id} - Dettagli di una partita
 */
Router::get('/games/{id}', function ($id) {
    try {
        $game = Game::find($id);

        if($game === null) {
            Response::error('Partita non trovata', Response::HTTP_NOT_FOUND)->send();
            return;
        }

        Response::success($game)->send();
    } catch (\Exception $e) {
        Response::error('Errore nel recupero della partita: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * GET /api/matches/tournament/{id_tournament} - Partite di un torneo
 */
Router::get('/games/tournament/{id_tournament}', function ($id_tournament) {
    try {
        $games = Game::find($id_tournament);
         if ($games === null) {
            Response::error("Torneo non trovato", Response::HTTP_NOT_FOUND)->send();
            return;
        }
        Response::success($games)->send();
    } catch (\Exception $e) {
        Response::error('Errore nel recupero delle partite del torneo: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * GET /api/matches/round/{id_round} - Partite di un round
 */
Router::get('/games/round/{id_round}', function ($id_round) {
    try {
        $games = Game::find($id_round);
         if ($games === null) {
            Response::error("Round non trovato", Response::HTTP_NOT_FOUND)->send();
            return;
        }
        Response::success($games)->send();
    } catch (\Exception $e) {
        Response::error('Errore nel recupero delle partite del round: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * POST /api/games - Crea nuova partita
 */
Router::post('/games', function () {
    try {
        $request = new Request();
        $data = $request->json();

        // Validazione
        $requiredFields = ['id_round', 'team1_id', 'team2_id'];
        $missingFields = array_filter($requiredFields, fn($field) => !isset($data[$field]) || $data[$field] === '');
        
        if (!empty($missingFields)) {
            Response::error('Errore di validazione', Response::HTTP_BAD_REQUEST, array_map(fn($field) => "Il campo {$field} è obbligatorio", $missingFields))->send();
            return;
        }

        $errors = Game::validate($data);
        if (!empty($errors)) {
            Response::error('Errore di validazione', Response::HTTP_BAD_REQUEST, $errors)->send();
            return;
        }

        $game = Game::create($data);

        Response::success($game, Response::HTTP_CREATED, "Partita creata con successo")->send();
    } catch (\Exception $e) {
        Response::error('Errore durante la creazione della partita: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * PUT/PATCH /api/games/{id} - Aggiorna partita 
 */
Router::match(['put', 'patch'], '/games/{id}', function($id) {
    try {
        $request = new Request();
        $data = $request->json();

        $game = Game::find($id);
        if($game === null) {
            Response::error('Partita non trovata', Response::HTTP_NOT_FOUND)->send();
            return;
        }

        $errors = Game::validate(array_merge($data, ['id_game' => $id]));
        if (!empty($errors)) {
            Response::error('Errore di validazione', Response::HTTP_BAD_REQUEST, $errors)->send();
            return;
        }

        $game->update($data);

        Response::success($game, Response::HTTP_OK, "Partita aggiornata con successo")->send();
    } catch (\Exception $e) {
        Response::error('Errore durante l\'aggiornamento della partita: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * DELETE /api/games/{id} - Elimina partita
 */
Router::delete('/games/{id}', function($id) {
    try {
        $game = Game::find($id);
        if($game === null) {
            Response::error('Partita non trovata', Response::HTTP_NOT_FOUND)->send();
            return;
        }

        $game->delete();

        Response::success(null, Response::HTTP_OK, "Partita eliminata con successo")->send();
    } catch (\Exception $e) {
        Response::error('Errore durante l\'eliminazione della partita: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});
