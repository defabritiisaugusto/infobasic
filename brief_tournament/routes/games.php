<?php

use App\Utils\Response;
use App\Models\Game;
use App\Utils\Request;
use Pecee\SimpleRouter\SimpleRouter as Router;

/**
 * GET /api/games - Lista di tutte le partite
 */
Router::get('/games', function () {
    try {
        $request = new Request();
        $id_round = $request->getParam('id_round');

        if ($id_round !== null) {
            $games = Game::find($id_round);
            if ($games === null) {
                Response::error("Round non trovato", Response::HTTP_NOT_FOUND)->send();
               
            }

            $games = Game::where('id_round', '=', $id_round);
        } else {
            $games = Game::all();
        }


        Response::success($games)->send();
    } catch (\Exception $e) {
        Response::error('Errore nel recupero della lista partite: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR)->send();
    }
});







/**
 * POST /api/games - Crea nuova partita
 */
Router::post('/games', function () {
    try {
        $request = new Request();
        $data = $request->json();

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

        // Prova aggiornamento diretto senza validazione
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
