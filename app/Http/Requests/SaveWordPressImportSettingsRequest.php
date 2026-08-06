<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveWordPressImportSettingsRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'administrator'; }

    public function rules(): array
    {
        $rules = ['options' => ['array'], 'options.duplicate_media' => [Rule::in(['skip', 'import'])], 'options.image_variants' => [Rule::in(['originals', 'all'])]];
        foreach (['posts', 'categories', 'tags', 'images', 'featured_images', 'seo', 'authors', 'comments', 'skip_ignored_files', 'preserve_upload_structure', 'verify_media_checksums', 'block_high_risk_executables', 'warn_suspicious_executables', 'ignore_protection_files'] as $key) $rules["options.{$key}"] = ['boolean'];
        return $rules;
    }

    public function importOptions(): array
    {
        $input = $this->validated('options', []);
        $booleanKeys = ['posts', 'categories', 'tags', 'images', 'featured_images', 'seo', 'authors', 'comments', 'skip_ignored_files', 'preserve_upload_structure', 'verify_media_checksums', 'block_high_risk_executables', 'warn_suspicious_executables', 'ignore_protection_files'];
        $options = [];
        foreach ($booleanKeys as $key) $options[$key] = ! empty($input[$key]);
        $options['duplicate_media'] = $input['duplicate_media'] ?? 'skip';
        $options['image_variants'] = $input['image_variants'] ?? 'originals';
        $options['skip_duplicate_media'] = $options['duplicate_media'] === 'skip';
        $options['import_duplicate_media'] = $options['duplicate_media'] === 'import';
        $options['import_original_images_only'] = $options['image_variants'] === 'originals';
        $options['import_generated_thumbnails'] = $options['image_variants'] === 'all';
        return $options;
    }
}
