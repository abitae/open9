<section class="space-y-4">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ $course->title }}</flux:heading>
            <flux:text class="text-xs">
                {{ $course->category->name }} / {{ $course->instructor->name }} / {{ $this->lessons()->count() }} lecciones
            </flux:text>
        </div>

        <div class="flex gap-2">
            <flux:button size="sm" icon="arrow-left" variant="ghost" :href="route('admin.courses.index')" wire:navigate>
                Cursos
            </flux:button>
            <flux:button size="sm" icon="plus" wire:click="newLesson">
                Lección
            </flux:button>
        </div>
    </div>

    <form wire:submit="saveCourseMedia" class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
        <div class="grid gap-3 lg:grid-cols-[14rem_1fr_1fr_1fr_auto]">
            <div>
                <flux:heading size="sm">Media del curso</flux:heading>
                <flux:text class="text-xs">Portada, video de presentación y temario.</flux:text>
            </div>

            <label class="space-y-1 text-xs">
                <span class="font-medium text-zinc-600 dark:text-zinc-300">Imagen de portada</span>
                <input type="file" wire:model="coverImage" accept="image/*" class="block w-full text-xs file:mr-3 file:rounded-md file:border-0 file:bg-zinc-900 file:px-3 file:py-1.5 file:text-xs file:text-white dark:file:bg-zinc-100 dark:file:text-zinc-900">
                @if ($course->image)
                    <span class="block truncate text-zinc-500">{{ $course->image }}</span>
                @endif
            </label>

            <label class="space-y-1 text-xs">
                <span class="font-medium text-zinc-600 dark:text-zinc-300">Video de presentación</span>
                <input type="file" wire:model="promotionalVideo" accept="video/mp4,video/webm,video/quicktime" class="block w-full text-xs file:mr-3 file:rounded-md file:border-0 file:bg-zinc-900 file:px-3 file:py-1.5 file:text-xs file:text-white dark:file:bg-zinc-100 dark:file:text-zinc-900">
                @if ($course->promotional_video_url)
                    <span class="block truncate text-zinc-500">{{ $course->promotional_video_url }}</span>
                @endif
            </label>

            <label class="space-y-1 text-xs">
                <span class="font-medium text-zinc-600 dark:text-zinc-300">Archivo de temario</span>
                <input type="file" wire:model="syllabusFile" accept=".pdf,.doc,.docx,.ppt,.pptx" class="block w-full text-xs file:mr-3 file:rounded-md file:border-0 file:bg-zinc-900 file:px-3 file:py-1.5 file:text-xs file:text-white dark:file:bg-zinc-100 dark:file:text-zinc-900">
                @if ($course->syllabus_file)
                    <span class="block truncate text-zinc-500">{{ $course->syllabus_file }}</span>
                @endif
            </label>

            <div class="flex items-end justify-end">
                <flux:button type="submit" size="sm" icon="arrow-up-tray">Guardar</flux:button>
            </div>
        </div>
    </form>

    <div class="grid gap-4 xl:grid-cols-[22rem_1fr]">
        <aside class="rounded-lg border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between border-b border-zinc-200 px-3 py-2 dark:border-zinc-700">
                <flux:heading size="sm">Currícula</flux:heading>
                <span class="text-xs text-zinc-500">{{ $this->lessons()->sum('duration_minutes') }} min</span>
            </div>

            <div class="max-h-[34rem] overflow-y-auto p-2">
                @forelse ($this->lessons() as $lesson)
                    <button
                        type="button"
                        wire:click="selectLesson({{ $lesson->id }})"
                        class="mb-2 w-full rounded-md border px-3 py-2 text-left text-xs transition {{ $selectedLessonId === $lesson->id ? 'border-zinc-900 bg-zinc-100 dark:border-zinc-100 dark:bg-zinc-800' : 'border-zinc-200 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800' }}"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <span class="truncate font-medium">{{ $lesson->sort_order }}. {{ $lesson->title }}</span>
                            @if ($lesson->is_preview)
                                <span class="rounded bg-emerald-100 px-1.5 py-0.5 text-[10px] text-emerald-700">Vista previa</span>
                            @endif
                        </div>
                        <div class="mt-1 flex flex-wrap gap-2 text-[11px] text-zinc-500">
                            <span>{{ $lesson->duration_minutes }} min</span>
                            @if ($lesson->video_url)
                                <span>Video</span>
                            @endif
                            @if ($lesson->image)
                                <span>Imagen</span>
                            @endif
                            @if ($lesson->resources)
                                <span>{{ count($lesson->resources) }} archivos</span>
                            @endif
                        </div>
                    </button>
                @empty
                    <div class="px-3 py-8 text-center text-xs text-zinc-500">Aún no hay lecciones.</div>
                @endforelse
            </div>
        </aside>

        <form wire:submit="saveLesson" class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
            <div class="mb-3 flex items-center justify-between">
                <div>
                    <flux:heading size="sm">{{ $selectedLessonId ? 'Editar lección' : 'Nueva lección' }}</flux:heading>
                    <flux:text class="text-xs">Video, imagen, adjuntos y contenido de la lección.</flux:text>
                </div>

                @if ($selectedLessonId)
                    <flux:button type="button" size="sm" variant="danger" icon="trash" wire:confirm="¿Eliminar esta lección?" wire:click="deleteLesson({{ $selectedLessonId }})">
                        Eliminar
                    </flux:button>
                @endif
            </div>

            <div class="grid gap-3 lg:grid-cols-4">
                <div class="lg:col-span-2">
                    <flux:input size="sm" wire:model="lessonForm.title" label="Título" />
                </div>
                <flux:input size="sm" type="number" wire:model="lessonForm.sort_order" label="Orden" />
                <flux:input size="sm" type="number" wire:model="lessonForm.duration_minutes" label="Minutos" />

                <flux:select size="sm" wire:model="lessonForm.status" label="Estado">
                    <option value="active">Activo</option>
                    <option value="inactive">Inactivo</option>
                </flux:select>

                <div class="flex items-end lg:col-span-3">
                    <flux:checkbox wire:model="lessonForm.is_preview" label="Lección de vista previa gratuita" />
                </div>

                <div class="lg:col-span-2">
                    <flux:textarea size="sm" wire:model="lessonForm.description" label="Descripción" />
                </div>
                <div class="lg:col-span-2">
                    <flux:textarea size="sm" wire:model="lessonForm.content" label="Contenido" />
                </div>

                <label class="space-y-1 text-xs lg:col-span-2">
                    <span class="font-medium text-zinc-600 dark:text-zinc-300">Imagen de la lección</span>
                    <input type="file" wire:model="lessonImage" accept="image/*" class="block w-full text-xs file:mr-3 file:rounded-md file:border-0 file:bg-zinc-900 file:px-3 file:py-1.5 file:text-xs file:text-white dark:file:bg-zinc-100 dark:file:text-zinc-900">
                </label>

                <label class="space-y-1 text-xs lg:col-span-2">
                    <span class="font-medium text-zinc-600 dark:text-zinc-300">Video de la lección</span>
                    <input type="file" wire:model="lessonVideo" accept="video/mp4,video/webm,video/quicktime" class="block w-full text-xs file:mr-3 file:rounded-md file:border-0 file:bg-zinc-900 file:px-3 file:py-1.5 file:text-xs file:text-white dark:file:bg-zinc-100 dark:file:text-zinc-900">
                </label>

                <label class="space-y-1 text-xs lg:col-span-2">
                    <span class="font-medium text-zinc-600 dark:text-zinc-300">Subir recursos</span>
                    <input type="file" wire:model="lessonFiles" multiple class="block w-full text-xs file:mr-3 file:rounded-md file:border-0 file:bg-zinc-900 file:px-3 file:py-1.5 file:text-xs file:text-white dark:file:bg-zinc-100 dark:file:text-zinc-900">
                </label>

                <div class="lg:col-span-2">
                    <flux:textarea size="sm" wire:model="lessonForm.resources_text" label="Recursos existentes / enlaces externos" />
                </div>
            </div>

            @if ($errors->any())
                <div class="mt-3 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="mt-4 flex justify-end gap-2">
                <flux:button type="button" size="sm" variant="ghost" wire:click="newLesson">Limpiar</flux:button>
                <flux:button type="submit" size="sm" icon="check">Guardar lección</flux:button>
            </div>
        </form>
    </div>
</section>
