import tinymce from 'tinymce/tinymce';
import 'tinymce/icons/default';
import 'tinymce/themes/silver';
import 'tinymce/models/dom';
import 'tinymce/plugins/advlist';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/image';
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/code';
import 'tinymce/skins/ui/oxide/skin.min.css';
import 'tinymce/skins/ui/oxide/content.min.css';
import 'tinymce/skins/content/default/content.min.css';
import './tinymce-lt';

export function initializeRichTextEditors() {
    const editors = document.querySelectorAll('[data-rich-text-editor]');

    editors.forEach((editor) => {
        if (editor.dataset.richTextEditorInitialized === 'true') {
            return;
        }

        editor.dataset.richTextEditorInitialized = 'true';

        tinymce.init({
            target: editor,
            language: 'lt',
            menubar: false,
            branding: false,
            promotion: false,
            license_key: 'gpl',
            height: 520,
            plugins: 'advlist autolink code image link lists',
            toolbar: [
                'undo redo | blocks | bold italic | bullist numlist | alignleft aligncenter alignright alignjustify | link image | code',
            ],
            contextmenu: 'link image',
            block_formats: 'Pastraipa=p; Antra\u0161t\u0117 1=h1; Antra\u0161t\u0117 2=h2; Antra\u0161t\u0117 3=h3',
            content_css: false,
            skin: false,
            content_style: `
                body { color: #1e293b; font-family: Arial, sans-serif; font-size: 16px; line-height: 1.7; }
                h1, h2, h3 { color: #0f172a; line-height: 1.2; }
                img { max-width: 100%; height: auto; }
                img.article-image-full { display: block; width: 100%; margin: 20px 0; }
                img.article-image-left { float: left; margin: 0 20px 16px 0; }
                img.article-image-right { float: right; margin: 0 0 16px 20px; }
                img.article-image-center { display: block; margin: 18px auto; }
                figure.image { display: table; margin: 20px auto; max-width: 100%; }
                figure.image img { display: block; max-width: 100%; }
                figure.image:has(img.article-image-full) { display: block; width: 100%; }
                figure.image img.article-image-full { width: 100%; }
                figure.image:has(img.article-image-left) { float: left; margin: 0 20px 16px 0; }
                figure.image:has(img.article-image-right) { float: right; margin: 0 0 16px 20px; }
                figcaption { color: #64748b; font-size: 13px; line-height: 1.5; margin-top: 8px; text-align: center; }
                @media (max-width: 640px) {
                    img.article-image-left,
                    img.article-image-right,
                    figure.image:has(img.article-image-left),
                    figure.image:has(img.article-image-right) { float: none; width: 100%; margin: 20px 0; }
                }
            `,
            image_advtab: true,
            image_caption: true,
            image_dimensions: true,
            image_title: true,
            automatic_uploads: true,
            file_picker_types: 'image',
            image_class_list: [
                { title: 'Be lygiavimo', value: '' },
                { title: 'Per vis\u0105 plot\u012f', value: 'article-image-full' },
                { title: 'Kair\u0117je', value: 'article-image-left' },
                { title: 'De\u0161in\u0117je', value: 'article-image-right' },
                { title: 'Centre', value: 'article-image-center' },
            ],
            images_upload_handler: (blobInfo, progress) => uploadEditorImage(editor, blobInfo, progress),
            setup: (tinyEditor) => {
                tinyEditor.on('change keyup undo redo', () => {
                    tinyEditor.save();
                });

                tinyEditor.on('init', () => {
                    editor.form?.addEventListener('submit', () => tinyEditor.save());
                });
            },
        });
    });
}

async function uploadEditorImage(editor, blobInfo, progress) {
    const uploadUrl = editor.dataset.imageUploadUrl;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!uploadUrl || !csrfToken) {
        throw new Error('Nuotraukos \u012fk\u0117limas \u0161iuo metu negalimas.');
    }

    const formData = new FormData();
    formData.append('file', blobInfo.blob(), blobInfo.filename());

    const response = await fetch(uploadUrl, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: formData,
    });

    progress(100);

    if (!response.ok) {
        throw new Error('Nepavyko \u012fkelti nuotraukos.');
    }

    const data = await response.json();

    if (!data.location) {
        throw new Error('Serveris negr\u0105\u017eino nuotraukos adreso.');
    }

    return data.location;
}
