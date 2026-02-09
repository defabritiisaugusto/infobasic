<?php

use App\Utils\Response;
use App\Models\TournamentTeam;
use App\Models\Tournament;
use App\Models\Team;
use App\Utils\Request;
use LDAP\Result;
use Pecee\SimpleRouter\SimpleRouter as Router;



/**
 * GET /api/tournament-teams/{id_team}/tournaments - Lista tutti i tornei dove e iscritta una squadra
 */

Router::get('/tournament-teams/{id_team}/tournaments', function ($id_team) {
     try {
        $team = Team::find($id_team);
         if ($team === null) {
            Response::error("Squadra non trovata", Response::HTTP_NOT_FOUND)->send();
            return;
        }
        $tournamentTeams = TournamentTeam::where('id_team', '=', $id_team);

        $result = [];
        foreach ($tournamentTeams as $tournamentTeam) {
            $TournamentTeamData = $tournamentTeam->toArray();
            $tournament = Tournament::find($tournamentTeam->id_tournament);
            if ($tournament !== null) {
                $TournamentTeamData['tournament'] = $tournament->toArray();
            }
            $result[] = $TournamentTeamData;
        }

        Response::success($result)->send();
    } catch (\Exception $e) {
        Response::error('Errore nel recupero dei tornei della squadra: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
    
});

/**
 * GET /api/tournament-teams/{id_tournament}/teams - Lista squadre iscritte ad un torneo
 */

Router::get('/tournament-teams/{id_tournament}/teams', function ($id_tournament) {
     try {
        $team = Tournament::find($id_tournament);
         if ($team === null) {
            Response::error("Torneo non trovato", Response::HTTP_NOT_FOUND)->send();
            return;
        }
        $tournamentTeams = TournamentTeam::where('id_tournament', '=', $id_tournament);

        $result = [];
        foreach ($tournamentTeams as $tournamentTeam) {
            $TournamentTeamData = $tournamentTeam->toArray();
            $team = Team::find($tournamentTeam->id_team);
            if ($team !== null) {
                $TournamentTeamData['teams'] = $team->toArray();
            }
            $result[] = $TournamentTeamData;
        }

        Response::success($result)->send();
    } catch (\Exception $e) {
        Response::error('Errore nel recupero dei tornei della squadra: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
    
});



/**
 * POST /api/tournament-teams/{id_team}/tournaments/{id_tournaments} - Iscrive una squadra a un torneo
 */


Router::post('/tournament-teams/{id_team}/tournaments/{id_tournament}', function ($id_team, $id_tournament) {
    try {
        $request = new Request();
        $data = $request->json();

        // verifico che la squadra esiste
        $team = Team::find($id_team);
        if ($team === null) {
            Response::error("Squadra non trovata", Response::HTTP_NOT_FOUND)->send();
            return;
        }

        // aggiungo id_team ai dati per la validazione
        $data['id_team'] = (int)$id_team;
        $data['id_tournament'] = (int)$id_tournament;

        // verifico che l'id_tournament sia stato passato e esista nel db
        if (!isset($id_tournament)) {
            Response::error("Il campo id_tournament è obbligatorio", Response::HTTP_BAD_REQUEST)->send();
            return;
        }
        $tournament = Tournament::find($id_tournament);
        if ($tournament === null) {
            Response::error("Torneo non trovato", Response::HTTP_NOT_FOUND)->send();
            return;
        }

        $errors = TournamentTeam::validate($data);
        if (!empty($errors)) {
            Response::error('Errore di validazione', Response::HTTP_BAD_REQUEST, $errors)->send();
            return;
        }

        $tournamentTeam = TournamentTeam::create($data)->toArray();

        $tournament = Tournament::find($tournamentTeam['id_tournament']);
        if ($tournament !== null) {
            $tournamentTeam['tournament'] = $tournament->toArray();
        }

        Response::success($tournamentTeam, Response::HTTP_CREATED, "Squadra iscritta al torneo con successo")->send();
    } catch (\Exception $e) {
        Response::error('Errore durante l\'iscrizione della squadra: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});

/**
 * PUT/PATCH /api/tournament-teams/{id_team}/tournaments/{id_tournament} - Aggiorna iscrizione
 */
Router::match(['put', 'patch'], '/tournament-teams/{id_team}/tournaments/{id_tournament}', function($id_team, $id_tournament) {
    try {
        $request = new Request();
        $data = $request->json();

        // verifico che la squadra esiste
        $tournamentTeam = TournamentTeam::find($id_team);
        if($tournamentTeam === null) {
            Response::error('Iscrizione non trovata', Response::HTTP_NOT_FOUND)->send();
            return;
        }

        // recupero il TournamentTeam da aggiornare
        $tournamentTeam = TournamentTeam::findByTournamentAndTeam($id_tournament, $id_team);
        if($tournamentTeam === null) {
            Response::error('Iscrizione non trovata', Response::HTTP_NOT_FOUND)->send();
            return;
        }

         // aggiungo id_team e id_tournament ai dati per la validazione
         $data['id_team'] = (int)$id_team;
         $data['id_tournament'] = (int)$id_tournament;

         // verifico che l'id_tournament sia stato passato e esista nel db
         if (!isset($data['id_tournament'])) {
            Response::error("Il campo id_tournament è obbligatorio", Response::HTTP_BAD_REQUEST)->send();
            return;
        }
        $errors = TournamentTeam::validate(array_merge($tournamentTeam->toArray()));
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
 * DELETE /api/tournament-teams/{id_team}/tournaments/{id_tournament} - Rimuove una squadra dal torneo
 */
Router::delete('/tournament-teams/{id_team}/tournaments/{id_tournament}', function($id_team, $id_tournament) {
    try {
        $tournamentTeam = TournamentTeam::findByTournamentAndTeam($id_tournament, $id_team);
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
