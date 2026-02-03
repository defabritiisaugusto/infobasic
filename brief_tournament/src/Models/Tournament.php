<?php

namespace App\Models;

use App\Traits\WithValidate;
use App\Database\DB;

class Tournament extends BaseModel
{

    use WithValidate;

    
    public ?string $name = null;
    public ?string $date = null;
    public ?string $place = null;


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
        ];
    }

    /**
     * Relazioni
     */
    protected function tournamentTeams()
    {
        return $this->hasMany(TournamentTeam::class, 'id_tournament');
    }

    protected function teams()
    {
        return $this->hasMany(TournamentTeam::class, 'id_tournament');
    }

    protected function rounds()
    {
        return $this->hasMany(Round::class, 'id_tournament');
    }


}