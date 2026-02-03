<?php

use App\Utils\Response;
use App\Models\Round;
use App\Utils\Request;
use Pecee\SimpleRouter\SimpleRouter as Router;

/**
 * GET /api/rounds - Lista di tutti i round
 */
Router::get('/rounds', function () {
    try {
        $rounds = Round::all();
        Response::success($rounds)->send();
    } catch (\Exception $e) {
        Response::error('Errore nel recupero della lista round: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * GET /api/rounds/{id} - Dettagli di un round
 */
Router::get('/rounds/{id}', function ($id) {
    try {
        $round = Round::find($id);

        if($round === null) {
            Response::error('Round non trovato', Response::HTTP_NOT_FOUND)->send();
            return;
        }

        Response::success($round)->send();
    } catch (\Exception $e) {
        Response::error('Errore nel recupero del round: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * GET /api/rounds/tournament/{id_tournament} - Round di un torneo
 */
Router::get('/rounds/tournament/{id_tournament}', function ($id_tournament) {
    try {
        $rounds = Round::find($id_tournament);

         if (empty($rounds)) {
            Response::error("Nessun round trovato per questo torneo", Response::HTTP_NOT_FOUND)->send();
            return;
        }
        Response::success($rounds)->send();
    } catch (\Exception $e) {
        Response::error('Errore nel recupero dei round del torneo: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * POST /api/rounds - Crea nuovo round
 */
Router::post('/rounds', function () {
    try {
        $request = new Request();
        $data = $request->json();

        // Validazione
        $requiredFields = ['id_tournament', 'name', 'status'];
        $missingFields = array_filter($requiredFields, fn($field) => !isset($data[$field]) || $data[$field] === '');
        
        if (!empty($missingFields)) {
            Response::error('Errore di validazione', Response::HTTP_BAD_REQUEST, array_map(fn($field) => "Il campo {$field} è obbligatorio", $missingFields))->send();
            return;
        }

        $errors = Round::validate($data);
        if (!empty($errors)) {
            Response::error('Errore di validazione', Response::HTTP_BAD_REQUEST, $errors)->send();
            return;
        }

        $round = Round::create($data);

        Response::success($round, Response::HTTP_CREATED, "Round creato con successo")->send();
    } catch (\Exception $e) {
        Response::error('Errore durante la creazione del round: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * PUT/PATCH /api/rounds/{id} - Aggiorna round
 */
Router::match(['put', 'patch'], '/rounds/{id}', function($id) {
    try {
        $request = new Request();
        $data = $request->json();

        $round = Round::find($id);
        if($round === null) {
            Response::error('Round non trovato', Response::HTTP_NOT_FOUND)->send();
            return;
        }

        $errors = Round::validate(array_merge($data, ['id_round' => $id]));
        if (!empty($errors)) {
            Response::error('Errore di validazione', Response::HTTP_BAD_REQUEST, $errors)->send();
            return;
        }

        $round->update($data);

        Response::success($round, Response::HTTP_OK, "Round aggiornato con successo")->send();
    } catch (\Exception $e) {
        Response::error('Errore durante l\'aggiornamento del round: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * DELETE /api/rounds/{id} - Elimina round
 */
Router::delete('/rounds/{id}', function($id) {
    try {
        $round = Round::find($id);
        if($round === null) {
            Response::error('Round non trovato', Response::HTTP_NOT_FOUND)->send();
            return;
        }

        $round->delete();

        Response::success(null, Response::HTTP_OK, "Round eliminato con successo")->send();
    } catch (\Exception $e) {
        Response::error('Errore durante l\'eliminazione del round: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});
