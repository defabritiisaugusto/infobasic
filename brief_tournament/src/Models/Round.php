<?php

namespace App\Models;

use App\Traits\WithValidate;
use App\Database\DB;

class Round extends BaseModel {

    use WithValidate;

    
    public ?int $id_tournament = null;
    public ?string $name = null; // Quarti, Semifinali, Finale, Finale 3° posto
    public ?string $status = null; // pending, in_progress, completed
    
   
    /**
     * Nome della tabella
     */
    protected static ?string $table = "rounds";

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
            "name" => ["required", "min:2", "max:100"],
            "status" => ["required", function ($field, $value, $data) {
                if (!in_array($value, ["pending", "in_progress", "completed"])) {
                    return "Lo stato deve essere uno tra: in_caricamento, in_progresso, completato";
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

   

}
