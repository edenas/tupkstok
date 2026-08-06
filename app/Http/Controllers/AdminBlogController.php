<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveBlogPostRequest;
use App\Models\BlogPost;
use App\Services\BlogContentSanitizer;
use App\Services\BlogThumbnailService;
use App\Services\MediaLibraryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminBlogController extends Controller
{

    /**
     * Display a listing of blog posts.
     */
    public function index()
    {
        $blogPosts = BlogPost::query()
            ->orderBy('position')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withPath(route('admin.blog'));

        return view('admin.blog', [
            'blogPosts' => $blogPosts,
        ]);
    }

    /**
     * Show the form for creating a blog post.
     */
    public function showCreateForm()
    {
        return view('admin.blog-create');
    }

    /**
     * Store a newly created blog post.
     */
    public function store(SaveBlogPostRequest $request, BlogThumbnailService $blogThumbnailService, BlogContentSanitizer $blogContentSanitizer)
    {
        $validated = $request->validated();
        $validated['description'] = $blogContentSanitizer->sanitize($validated['description'] ?? null);
        $validated['project_details'] = $this->normalizeArticleInformation($validated['project_details'] ?? []);
        $validated['thumbnail'] = $blogThumbnailService->store($request->file('thumbnail'));
        $validated['position'] = ((int) BlogPost::max('position')) + 1;

        $blogPost = BlogPost::create($validated);

        return $this->redirectAfterSave($request, $blogPost)
            ->with('success', 'Straipsnis sėkmingai sukurtas.');
    }

    /**
     * Show the form for editing the specified blog post.
     */
    public function showEditForm(BlogPost $blogPost)
    {
        return view('admin.blog-edit', compact('blogPost'));
    }

    /**
     * Update the specified blog post in storage.
     */
    public function update(SaveBlogPostRequest $request, BlogPost $blogPost, BlogThumbnailService $blogThumbnailService, BlogContentSanitizer $blogContentSanitizer)
    {
        $validated = $request->validated();
        $validated['description'] = $blogContentSanitizer->sanitize($validated['description'] ?? null);
        $validated['project_details'] = $this->normalizeArticleInformation($validated['project_details'] ?? []);

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $blogThumbnailService->store($request->file('thumbnail'));

            if ($blogPost->thumbnail) {
                Storage::disk('public')->delete($blogPost->thumbnailPath());
            }
        }

        $blogPost->update($validated);

        return $this->redirectAfterSave($request, $blogPost)
            ->with('success', 'Straipsnis sėkmingai atnaujintas.');
    }

    public function uploadEditorImage(Request $request, MediaLibraryService $mediaLibrary): JsonResponse
    {
        $validated = $request->validate([
            'file' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
        ]);

        $path = $mediaLibrary->store($validated['file']);

        return response()->json([
            'location' => Storage::url($path),
        ]);
    }

    /**
     * Update a blog post position, swapping with an existing post if needed.
     */
    public function updatePosition(Request $request, BlogPost $blogPost)
    {
        $validated = $request->validate([
            'position' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($blogPost, $validated): void {
            $currentPost = BlogPost::query()
                ->whereKey($blogPost->id)
                ->lockForUpdate()
                ->firstOrFail();

            $oldPosition = $currentPost->position;
            $newPosition = (int) $validated['position'];

            if ($oldPosition === $newPosition) {
                return;
            }

            $conflictingPost = BlogPost::query()
                ->whereKeyNot($currentPost->id)
                ->where('position', $newPosition)
                ->lockForUpdate()
                ->first();

            if ($conflictingPost) {
                $conflictingPost->update(['position' => $oldPosition]);
            }

            $currentPost->update(['position' => $newPosition]);
        });

        return redirect($this->blogPositionRedirectUrl($request))
            ->with('success', 'Straipsnių eiliškumas sėkmingai atnaujintas.');
    }

    /**
     * Remove the thumbnail from the specified blog post.
     */
    public function destroyThumbnail(BlogPost $blogPost)
    {
        if ($blogPost->thumbnail) {
            Storage::disk('public')->delete($blogPost->thumbnailPath());
            $blogPost->update(['thumbnail' => null]);
        }

        return redirect()
            ->route('admin.blog.edit', $blogPost->id)
            ->with('success', 'Miniatiūra pašalinta sėkmingai.');
    }

    /**
     * Remove the specified blog post from storage.
     */
    public function destroy(BlogPost $blogPost)
    {
        if ($blogPost->thumbnail) {
            Storage::disk('public')->delete($blogPost->thumbnailPath());
        }

        $blogPost->delete();
        $this->normalizeBlogPositions();

        return redirect()->route('admin.blog')->with('success', 'Straipsnis sėkmingai ištrintas.');
    }

    /**
     * Normalize article metadata before saving it in the existing JSON column.
     *
     * @param array<string, mixed> $articleInformation
     * @return array{author: string|null, source: string|null, show_disclaimer: bool, disclaimer_text: string|null}
     */
    private function normalizeArticleInformation(array $articleInformation): array
    {
        $showDisclaimer = filter_var($articleInformation['show_disclaimer'] ?? true, FILTER_VALIDATE_BOOL);

        return [
            'author' => $this->nullableTrimmedString($articleInformation['author'] ?? null),
            'source' => $this->nullableTrimmedString($articleInformation['source'] ?? null),
            'show_disclaimer' => $showDisclaimer,
            'disclaimer_text' => $this->nullableTrimmedString($articleInformation['disclaimer_text'] ?? null),
        ];
    }

    private function nullableTrimmedString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function redirectAfterSave(Request $request, BlogPost $blogPost): RedirectResponse
    {
        if ($request->input('save_action') === 'return') {
            return redirect()->route('admin.blog');
        }

        return redirect()->route('admin.blog.edit', $blogPost->id);
    }

    /**
     * Get a safe redirect target for position updates.
     */
    private function blogPositionRedirectUrl(Request $request): string
    {
        $fallback = route('admin.blog');
        $redirectTo = $request->input('redirect_to');

        if (! is_string($redirectTo) || $redirectTo === '') {
            return $fallback;
        }

        $adminBlogUrl = route('admin.blog');
        $adminBlogPath = parse_url($adminBlogUrl, PHP_URL_PATH) ?: '/admin/blog';
        $redirectPath = parse_url($redirectTo, PHP_URL_PATH);
        $redirectHost = parse_url($redirectTo, PHP_URL_HOST);

        if ($redirectHost && $redirectHost !== $request->getHost()) {
            return $fallback;
        }

        return $redirectPath === $adminBlogPath ? $redirectTo : $fallback;
    }

    /**
     * Ensure blog post positions are sequential with no gaps.
     */
    private function normalizeBlogPositions(): void
    {
        BlogPost::query()
            ->orderBy('position')
            ->orderByDesc('created_at')
            ->get()
            ->each(function (BlogPost $blogPost, int $index): void {
                $position = $index + 1;

                if ($blogPost->position !== $position) {
                    $blogPost->update(['position' => $position]);
                }
            });
    }
}


