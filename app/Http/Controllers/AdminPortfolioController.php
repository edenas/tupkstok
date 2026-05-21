<?php

namespace App\Http\Controllers;

use App\Models\PortfolioPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminPortfolioController extends Controller
{
    private const THUMBNAIL_DIRECTORY = 'portfolio-thumbnails';
    private const POST_IMAGE_DIRECTORY = 'portfolio-posts';

    /**
     * Display a listing of portfolio posts.
     */
    public function index()
    {
        $portfolioPosts = PortfolioPost::query()
            ->orderBy('position')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withPath(route('admin.portfolio'));

        return view('admin.portfolio', [
            'portfolioPosts' => $portfolioPosts,
        ]);
    }

    /**
     * Show the form for creating a portfolio post.
     */
    public function showCreateForm()
    {
        return view('admin.portfolio-create');
    }

    /**
     * Store a newly created portfolio post.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules());
        $validated['project_details'] = $this->normalizeProjectDetails($validated['project_details'] ?? []);
        $validated['project_details_en'] = $this->normalizeProjectDetails($validated['project_details_en'] ?? []);
        $validated['project_details_ru'] = $this->normalizeProjectDetails($validated['project_details_ru'] ?? []);
        $validated['thumbnail'] = $this->storeThumbnail($request);
        $validated['position'] = ((int) PortfolioPost::max('position')) + 1;

        if ($request->hasFile('post_image')) {
            $validated['post_image'] = $this->storePostImage($request);
        }

        PortfolioPost::create($validated);

        return redirect()->route('admin.portfolio')->with('success', 'Portfolio post created successfully.');
    }

    /**
     * Show the form for editing the specified portfolio post.
     */
    public function showEditForm(PortfolioPost $portfolioPost)
    {
        return view('admin.portfolio-edit', compact('portfolioPost'));
    }

    /**
     * Update the specified portfolio post in storage.
     */
    public function update(Request $request, PortfolioPost $portfolioPost)
    {
        $validated = $request->validate($this->validationRules(false));
        $validated['project_details'] = $this->normalizeProjectDetails($validated['project_details'] ?? []);
        $validated['project_details_en'] = $this->normalizeProjectDetails($validated['project_details_en'] ?? []);
        $validated['project_details_ru'] = $this->normalizeProjectDetails($validated['project_details_ru'] ?? []);

        if ($request->hasFile('thumbnail')) {
            if ($portfolioPost->thumbnail) {
                Storage::disk('public')->delete($portfolioPost->thumbnailPath());
            }

            $validated['thumbnail'] = $this->storeThumbnail($request);
        }

        if ($request->hasFile('post_image')) {
            if ($portfolioPost->post_image) {
                Storage::disk('public')->delete($portfolioPost->postImagePath());
            }

            $validated['post_image'] = $this->storePostImage($request);
        }

        $portfolioPost->update($validated);

        return redirect()->route('admin.portfolio')->with('success', 'Portfolio post updated successfully.');
    }

    /**
     * Update a portfolio post position, swapping with an existing post if needed.
     */
    public function updatePosition(Request $request, PortfolioPost $portfolioPost)
    {
        $validated = $request->validate([
            'position' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($portfolioPost, $validated): void {
            $currentPost = PortfolioPost::query()
                ->whereKey($portfolioPost->id)
                ->lockForUpdate()
                ->firstOrFail();

            $oldPosition = $currentPost->position;
            $newPosition = (int) $validated['position'];

            if ($oldPosition === $newPosition) {
                return;
            }

            $conflictingPost = PortfolioPost::query()
                ->whereKeyNot($currentPost->id)
                ->where('position', $newPosition)
                ->lockForUpdate()
                ->first();

            if ($conflictingPost) {
                $conflictingPost->update(['position' => $oldPosition]);
            }

            $currentPost->update(['position' => $newPosition]);
        });

        return redirect($this->portfolioPositionRedirectUrl($request))
            ->with('success', 'Portfolio order updated successfully.');
    }

    /**
     * Remove the thumbnail from the specified portfolio post.
     */
    public function destroyThumbnail(PortfolioPost $portfolioPost)
    {
        if ($portfolioPost->thumbnail) {
            Storage::disk('public')->delete($portfolioPost->thumbnailPath());
            $portfolioPost->update(['thumbnail' => null]);
        }

        return redirect()
            ->route('admin.portfolio.edit', $portfolioPost->id)
            ->with('success', 'Thumbnail removed successfully.');
    }

    /**
     * Remove the post image from the specified portfolio post.
     */
    public function destroyPostImage(PortfolioPost $portfolioPost)
    {
        if ($portfolioPost->post_image) {
            Storage::disk('public')->delete($portfolioPost->postImagePath());
            $portfolioPost->update(['post_image' => null]);
        }

        return redirect()
            ->route('admin.portfolio.edit', $portfolioPost->id)
            ->with('success', 'Post image removed successfully.');
    }

    /**
     * Remove the specified portfolio post from storage.
     */
    public function destroy(PortfolioPost $portfolioPost)
    {
        if ($portfolioPost->thumbnail) {
            Storage::disk('public')->delete($portfolioPost->thumbnailPath());
        }

        if ($portfolioPost->post_image) {
            Storage::disk('public')->delete($portfolioPost->postImagePath());
        }

        $portfolioPost->delete();
        $this->normalizePortfolioPositions();

        return redirect()->route('admin.portfolio')->with('success', 'Portfolio post deleted successfully.');
    }

    /**
     * Get validation rules for creating or updating a portfolio post.
     *
     * @return array<string, mixed>
     */
    private function validationRules(bool $isThumbnailRequired = true): array
    {
        return [
            'title' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'title_ru' => 'nullable|string|max:255',
            'category' => 'required|string|max:120',
            'category_en' => 'nullable|string|max:120',
            'category_ru' => 'nullable|string|max:120',
            'short_description' => 'nullable|string|max:1000',
            'short_description_en' => 'nullable|string|max:1000',
            'short_description_ru' => 'nullable|string|max:1000',
            'content_heading' => 'nullable|string|max:255',
            'content_heading_en' => 'nullable|string|max:255',
            'content_heading_ru' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:10000',
            'description_en' => 'nullable|string|max:10000',
            'description_ru' => 'nullable|string|max:10000',
            'project_details' => 'nullable|array',
            'project_details.*' => 'nullable|string|max:255',
            'project_details_en' => 'nullable|array',
            'project_details_en.*' => 'nullable|string|max:255',
            'project_details_ru' => 'nullable|array',
            'project_details_ru.*' => 'nullable|string|max:255',
            'thumbnail' => [
                $isThumbnailRequired ? 'required' : 'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'dimensions:ratio=1/1',
                'max:4096',
            ],
            'post_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
            'youtube_url' => [
                'nullable',
                'url',
                'max:255',
            ],
        ];
    }

    /**
     * Store the uploaded thumbnail using a sanitized original filename.
     */
    private function storeThumbnail(Request $request): string
    {
        $file = $request->file('thumbnail');
        $path = $this->uniqueImagePath(self::THUMBNAIL_DIRECTORY, $file->getClientOriginalName());

        return $file->storeAs(dirname($path), basename($path), 'public');
    }

    /**
     * Store the uploaded post image using a sanitized original filename.
     */
    private function storePostImage(Request $request): string
    {
        $file = $request->file('post_image');
        $path = $this->uniqueImagePath(self::POST_IMAGE_DIRECTORY, $file->getClientOriginalName(), 2);

        return $file->storeAs(dirname($path), basename($path), 'public');
    }

    /**
     * Get a storage-relative path that does not overwrite an existing file.
     */
    private function uniqueImagePath(string $directory, string $originalName, int $suffixPadding = 0): string
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $filename = $this->sanitizeImageFilename(pathinfo($originalName, PATHINFO_FILENAME));
        $path = $directory.'/'.$filename.'.'.$extension;
        $counter = 1;

        while (Storage::disk('public')->exists($path)) {
            $suffix = str_pad((string) $counter, $suffixPadding, '0', STR_PAD_LEFT);
            $path = $directory.'/'.$filename.'_'.$suffix.'.'.$extension;
            $counter++;
        }

        return $path;
    }

    /**
     * Convert an uploaded filename to a safe storage filename.
     */
    private function sanitizeImageFilename(string $filename): string
    {
        $filename = (string) Str::of($filename)
            ->ascii()
            ->lower()
            ->replaceMatches('/\s+/', '-')
            ->replaceMatches('/[^a-z0-9_-]+/', '')
            ->replaceMatches('/-+/', '-')
            ->trim('-_');

        return $filename !== '' ? $filename : 'thumbnail';
    }

    /**
     * Remove empty project detail values before saving.
     *
     * @param array<int, string|null> $projectDetails
     * @return array<int, string>|null
     */
    private function normalizeProjectDetails(array $projectDetails): ?array
    {
        $projectDetails = collect($projectDetails)
            ->map(fn ($detail) => trim((string) $detail))
            ->filter()
            ->values()
            ->all();

        return $projectDetails !== [] ? $projectDetails : null;
    }

    /**
     * Get a safe redirect target for position updates.
     */
    private function portfolioPositionRedirectUrl(Request $request): string
    {
        $fallback = route('admin.portfolio');
        $redirectTo = $request->input('redirect_to');

        if (! is_string($redirectTo) || $redirectTo === '') {
            return $fallback;
        }

        $adminPortfolioUrl = route('admin.portfolio');
        $adminPortfolioPath = parse_url($adminPortfolioUrl, PHP_URL_PATH) ?: '/admin/portfolio';
        $redirectPath = parse_url($redirectTo, PHP_URL_PATH);
        $redirectHost = parse_url($redirectTo, PHP_URL_HOST);

        if ($redirectHost && $redirectHost !== $request->getHost()) {
            return $fallback;
        }

        return $redirectPath === $adminPortfolioPath ? $redirectTo : $fallback;
    }

    /**
     * Ensure portfolio post positions are sequential with no gaps.
     */
    private function normalizePortfolioPositions(): void
    {
        PortfolioPost::query()
            ->orderBy('position')
            ->orderByDesc('created_at')
            ->get()
            ->each(function (PortfolioPost $portfolioPost, int $index): void {
                $position = $index + 1;

                if ($portfolioPost->position !== $position) {
                    $portfolioPost->update(['position' => $position]);
                }
            });
    }
}
