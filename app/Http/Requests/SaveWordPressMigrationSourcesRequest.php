<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Validator;

class SaveWordPressMigrationSourcesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'administrator';
    }

    public function rules(): array
    {
        return [
            'wordpress_xml' => ['required', 'file', 'max:'.config('wordpress-migration.max_xml_kb')],
            'wordpress_sql' => ['required', 'file', 'max:'.config('wordpress-migration.max_sql_kb')],
            'uploads_zip' => ['required', 'file', 'max:'.config('wordpress-migration.max_zip_kb')],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $this->validateXml($validator, $this->file('wordpress_xml'));
            $this->validateSql($validator, $this->file('wordpress_sql'));
            $this->validateZip($validator, $this->file('uploads_zip'));
        }];
    }

    private function validateXml(Validator $validator, ?UploadedFile $file): void
    {
        if (! $file || strtolower($file->getClientOriginalExtension()) !== 'xml') {
            $validator->errors()->add('wordpress_xml', 'Pasirinkite galiojantį XML failą.');
            return;
        }

        $handle = @fopen($file->getRealPath(), 'rb');
        $head = $handle ? fread($handle, 512) : false;
        if (is_resource($handle)) fclose($handle);
        if ($head === false || ! preg_match('/^\s*(?:\xEF\xBB\xBF)?<\?xml\b|^\s*<rss\b/i', $head)) {
            $validator->errors()->add('wordpress_xml', 'Failo turinys nėra galiojantis XML eksportas.');
        }
    }

    private function validateSql(Validator $validator, ?UploadedFile $file): void
    {
        if (! $file) return;
        $name = strtolower($file->getClientOriginalName());
        if (! (str_ends_with($name, '.sql') || str_ends_with($name, '.sql.gz') || str_ends_with($name, '.gz'))) {
            $validator->errors()->add('wordpress_sql', 'Galimi SQL formatai: sql, sql.gz arba gz.');
            return;
        }
        $head = file_get_contents($file->getRealPath(), false, null, 0, 2);
        if (str_ends_with($name, '.gz') && $head !== "\x1f\x8b") {
            $validator->errors()->add('wordpress_sql', 'Suglaudintas SQL failas neturi galiojančio GZIP parašo.');
        } elseif (str_ends_with($name, '.sql') && ($head === false || str_contains($head, "\0"))) {
            $validator->errors()->add('wordpress_sql', 'SQL failas turi būti tekstinis.');
        }
    }

    private function validateZip(Validator $validator, ?UploadedFile $file): void
    {
        if (! $file || strtolower($file->getClientOriginalExtension()) !== 'zip') {
            $validator->errors()->add('uploads_zip', 'Pasirinkite ZIP archyvą.');
            return;
        }
        $head = file_get_contents($file->getRealPath(), false, null, 0, 4);
        if (! in_array($head, ["PK\x03\x04", "PK\x05\x06", "PK\x07\x08"], true)) {
            $validator->errors()->add('uploads_zip', 'Failas neturi galiojančio ZIP parašo.');
        }
    }

    public function messages(): array
    {
        return ['*.required' => 'Pasirinkite visus tris šaltinio failus.', '*.max' => 'Failas viršija leistiną dydį.'];
    }
}
