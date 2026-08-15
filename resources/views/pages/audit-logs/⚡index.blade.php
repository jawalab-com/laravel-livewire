<?php

use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

new #[Title('Audit Logs')] class extends Component {
    use WithPagination;

    public string $search = '';
    public string $eventFilter = '';

    public bool $showDetailsModal = false;

    #[Locked]
    public ?int $selectedActivityId = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        Gate::authorize('viewAny', Activity::class);
    }

    /**
     * Reset pagination when filters change.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedEventFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Get paginated activity logs.
     *
     * @return LengthAwarePaginator<int, Activity>
     */
    #[Computed]
    public function activities(): LengthAwarePaginator
    {
        return Activity::with(['causer', 'subject'])
            ->when($this->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                        ->orWhere('log_name', 'like', "%{$search}%")
                        ->orWhere('event', 'like', "%{$search}%");
                });
            })
            ->when($this->eventFilter, function ($query, $event) {
                $query->where('event', $event);
            })
            ->latest()
            ->paginate(15);
    }

    /**
     * Get the currently inspected activity record.
     */
    #[Computed]
    public function selectedActivity(): ?Activity
    {
        if (! $this->selectedActivityId) {
            return null;
        }

        return Activity::with(['causer', 'subject'])->find($this->selectedActivityId);
    }

    /**
     * Open details inspection modal for an activity record.
     */
    public function inspectActivity(int $activityId): void
    {
        $this->selectedActivityId = $activityId;
        $this->showDetailsModal = true;
    }

    /**
     * Close the details modal.
     */
    public function closeDetailsModal(): void
    {
        $this->showDetailsModal = false;
        $this->selectedActivityId = null;
    }
}; ?>

<div class="w-full max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ __('Audit Logs') }}</flux:heading>
            <flux:subheading size="lg">{{ __('Inspect recorded system events, user activities, and data modifications.') }}</flux:subheading>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="{{ __('Search description, event, or log name...') }}"
            icon="magnifying-glass"
            size="sm"
            class="max-w-xs"
            data-test="search-audit-logs"
        />

        <div class="flex items-center gap-2">
            <flux:select wire:model.live="eventFilter" size="sm" class="w-36">
                <flux:select.option value="">{{ __('All Events') }}</flux:select.option>
                <flux:select.option value="created">{{ __('Created') }}</flux:select.option>
                <flux:select.option value="updated">{{ __('Updated') }}</flux:select.option>
                <flux:select.option value="deleted">{{ __('Deleted') }}</flux:select.option>
            </flux:select>
        </div>
    </div>

    <flux:separator variant="subtle" />

    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900 overflow-hidden shadow-xs">
        @forelse ($this->activities as $activity)
            <div
                wire:key="activity-{{ $activity->id }}"
                class="flex flex-col sm:flex-row sm:items-center justify-between p-4 sm:p-5 gap-4 {{ ! $loop->last ? 'border-b border-zinc-200 dark:border-zinc-700' : '' }}"
                data-test="activity-row-{{ $activity->id }}"
            >
                <div class="flex items-start gap-3.5">
                    @if ($activity->causer && method_exists($activity->causer, 'initials'))
                        <flux:avatar
                            :name="$activity->causer->name"
                            :initials="$activity->causer->initials()"
                            class="size-9 shrink-0 mt-0.5"
                        />
                    @else
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-500 text-xs font-medium">
                            {{ __('SYS') }}
                        </div>
                    @endif

                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-medium text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $activity->causer ? $activity->causer->name : __('System / Guest') }}
                            </span>

                            @php
                                $eventColor = match($activity->event) {
                                    'created' => 'green',
                                    'updated' => 'blue',
                                    'deleted' => 'red',
                                    default => 'zinc',
                                };
                            @endphp

                            <flux:badge size="sm" :color="$eventColor">
                                {{ ucfirst($activity->event ?? 'event') }}
                            </flux:badge>

                            @if ($activity->subject)
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}
                                </span>
                            @endif
                        </div>

                        <p class="text-sm text-zinc-600 dark:text-zinc-300">
                            {{ $activity->description }}
                        </p>

                        <div class="text-xs text-zinc-400 dark:text-zinc-500">
                            <time datetime="{{ $activity->created_at->toIso8601String() }}" title="{{ $activity->created_at->toDayDateTimeString() }}">
                                {{ $activity->created_at->diffForHumans() }}
                            </time>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:self-center">
                    <flux:button
                        variant="ghost"
                        size="sm"
                        icon="eye"
                        wire:click="inspectActivity({{ $activity->id }})"
                        data-test="view-activity-{{ $activity->id }}"
                    >
                        {{ __('Details') }}
                    </flux:button>
                </div>
            </div>
        @empty
            <div class="p-12 text-center">
                <div class="mx-auto mb-3 flex size-12 items-center justify-center rounded-2xl bg-zinc-100 dark:bg-zinc-800 text-zinc-400">
                    <flux:icon.clipboard-document-list class="size-6" />
                </div>
                <p class="font-medium">{{ __('No audit logs recorded yet') }}</p>
                <flux:text variant="subtle" class="mt-1 text-sm">
                    {{ $search || $eventFilter ? __('No audit events matched your search filters.') : __('System activities and model changes will be logged here automatically.') }}
                </flux:text>
            </div>
        @endforelse
    </div>

    @if ($this->activities->hasPages())
        <div>
            {{ $this->activities->links() }}
        </div>
    @endif

    {{-- Details Inspection Modal --}}
    <flux:modal
        name="activity-details-modal"
        class="md:w-[600px]"
        wire:model="showDetailsModal"
        @close="closeDetailsModal"
    >
        @if ($this->selectedActivity)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Activity Details') }}</flux:heading>
                    <flux:subheading>{{ __('Recorded payload and attribute changes for this event.') }}</flux:subheading>
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                    <div>
                        <span class="text-xs text-zinc-400 block">{{ __('Event') }}</span>
                        <span class="font-medium capitalize">{{ $this->selectedActivity->event ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-zinc-400 block">{{ __('Logged At') }}</span>
                        <span class="font-medium">{{ $this->selectedActivity->created_at->format('M d, Y H:i:s') }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-zinc-400 block">{{ __('Actor') }}</span>
                        <span class="font-medium">{{ $this->selectedActivity->causer?->name ?? __('System') }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-zinc-400 block">{{ __('Subject') }}</span>
                        <span class="font-medium">{{ $this->selectedActivity->subject_type ? class_basename($this->selectedActivity->subject_type).' #'.$this->selectedActivity->subject_id : __('None') }}</span>
                    </div>
                </div>

                @php
                    $changes = $this->selectedActivity->attribute_changes ?? [];
                    $old = $changes['old'] ?? null;
                    $attributes = $changes['attributes'] ?? null;
                    $properties = $this->selectedActivity->properties;
                @endphp

                @if ($old || $attributes)
                    <div class="space-y-3">
                        <flux:heading size="sm">{{ __('Attribute Changes') }}</flux:heading>

                        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden text-xs">
                            <table class="w-full text-start">
                                <thead class="bg-zinc-50 dark:bg-zinc-800/60 border-b border-zinc-200 dark:border-zinc-700 text-zinc-500 font-medium">
                                    <tr>
                                        <th class="p-2.5 text-start">{{ __('Field') }}</th>
                                        @if ($old)
                                            <th class="p-2.5 text-start">{{ __('Old Value') }}</th>
                                        @endif
                                        <th class="p-2.5 text-start">{{ __('New Value') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @php
                                        $allKeys = array_unique(array_merge(array_keys((array) $old), array_keys((array) $attributes)));
                                    @endphp
                                    @foreach ($allKeys as $key)
                                        <tr>
                                            <td class="p-2.5 font-mono text-zinc-700 dark:text-zinc-300 font-medium">{{ $key }}</td>
                                            @if ($old)
                                                <td class="p-2.5 text-red-600 dark:text-red-400 font-mono">{{ is_array($old[$key] ?? null) ? json_encode($old[$key]) : ($old[$key] ?? 'null') }}</td>
                                            @endif
                                            <td class="p-2.5 text-emerald-600 dark:text-emerald-400 font-mono">{{ is_array($attributes[$key] ?? null) ? json_encode($attributes[$key]) : ($attributes[$key] ?? 'null') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if (! empty($properties) && (is_array($properties) ? count($properties) > 0 : (method_exists($properties, 'isNotEmpty') && $properties->isNotEmpty())))
                    <div class="space-y-2">
                        <flux:heading size="sm">{{ __('Custom Payload') }}</flux:heading>
                        <pre class="rounded-lg bg-zinc-950 p-4 text-xs font-mono text-zinc-100 overflow-x-auto">{{ json_encode($properties, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                @endif

                @if (! $old && ! $attributes && (empty($properties) || (is_array($properties) && count($properties) === 0)))
                    <div class="p-4 rounded-lg bg-zinc-50 dark:bg-zinc-800/40 text-center text-xs text-zinc-500">
                        {{ __('No additional attribute diffs or payload recorded for this event.') }}
                    </div>
                @endif

                <div class="flex justify-end">
                    <flux:button variant="filled" wire:click="closeDetailsModal">
                        {{ __('Close') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
