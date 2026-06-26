<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'hook' => $this->hook,
            'body_points' => $this->body_points,
            'technical_readability_score' => $this->technical_readability_score,
            'suggested_hashtags' => $this->suggested_hashtags,
            'tone_compliance_justification' => $this->tone_compliance_justification,
            'payload_brut' => $this->payload_brut,
            'statut_publication' => $this->statut_publication->value,
            'raw_content_id' => $this->raw_content_id,
            'created_at' => $this->created_at->format('d/m/Y'),
        ];
    }
}
