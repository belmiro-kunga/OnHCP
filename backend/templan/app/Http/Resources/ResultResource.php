<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ResultResource extends JsonResource
{
    public function toArray($request)
    {
        // Retorna o array "result" sem embrulhar, preservando o formato atual
        return $this->resource;
    }
}
