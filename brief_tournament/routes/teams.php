<?php

use App\Utils\Response;
use App\Models\Team;
use App\Utils\Request;
use Pecee\SimpleRouter\SimpleRouter as Router;

/**
 * GET /api/teams - Lista di tutte le squadre
 */
Router::get('/teams', function () {
    try {
        $teams = Team::all();
        Response::success($teams)->send();
    } catch (\Exception $e) {
        Response::error('Errore nel recupero della lista squadre: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * GET /api/teams/{id} - Dettagli di una squadra
 */
Router::get('/teams/{id}', function ($id) {
    try {
        $team = Team::find($id);

        if($team === null) {
            Response::error('Squadra non trovata', Response::HTTP_NOT_FOUND)->send();
            return;
        }

        Response::success($team)->send();
    } catch (\Exception $e) {
        Response::error('Errore nel recupero della squadra: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * POST /api/teams - Crea nuova squadra
 */
Router::post('/teams', function () {
    try {
        $request = new Request();
        $data = $request->json();

        // Validazione
        if(!isset($data['name']) || $data['name'] === '') {
            Response::error('Errore di validazione', Response::HTTP_BAD_REQUEST, ['name' => 'Il campo name è obbligatorio'])->send();
            return;
        }

        $errors = Team::validate($data);
        if (!empty($errors)) {
            Response::error('Errore di validazione', Response::HTTP_BAD_REQUEST, $errors)->send();
            return;
        }

        $team = Team::create($data);

        Response::success($team, Response::HTTP_CREATED, "Squadra creata con successo")->send();
    } catch (\Exception $e) {
        Response::error('Errore durante la creazione della squadra: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * PUT/PATCH /api/teams/{id} - Aggiorna squadra
 */
Router::match(['put', 'patch'], '/teams/{id}', function($id) {
    try {
        $request = new Request();
        $data = $request->json();

        $team = Team::find($id);
        if($team === null) {
            Response::error('Squadra non trovata', Response::HTTP_NOT_FOUND)->send();
            return;
        }

        $errors = Team::validate(array_merge($data, ['id_team' => $id]));
        if (!empty($errors)) {
            Response::error('Errore di validazione', Response::HTTP_BAD_REQUEST, $errors)->send();
            return;
        }

        $team->update($data);

        Response::success($team, Response::HTTP_OK, "Squadra aggiornata con successo")->send();
    } catch (\Exception $e) {
        Response::error('Errore durante l\'aggiornamento della squadra: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * DELETE /api/teams/{id} - Elimina squadra
 */
Router::delete('/teams/{id}', function($id) {
    try {
        $team = Team::find($id);
        if($team === null) {
            Response::error('Squadra non trovata', Response::HTTP_NOT_FOUND)->send();
            return;
        }

        $team->delete();

        Response::success(null, Response::HTTP_OK, "Squadra eliminata con successo")->send();
    } catch (\Exception $e) {
        Response::error('Errore durante l\'eliminazione della squadra: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});
