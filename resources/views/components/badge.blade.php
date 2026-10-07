@props(['color' => 'slate'])
@php
    $styles = [
        'slate' => 'bg-slate-100 text-slate-600',
        'amber' => 'bg-saffron-50 text-saffron-700',
        'teal'  => 'bg-shifa-50 text-shifa-700',
        'green' => 'bg-emerald-50 text-emerald-700',
        'red'   => 'bg-rose-50 text-rose-700',
    ][$color] ?? 'bg-slate-100 text-slate-600';
@endphp
<span {{ $attributes->class("inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium $styles") }}>
    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>{{ $slot }}
</span>
