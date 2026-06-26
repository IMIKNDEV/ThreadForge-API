<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBlueprintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'tone' => ['sometimes', 'string'],
            'max_hashtag' => ['sometimes', 'integer', 'between:0,30'],
            'max_characters' => ['sometimes', 'integer', 'between:1,280'],
            'banned_word' => ['nullable', 'string'],
            'extra_rules' => ['nullable', 'string'],
        ];
    }
}
