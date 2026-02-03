<?php

namespace App\Models;

use App\Traits\WithValidate;
use App\Database\DB;

class Game extends BaseModel {

    use WithValidate;

    public ?int $id_round = null;
    public ?int $team1_id = null;
    public ?int $team2_id = null;
    public ?int $goals_team1 = null;
    public ?int $goals_team2 = null;
    public ?int $winner_team_id = null;
    
    /**
     * Nome della tabella
     */
    protected static ?string $table = "games";

    public function __construct(array $data = []) {
        parent::__construct($data);
    }

    protected static function validationRules(): array {
        return [
            "id_round" => ["required", "numeric", function ($field, $value, $data) {
                if ($value !== null && $value !== '') {
                    $round = Round::find((int)$value);
                    if ($round === null) {
                        return "Il round specificato non esiste";
                    }
                }
            }],
            "team1_id" => ["required", "numeric", function ($field, $value, $data) {
                if ($value !== null && $value !== '') {
                    $team = Team::find((int)$value);
                    if ($team === null) {
                        return "La squadra 1 specificata non esiste";
                    }
                }
            }],
            "team2_id" => ["required", "numeric", function ($field, $value, $data) {
                if ($value !== null && $value !== '') {
                    $team = Team::find((int)$value);
                    if ($team === null) {
                        return "La squadra 2 specificata non esiste";
                    }
                }
            }],
            "goals_team1" => ["sometimes", "numeric"],
            "goals_team2" => ["sometimes", "numeric"],
            "winner_team_id" => ["sometimes", "numeric", function ($field, $value, $data) {
                if ($value !== null && $value !== '') {
                    if ($value != $data['team1_id'] && $value != $data['team2_id']) {
                        return "Il vincitore deve essere una delle due squadre";
                    }
                }
            }]
        ];
    }

     /**
     * Relazioni
     */
    protected function round()
    {
        return $this->belongsTo(Round::class, 'id_round');
    }

    // indica che molte istanze di un'entità A sono associate a una singola istanza di un'entità B, mentre ogni istanza di B può essere legata a una o più di A

    
}