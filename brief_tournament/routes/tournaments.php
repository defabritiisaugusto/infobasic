<?php

use App\Utils\Response;
use App\Models\Tournament;
use App\Utils\Request;
use Pecee\SimpleRouter\SimpleRouter as Router;

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
 * GET /api/tournaments/{id} - Dettagli di un torneo
 */
Router::get('/tournaments/{id}', function ($id) {
    try {
        $tournament = Tournament::find($id);

        if($tournament === null) {
            Response::error('Torneo non trovato', Response::HTTP_NOT_FOUND)->send();
            return;
        }

        Response::success($tournament)->send();
    } catch (\Exception $e) {
        Response::error('Errore nel recupero del torneo: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * POST /api/tournaments - Crea nuovo torneo
 */
Router::post('/tournaments', function () {
    try {
        $request = new Request();
        $data = $request->json();

        // Validazione
        $requiredFields = ['name'];
        $missingFields = array_filter($requiredFields, fn($field) => !isset($data[$field]) || $data[$field] === '');
        
        if (!empty($missingFields)) {
            Response::error('Errore di validazione', Response::HTTP_BAD_REQUEST, array_map(fn($field) => "Il campo {$field} è obbligatorio", $missingFields))->send();
            return;
        }

        $errors = Tournament::validate($data);
        if (!empty($errors)) {
            Response::error('Errore di validazione', Response::HTTP_BAD_REQUEST, $errors)->send();
            return;
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
Router::match(['put', 'patch'], '/tournaments/{id}', function($id) {
    try {
        $request = new Request();
        $data = $request->json();

        $tournament = Tournament::find($id);
        if($tournament === null) {
            Response::error('Torneo non trovato', Response::HTTP_NOT_FOUND)->send();
            return;
        }

        $errors = Tournament::validate(array_merge($data, ['id_tournament' => $id]));
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
Router::delete('/tournaments/{id}', function($id) {
    try {
        $tournament = Tournament::find($id);
        if($tournament === null) {
            Response::error('Torneo non trovato', Response::HTTP_NOT_FOUND)->send();
            return;
        }

        $tournament->delete();

        Response::success(null, Response::HTTP_OK, "Torneo eliminato con successo")->send();
    } catch (\Exception $e) {
        Response::error('Errore durante l\'eliminazione del torneo: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});
