<?php

namespace App\Models;

use App\Traits\WithValidate;
use App\Database\DB;

class TournamentTeam extends BaseModel {

    use WithValidate;

  
    public ?int $id_tournament = null;
    public ?int $id_team = null;
    public ?int $position = null; // Posizione nel tabellone (1-8 per quarti)
    public ?string $status = null; // participare, eliminato, vincitore
    
    /**
     * Nome della tabella
     */
    protected static ?string $table = "tournament_teams";

    public function __construct(array $data = []) {
        parent::__construct($data);
    }

    protected static function validationRules(): array {
        return [
           
            "id_tournament" => ["required", "numeric", function ($field, $value, $data) {
                if ($value !== null && $value !== '') {
                    $tournament = Tournament::find((int)$value);
                    if ($tournament === null) {
                        return "Il torneo specificato non esiste";
                    }
                }
            }],
            "id_team" => ["required", "numeric", function ($field, $value, $data) {
                if ($value !== null && $value !== '') {
                    $team = Team::find((int)$value);
                    if ($team === null) {
                        return "La squadra specificata non esiste";
                    }
                }
            }],
            "position" => ["required", "numeric", function ($field, $value, $data) {
                if ($value !== null && ($value < 1 || $value > 8)) {
                    return "La posizione deve essere tra 1 e 8 per i quarti di finale";
                }
            }],
            "status" => ["required", function ($field, $value, $data) {
                if (!in_array($value, ["participating", "eliminated", "winner"])) {
                    return "Lo status deve essere uno tra: participando, eliminato, vincitore";
                }
            }]
        ];
    }

    
    /**
     * Relazioni
     */
    protected function tournament()
    {
        return $this->belongsTo(Tournament::class, 'id_tournament');
    }

    protected function team()
    {
        return $this->belongsTo(Team::class, 'id_team');
    }


    

}
