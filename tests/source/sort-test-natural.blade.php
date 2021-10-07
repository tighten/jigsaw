@extends('_layouts.master')

@section('body')
<h2>Natural Sort Test</h2>

@foreach ($sort_natural_tests as $item)
    <p>{{ $item->title }}</p>
@endforeach

@endsection
