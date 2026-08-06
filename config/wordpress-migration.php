<?php

return [
    'storage_path' => storage_path('app/wordpress-imports'),
    'workspace_path' => env('WORDPRESS_MIGRATION_WORKSPACE_PATH') ?: base_path('wordpress-import'),
    'workspace_uploads_directory_name' => 'uploads',
    'workspace_uploads_archive_pattern' => 'uploads.zip',
    'max_xml_kb' => (int) env('WORDPRESS_MIGRATION_MAX_XML_KB', 102400),
    'max_sql_kb' => (int) env('WORDPRESS_MIGRATION_MAX_SQL_KB', 1048576),
    'max_zip_kb' => (int) env('WORDPRESS_MIGRATION_MAX_ZIP_KB', 2097152),
    'max_extracted_bytes' => (int) env('WORDPRESS_MIGRATION_MAX_EXTRACTED_BYTES', 5368709120),
    'max_extracted_files' => (int) env('WORDPRESS_MIGRATION_MAX_EXTRACTED_FILES', 50000),
    'maximum_directory_total_size' => (int) env('WORDPRESS_MIGRATION_MAX_DIRECTORY_BYTES', 5368709120),
    'maximum_directory_file_count' => (int) env('WORDPRESS_MIGRATION_MAX_DIRECTORY_FILES', 50000),
    'max_compression_ratio' => (float) env('WORDPRESS_MIGRATION_MAX_COMPRESSION_RATIO', 200),
    'queue_stale_after_seconds' => (int) env('WORDPRESS_MIGRATION_QUEUE_STALE_AFTER_SECONDS', 120),
    'executable_inspection_max_bytes' => (int) env('WORDPRESS_MIGRATION_EXECUTABLE_INSPECTION_MAX_BYTES', 65536),
    'protection_file_max_bytes' => (int) env('WORDPRESS_MIGRATION_PROTECTION_FILE_MAX_BYTES', 1024),
    'image_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'],
    'rejected_extensions' => ['php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar', 'cgi', 'sh', 'bat', 'cmd', 'exe'],
];
