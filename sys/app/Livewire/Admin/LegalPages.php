<?php

namespace App\Livewire\Admin;

use App\Models\LegalPage;
use App\Services\SiteConfigService;
use Illuminate\View\View;
use Livewire\Component;

class LegalPages extends Component
{
    public bool $showForm = false;

    public ?int $editingId = null;

    /** @var array<string, mixed> */
    public array $form = [
        'slug' => '',
        'title' => '',
        'status' => 'published',
        'blocks' => [],
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('legal-pages.view'), 403);
    }

    public function create(): void
    {
        abort_unless(auth()->user()?->can('legal-pages.create'), 403);
        $this->editingId = null;
        $this->form = ['slug' => '', 'title' => '', 'status' => 'published', 'blocks' => []];
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        abort_unless(auth()->user()?->can('legal-pages.update'), 403);
        $page = LegalPage::query()->findOrFail($id);
        $this->editingId = $page->id;
        $this->form = [
            'slug' => $page->slug,
            'title' => $page->title,
            'status' => $page->status instanceof \BackedEnum ? $page->status->value : $page->status,
            'blocks' => $page->blocks ?? [],
        ];
        $this->showForm = true;
    }

    public function addBlock(string $type): void
    {
        $this->form['blocks'][] = ['type' => $type, 'content' => ''];
    }

    public function removeBlock(int $index): void
    {
        unset($this->form['blocks'][$index]);
        $this->form['blocks'] = array_values($this->form['blocks']);
    }

    public function save(): void
    {
        $this->authorizeSave();

        $data = $this->validate([
            'form.slug' => ['required', 'string', 'max:255'],
            'form.title' => ['required', 'string', 'max:255'],
            'form.status' => ['required', 'in:draft,published,archived'],
            'form.blocks' => ['array'],
            'form.blocks.*.type' => ['required', 'in:heading,paragraph'],
            'form.blocks.*.content' => ['required', 'string'],
        ])['form'];

        $payload = $data + [
            'published_at' => $data['status'] === 'published' ? now() : null,
        ];

        if ($this->editingId) {
            LegalPage::query()->findOrFail($this->editingId)->update($payload);
        } else {
            LegalPage::query()->create($payload);
        }

        app(SiteConfigService::class)->clearCache();
        $this->showForm = false;
        session()->flash('status', 'Página legal guardada.');
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()?->can('legal-pages.delete'), 403);
        LegalPage::query()->findOrFail($id)->delete();
        app(SiteConfigService::class)->clearCache();
        session()->flash('status', 'Eliminada.');
    }

    public function render(): View
    {
        return view('livewire.admin.legal-pages', [
            'pages' => LegalPage::query()->orderBy('title')->get(),
        ]);
    }

    private function authorizeSave(): void
    {
        abort_unless(auth()->user()?->can($this->editingId ? 'legal-pages.update' : 'legal-pages.create'), 403);
    }
}
