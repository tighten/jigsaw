@extends('_layouts.master')

@section('body')
<h2>Natural Sort Test, Descending</h2>

@foreach ($sort_tests_natural_descending as $item)
    <p>{{ $item->title }}</p>
@endforeach

@endsection
