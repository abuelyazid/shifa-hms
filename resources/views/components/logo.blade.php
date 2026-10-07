@props(['dark' => false])
<div {{ $attributes->class('flex items-center gap-3') }}>
    <span class="relative grid place-items-center w-10 h-10 rounded-xl {{ $dark ? 'bg-shifa-600' : 'bg-white' }}">
        <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" aria-hidden="true">
            <path d="M9 3h6v6h6v6h-6v6H9v-6H3V9h6z" fill="{{ $dark ? '#fff' : '#127C6B' }}"/>
            <path d="M4 12h4l1.5-3 3 6 1.5-3h6" stroke="{{ $dark ? '#127C6B' : '#fff' }}" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </span>
    <span class="leading-tight">
        <span class="block text-xl font-bold {{ $dark ? 'text-ink' : 'text-white' }}">شفاء</span>
        <span class="block text-xs {{ $dark ? 'text-ink-mute' : 'text-shifa-200' }}">نظام إدارة المستشفى</span>
    </span>
</div>
