<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlueprintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'tone' => ['required', 'string'],
            'max_hashtag' => ['required', 'integer', 'between:0,30'],
            'max_characters' => ['required', 'integer', 'between:1,280'],
            'banned_word' => ['nullable', 'string'],
            'extra_rules' => ['nullable', 'string'],
        ];
    }
}
