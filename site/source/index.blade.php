@extends('_layouts.master')

{{-- Meta Data --}}
@if ( $page->title = 'Page title' )@endif
@if ( $page->description= 'Description' )@endif
@if ( $page->keywords= 'Keywords, best' )@endif

{{-- Content --}}
@section('body')
<h1>Hello world!</h1>
@endsection
