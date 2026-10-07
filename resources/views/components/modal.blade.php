@props(['show', 'title', 'width' => 'max-w-lg'])
<div x-data="{ open: @entangle($show) }" x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto p-4 sm:p-10"
     x-on:keydown.escape.window="open = false">
    <div x-show="open" x-transition.opacity class="fixed inset-0 bg-ink/40 backdrop-blur-[2px]" x-on:click="open = false"></div>
    <div x-show="open" x-transition class="relative w-full {{ $width }} bg-white rounded-2xl shadow-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-line">
            <h3 class="text-lg">{{ $title }}</h3>
            <button type="button" x-on:click="open = false" class="p-1.5 rounded-lg text-ink-mute hover:bg-paper" aria-label="إغلاق">
                <x-icon name="x" />
            </button>
        </div>
        <div class="p-6">{{ $slot }}</div>
    </div>
</div>
