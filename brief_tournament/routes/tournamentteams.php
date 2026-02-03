<?php

use App\Utils\Response;
use App\Models\TournamentTeam;
use App\Models\Tournament;
use App\Models\Team;
use App\Utils\Request;
use Pecee\SimpleRouter\SimpleRouter as Router;

/**
 * GET /api/tournament-teams - Lista tutte le iscrizioni al torneo, Serve per le iscrizioni
 */
Router::get('/tournament-teams', function () {
    try {
        $tournamentTeams = TournamentTeam::all();
        Response::success($tournamentTeams)->send();
    } catch (\Exception $e) {
        Response::error('Errore nel recupero della lista iscrizioni: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});


/**
 * GET /api/tournament-teams/tournament/{id_tournament} - Squadre di un torneo
 */

Router::get('/tournament-teams/tournament/{id_tournament}', function ($id_tournament) {
    try {
        $tournamentTeams = TournamentTeam::find($id_tournament);
         if ($tournamentTeams === null) {
            Response::error("Torneo non trovato", Response::HTTP_NOT_FOUND)->send();
            return;
        }
        Response::success($tournamentTeams)->send();
    } catch (\Exception $e) {
        Response::error('Errore nel recupero delle squadre del torneo: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * GET /api/tournament-teams/team/{id_team} - Tornei a cui partecipa una squadra
 */
Router::get('/tournament-teams/team/{id_team}', function ($id_team) {
    try {
        $tournamentTeams = TournamentTeam::find($id_team);
         if ($tournamentTeams === null) {
            Response::error("Squadra non trovata", Response::HTTP_NOT_FOUND)->send();
            return;
        }
        Response::success($tournamentTeams)->send();
    } catch (\Exception $e) {
        Response::error('Errore nel recupero dei tornei della squadra: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * POST /api/tournament-teams - Iscrive una squadra a un torneo
 */


Router::post('/tournament-teams', function () {
    try {
        $request = new Request();
        $data = $request->json();

        $requiredFields = ['id_tournament', 'id_team'];
        $missingFields = array_filter($requiredFields, fn($field) => empty($data[$field]));
      
        
        if (!empty($missingFields)) {
            Response::error('Errore di validazione', Response::HTTP_BAD_REQUEST, array_map(fn($field) => "Il campo {$field} è obbligatorio", $missingFields))->send();
            return;
        }

        $errors = TournamentTeam::validate($data);
        if (!empty($errors)) {
            Response::error('Errore di validazione', Response::HTTP_BAD_REQUEST, $errors)->send();
            return;
        }

        $tournamentTeam = TournamentTeam::create($data);

        Response::success($tournamentTeam, Response::HTTP_CREATED, "Squadra iscritta al torneo con successo")->send();
    } catch (\Exception $e) {
        Response::error('Errore durante l\'iscrizione della squadra: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * PUT/PATCH /api/tournament-teams/{id} - Aggiorna iscrizione
 */
Router::match(['put', 'patch'], '/tournament-teams/{id}', function($id) {
    try {
        $request = new Request();
        $data = $request->json();

        $tournamentTeam = TournamentTeam::find($id);
        if($tournamentTeam === null) {
            Response::error('Iscrizione non trovata', Response::HTTP_NOT_FOUND)->send();
            return;
        }

        $errors = TournamentTeam::validate(array_merge($data, ['id_tournament_team' => $id]));
        if (!empty($errors)) {
            Response::error('Errore di validazione', Response::HTTP_BAD_REQUEST, $errors)->send();
            return;
        }

        $tournamentTeam->update($data);

        Response::success($tournamentTeam, Response::HTTP_OK, "Iscrizione aggiornata con successo")->send();
    } catch (\Exception $e) {
        Response::error('Errore durante l\'aggiornamento dell\'iscrizione: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * DELETE /api/tournament-teams/{id} - Rimuove una squadra dal torneo
 */
Router::delete('/tournament-teams/{id}', function($id) {
    try {
        $tournamentTeam = TournamentTeam::find($id);
        if($tournamentTeam === null) {
            Response::error('Iscrizione non trovata', Response::HTTP_NOT_FOUND)->send();
            return;
        }

        $tournamentTeam->delete();

        Response::success(null, Response::HTTP_OK, "Squadra rimossa dal torneo con successo")->send();
    } catch (\Exception $e) {
        Response::error('Errore durante la rimozione della squadra dal torneo: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});
