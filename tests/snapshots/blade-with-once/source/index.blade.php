@extends('_layouts.main')

@section('structured-markup')
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "BlogPosting",
        }
    </script>
@endsection

@section('body')
Hello world!

<x-alert>Slot 1</x-alert>

<x-alert>Slot 2</x-alert>
@endsection
