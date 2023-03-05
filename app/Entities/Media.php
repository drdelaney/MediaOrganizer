<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Media extends Entity {
    public function serialize() : array {
        return [
            "ID" => $this->id,
            "Title" => $this->title,
            "Director" => $this->director,
            "Cast" => $this->cast,
            "Year" => $this->year,
            "Runtime" => $this->runtime,
            "Rating" => $this->rating,
            "Genre" => $this->genre,
            "Seen" => $this->seen,
            "Loaned" => $this->loaned,
            "Site" => $this->site,
            "Trailer" => $this->trailer,
            "Color" => $this->color,
            "Condition" => $this->cond,
            "Reigon" => $this->region,
        ];
    }
}
