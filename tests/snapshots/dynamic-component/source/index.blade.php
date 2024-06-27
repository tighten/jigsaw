@php
    $dynamic = 'alert';
@endphp
<x-dynamic-component :component="$dynamic" title="Title">
    Slot
</x-dynamic-component>
