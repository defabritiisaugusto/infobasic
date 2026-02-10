<?php

use App\Utils\Response;
use App\Models\Tournament;
use App\Utils\Request;
use App\Services\BracketService;
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

Router::post('/tournaments/{id}/generate-bracket', function ($id) {
    try {
        $result = BracketService::generateForTournament((int)$id);

        Response::success(
            $result,
            Response::HTTP_CREATED,
            "Bracket generato con successo"
        )->send();
    } catch (\RuntimeException $e) {
        Response::error($e->getMessage(), Response::HTTP_BAD_REQUEST)->send();
    }
});

/**
 * POST /api/tournaments/{id}/generate-semis - Genera le semifinali
 */
Router::post('/tournaments/{id}/generate-semis', function ($id) {
    try {
        $result = BracketService::createSemifinals((int)$id);

        Response::success(
            $result,
            Response::HTTP_CREATED,
            "Semifinali generate automaticamente"
        )->send();
    } catch (\RuntimeException $e) {
        Response::error($e->getMessage(), Response::HTTP_BAD_REQUEST)->send();
    }
});

/**
 * POST /api/tournaments/{id}/generate-final - Genera la finale
 */
Router::post('/tournaments/{id}/generate-final', function ($id) {
    try {
        $result = BracketService::createFinal((int)$id);

        Response::success(
            $result,
            Response::HTTP_CREATED,
            "Finale generata automaticamente"
        )->send();
    } catch (\RuntimeException $e) {
        Response::error($e->getMessage(), Response::HTTP_BAD_REQUEST)->send();
    }
});

/**
 * GET /api/tournaments/{id}/bracket - Restituisce l'intero tabellone del torneo
 */
Router::get('/tournaments/{id}/bracket', function ($id) {
    try {
        $result = BracketService::getBracket((int)$id);

        Response::success($result)->send();
    } catch (\RuntimeException $e) {
        Response::error($e->getMessage(), Response::HTTP_BAD_REQUEST)->send();
    }
});





           
