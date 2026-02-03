<?php

namespace App\Models;

use App\Traits\WithValidate;
use App\Database\DB;

class Team extends BaseModel {

    use WithValidate;

    
    public ?string $name = null;
    public ?string $img = null;

    /**
     * Nome della collection
     */
    protected static ?string $table = "teams";

    public function __construct(array $data = []) {
        parent::__construct($data);
    }

    protected static function validationRules(): array {
        return [
            "name" => ["required", "min:2", "max:100"],
            "img" =>  ["sometimes", function ($field, $value, $data) {
                if ($value !== null && $value !== '') {
                    if (!filter_var($value, FILTER_VALIDATE_URL) && !preg_match('/^\/[^\/]/', $value)) {
                        return "Il campo $field deve essere un URL valido o un path valido";
                    }
                }
            }]
        ];
    }

    /**
     * Relazioni
     */
    protected function tournaments()
    {
        return $this->hasMany(TournamentTeam::class, 'id_team');
    }
}