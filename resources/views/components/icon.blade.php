@props(['name', 'class' => 'w-5 h-5'])
@php
    $svg = file_get_contents(resource_path("svg/{$name}.svg"));
    $svg = preg_replace('/<!--.*?-->/s', '', $svg);
    $svg = preg_replace('/class="[^"]*"/', 'class="' . $class . '" aria-hidden="true"', $svg, 1);
@endphp
{!! $svg !!}
