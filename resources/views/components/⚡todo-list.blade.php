<?php

use App\Models\Todo;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Title('Todo List')] #[Layout('layouts.app')] class extends Component
{
    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('nullable|string|max:1000')]
    public string $description = '';

    public ?int $editingId = null;

    #[Validate('required|string|max:255')]
    public string $editTitle = '';

    #[Validate('nullable|string|max:1000')]
    public string $editDescription = '';

    public string $filter = 'all'; // all, active, completed

    public function getTodosProperty(): Collection
    {
        $query = auth()->user()->todos()->latest();

        return match ($this->filter) {
            'active' => $query->where('is_completed', false)->get(),
            'completed' => $query->where('is_completed', true)->get(),
            default => $query->get(),
        };
    }

    public function addTodo(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        auth()->user()->todos()->create([
            'title' => $this->title,
            'description' => $this->description,
        ]);

        $this->reset('title', 'description');
    }

    public function toggleComplete(int $id): void
    {
        $todo = auth()->user()->todos()->findOrFail($id);
        $todo->update(['is_completed' => !$todo->is_completed]);
    }

    public function startEditing(int $id): void
    {
        $todo = auth()->user()->todos()->findOrFail($id);
        $this->editingId = $id;
        $this->editTitle = $todo->title;
        $this->editDescription = $todo->description ?? '';
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editTitle' => 'required|string|max:255',
            'editDescription' => 'nullable|string|max:1000',
        ]);

        $todo = auth()->user()->todos()->findOrFail($this->editingId);
        $todo->update([
            'title' => $this->editTitle,
            'description' => $this->editDescription,
        ]);

        $this->cancelEdit();
    }

    public function cancelEdit(): void
    {
        $this->reset('editingId', 'editTitle', 'editDescription');
    }

    public function deleteTodo(int $id): void
    {
        auth()->user()->todos()->findOrFail($id)->delete();
    }

    public function clearCompleted(): void
    {
        auth()->user()->todos()->where('is_completed', true)->delete();
    }
};
?>

<div class="mx-auto w-full max-w-2xl space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">{{ __('Todo List') }}</flux:heading>
            <flux:text class="text-sm">
                {{ $this->todos->where('is_completed', false)->count() }} {{ __('remaining') }}
            </flux:text>
        </div>

        {{-- Add Todo Form --}}
        <form wire:submit="addTodo" class="space-y-3">
            <flux:input
                wire:model="title"
                :label="__('Title')"
                :placeholder="__('What needs to be done?')"
                required
            />

            <flux:textarea
                wire:model="description"
                :label="__('Description')"
                :placeholder="__('Optional details...')"
                rows="2"
                resize="none"
            />

            <div class="flex justify-end">
                <flux:button variant="primary" type="submit" icon="plus">
                    {{ __('Add Todo') }}
                </flux:button>
            </div>
        </form>

        <flux:separator />

        {{-- Filters --}}
        <div class="flex items-center justify-between">
            <flux:radio.group wire:model.live="filter" variant="segmented">
                <flux:radio value="all" label="{{ __('All') }}" />
                <flux:radio value="active" label="{{ __('Active') }}" />
                <flux:radio value="completed" label="{{ __('Completed') }}" />
            </flux:radio.group>

            @if($this->todos->where('is_completed', true)->count() > 0)
                <flux:button variant="subtle" size="sm" wire:click="clearCompleted" icon="trash">
                    {{ __('Clear completed') }}
                </flux:button>
            @endif
        </div>

        {{-- Todo List --}}
        <div class="space-y-2">
            @forelse($this->todos as $todo)
                <div
                    wire:key="todo-{{ $todo->id }}"
                    class="group flex items-start gap-3 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700 {{ $todo->is_completed ? 'bg-zinc-50 dark:bg-zinc-800/50' : 'bg-white dark:bg-zinc-900' }}"
                >
                    @if($editingId === $todo->id)
                        {{-- Edit Mode --}}
                        <div class="flex-1 space-y-3">
                            <flux:input
                                wire:model="editTitle"
                                :label="__('Title')"
                                required
                            />

                            <flux:textarea
                                wire:model="editDescription"
                                :label="__('Description')"
                                rows="2"
                                resize="none"
                            />

                            <div class="flex gap-2">
                                <flux:button variant="primary" size="sm" wire:click="saveEdit" icon="check">
                                    {{ __('Save') }}
                                </flux:button>
                                <flux:button variant="subtle" size="sm" wire:click="cancelEdit" icon="x-mark">
                                    {{ __('Cancel') }}
                                </flux:button>
                            </div>
                        </div>
                    @else
                        {{-- View Mode --}}
                        <flux:checkbox
                            wire:click="toggleComplete({{ $todo->id }})"
                            :checked="$todo->is_completed"
                            class="mt-1"
                        />

                        <div class="flex-1 min-w-0">
                            <flux:heading size="sm" class="{{ $todo->is_completed ? 'line-through opacity-50' : '' }}">
                                {{ $todo->title }}
                            </flux:heading>

                            @if($todo->description)
                                <flux:text class="mt-1 {{ $todo->is_completed ? 'line-through opacity-50' : '' }}">
                                    {{ $todo->description }}
                                </flux:text>
                            @endif

                            <flux:text class="mt-1 text-xs opacity-50">
                                {{ $todo->created_at->diffForHumans() }}
                            </flux:text>
                        </div>

                        <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <flux:button variant="subtle" size="xs" wire:click="startEditing({{ $todo->id }})" icon="pencil-square" />

                            <flux:button variant="subtle" size="xs" wire:click="deleteTodo({{ $todo->id }})" wire:confirm="{{ __('Are you sure you want to delete this todo?') }}" icon="trash" class="text-red-500 hover:text-red-700" />
                        </div>
                    @endif
                </div>
            @empty
                <div class="rounded-lg border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-600">
                    <flux:icon.clipboard-document-list class="mx-auto size-12 opacity-30" />
                    <flux:heading size="sm" class="mt-2">{{ __('No todos yet') }}</flux:heading>
                    <flux:text class="mt-1">{{ __('Add your first todo above to get started.') }}</flux:text>
                </div>
            @endforelse
        </div>
    </div>
</div>