<?php

namespace App\Livewire\Admin;

use App\Enums\RecordStatus;
use App\Models\Course;
use App\Models\CourseLesson;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class CourseBuilder extends Component
{
    use WithFileUploads;

    public Course $course;

    public ?int $selectedLessonId = null;

    /** @var array<string, mixed> */
    public array $lessonForm = [];

    public mixed $coverImage = null;

    public mixed $promotionalVideo = null;

    public mixed $syllabusFile = null;

    public mixed $lessonImage = null;

    public mixed $lessonVideo = null;

    /** @var array<int, mixed> */
    public array $lessonFiles = [];

    public function mount(Course $course): void
    {
        abort_unless(auth()->user()?->can('courses.update'), 403);

        $this->course = $course->load(['category', 'instructor', 'lessons']);
        $this->selectFirstLesson();
    }

    public function selectFirstLesson(): void
    {
        $lesson = $this->course->lessons()->orderBy('sort_order')->first();

        if ($lesson instanceof CourseLesson) {
            $this->selectLesson((int) $lesson->getKey());

            return;
        }

        $this->newLesson();
    }

    public function newLesson(): void
    {
        $this->selectedLessonId = null;
        $this->lessonForm = [
            'title' => '',
            'description' => '',
            'content' => '',
            'duration_minutes' => 0,
            'sort_order' => ($this->course->lessons()->max('sort_order') ?? 0) + 1,
            'is_preview' => false,
            'status' => 'active',
            'resources_text' => '',
        ];
        $this->resetLessonUploads();
    }

    public function selectLesson(int $lessonId): void
    {
        $lesson = $this->course->lessons()->findOrFail($lessonId);

        $status = $lesson->getAttribute('status');

        $resources = $lesson->getAttribute('resources');

        $this->selectedLessonId = (int) $lesson->getKey();
        $this->lessonForm = [
            'title' => $lesson->getAttribute('title'),
            'description' => $lesson->getAttribute('description'),
            'content' => $lesson->getAttribute('content'),
            'duration_minutes' => $lesson->getAttribute('duration_minutes'),
            'sort_order' => $lesson->getAttribute('sort_order'),
            'is_preview' => $lesson->getAttribute('is_preview'),
            'status' => $status instanceof RecordStatus ? $status->value : $status,
            'resources_text' => is_array($resources) ? implode(PHP_EOL, $resources) : '',
        ];
        $this->resetLessonUploads();
    }

    public function saveCourseMedia(): void
    {
        abort_unless(auth()->user()?->can('courses.update'), 403);

        $this->validate([
            'coverImage' => ['nullable', 'image', 'max:4096'],
            'promotionalVideo' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:102400'],
            'syllabusFile' => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:20480'],
        ]);

        $slug = (string) $this->course->getAttribute('slug');

        if ($this->coverImage instanceof TemporaryUploadedFile) {
            $this->course->setAttribute('image', $this->storeUploadedFile($this->coverImage, "courses/{$slug}"));
        }

        if ($this->promotionalVideo instanceof TemporaryUploadedFile) {
            $this->course->setAttribute('promotional_video_url', $this->storeUploadedFile($this->promotionalVideo, "courses/{$slug}/videos"));
        }

        if ($this->syllabusFile instanceof TemporaryUploadedFile) {
            $this->course->setAttribute('syllabus_file', $this->storeUploadedFile($this->syllabusFile, "courses/{$slug}/files"));
        }

        $this->course->save();
        $this->course->refresh();
        $this->reset(['coverImage', 'promotionalVideo', 'syllabusFile']);

        Flux::toast(variant: 'success', text: 'Media del curso guardada.');
    }

    public function saveLesson(): void
    {
        abort_unless(auth()->user()?->can('course-lessons.update') || auth()->user()?->can('course-lessons.create'), 403);

        $this->validate([
            'lessonForm.title' => ['required', 'string', 'max:255'],
            'lessonForm.description' => ['nullable', 'string'],
            'lessonForm.content' => ['nullable', 'string'],
            'lessonForm.duration_minutes' => ['required', 'integer', 'min:0'],
            'lessonForm.sort_order' => ['required', 'integer', 'min:0'],
            'lessonForm.is_preview' => ['boolean'],
            'lessonForm.status' => ['required', 'in:active,inactive'],
            'lessonForm.resources_text' => ['nullable', 'string'],
            'lessonImage' => ['nullable', 'image', 'max:4096'],
            'lessonVideo' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:102400'],
            'lessonFiles.*' => ['file', 'max:20480'],
        ]);

        $lesson = $this->selectedLessonId
            ? $this->course->lessons()->findOrFail($this->selectedLessonId)
            : new CourseLesson(['course_id' => $this->course->getKey()]);

        $lesson->fill([
            'course_id' => $this->course->getKey(),
            'title' => $this->lessonForm['title'],
            'description' => $this->lessonForm['description'] ?? null,
            'content' => $this->lessonForm['content'] ?? null,
            'duration_minutes' => $this->lessonForm['duration_minutes'],
            'sort_order' => $this->lessonForm['sort_order'],
            'is_preview' => (bool) ($this->lessonForm['is_preview'] ?? false),
            'status' => $this->lessonForm['status'],
            'resources' => $this->resourcesFromText((string) ($this->lessonForm['resources_text'] ?? '')),
        ]);

        $slug = (string) $this->course->getAttribute('slug');

        if ($this->lessonImage instanceof TemporaryUploadedFile) {
            $lesson->setAttribute('image', $this->storeUploadedFile($this->lessonImage, "courses/{$slug}/lessons/images"));
        }

        if ($this->lessonVideo instanceof TemporaryUploadedFile) {
            $lesson->setAttribute('video_url', $this->storeUploadedFile($this->lessonVideo, "courses/{$slug}/lessons/videos"));
        }

        $uploadedFiles = [];

        foreach ($this->lessonFiles as $file) {
            if ($file instanceof TemporaryUploadedFile) {
                $uploadedFiles[] = $this->storeUploadedFile($file, "courses/{$slug}/lessons/files");
            }
        }

        if ($uploadedFiles !== []) {
            $lesson->setAttribute('resources', array_values(array_unique(array_merge($lesson->getAttribute('resources') ?? [], $uploadedFiles))));
        }

        $lesson->save();
        $this->course->refresh()->load('lessons');
        $this->selectLesson((int) $lesson->getKey());

        Flux::toast(variant: 'success', text: 'Lección guardada.');
    }

    public function deleteLesson(int $lessonId): void
    {
        abort_unless(auth()->user()?->can('course-lessons.delete'), 403);

        $this->course->lessons()->findOrFail($lessonId)->delete();
        $this->course->refresh()->load('lessons');
        $this->selectFirstLesson();

        Flux::toast(variant: 'success', text: 'Lección eliminada.');
    }

    /**
     * @return Collection<int, CourseLesson>
     */
    public function lessons(): Collection
    {
        return CourseLesson::query()
            ->where('course_id', $this->course->getKey())
            ->orderBy('sort_order')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.admin.course-builder');
    }

    /**
     * @return list<string>
     */
    private function resourcesFromText(string $text): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];

        return array_values(array_filter(
            array_map(fn (string $line): string => trim($line), $lines),
            fn (string $line): bool => $line !== ''
        ));
    }

    private function storeUploadedFile(TemporaryUploadedFile $file, string $path): string
    {
        $storedPath = $file->store($path, 'public');

        if ($storedPath === false) {
            throw new \RuntimeException('No se pudo guardar el archivo subido.');
        }

        return $storedPath;
    }

    private function resetLessonUploads(): void
    {
        $this->reset(['lessonImage', 'lessonVideo', 'lessonFiles']);
    }
}
