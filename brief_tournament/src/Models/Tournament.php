<?php

namespace App\Models;

use App\Traits\WithValidate;
use App\Database\DB;

class Tournament extends BaseModel
{

    use WithValidate;

    // Campi principali della tabella "tournaments"
    public ?string $name = null;           // Nome del torneo
    public ?string $date = null;           // Data (es. 2025-05-01)
    public ?string $place = null;          // Luogo dove si svolge il torneo
    public ?int $winner_team_id = null;    // FK verso la squadra vincitrice (teams.id)
    public ?string $status = null;         // Stato: pending, in_progress, completed


    /**
     * Nome della collection
     */
    protected static ?string $table = "tournaments";

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    protected static function validationRules(): array
    {
        return [
            "name" => ["required", "min:2", "max:100"],
            "date" => ["sometimes"],
            "place" => ["sometimes", "min:2", "max:100"],
            // winner_team_id viene impostato solo quando il torneo viene completato
            // e valida che sia un valore numerico valido.
            "winner_team_id" => ["sometimes", "numeric"],
            "status" => ["sometimes", function ($field, $value) {
                if ($value === null || $value === '') {
                    return null; // Permetti valore nullo o stringa vuota
                }
                $statuses = ['pending', 'in_progress', 'completed'];
                if (!in_array($value, $statuses, true)) {
                    return "Il campo $field deve essere uno dei seguenti valori: " . implode(', ', $statuses);
                }
                return null;    
            }],
        ];
    }

 

    /**
     * Relazioni
     * winnerTeam: squadra vincitrice del torneo (belongsTo Team)
     * teams: tutte le squadre partecipanti tramite tabella pivot tournament_teams
     */
    protected function winnerTeam()
    {
        return $this->belongsTo(Team::class, 'winner_team_id');
    }

    protected function teams()
    {
        // many-to-many tramite tabella pivot "tournament_teams"
        // campi: id_tournament (FK torneo) e id_team (FK squadra)
        return $this->belongsToMany(Team::class, 'tournament_teams', 'id_tournament', 'id_team');
    }
 
    
  


}