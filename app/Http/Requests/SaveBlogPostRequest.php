<?php

namespace App\Http\Requests;

use App\Support\BlogCategories;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(BlogCategories::ALL)],
            'description' => ['nullable', 'string', 'max:50000'],
            'project_details' => ['nullable', 'array'],
            'project_details.author' => ['nullable', 'string', 'max:255'],
            'project_details.source' => ['nullable', 'string', 'max:255'],
            'project_details.show_disclaimer' => ['nullable', 'boolean'],
            'project_details.disclaimer_text' => ['nullable', 'string', 'max:2000'],
            'thumbnail' => [
                $this->routeIs('admin.blog.store') ? 'required' : 'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
            'youtube_url' => ['nullable', 'url', 'max:255'],
        ];
    }
}
