@extends('layouts.app')

@section('title', 'RODIS - Multi-Market Dashboard')

@section('styles')
@include('dashboard.partials.styles')
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8 w-full relative z-20">
    @include('dashboard.sections.monitor')
    @include('dashboard.sections.detail')
    <!-- @include('dashboard.sections.rodis')
        @include('dashboard.sections.doji')
        @include('dashboard.sections.manual')
        @include('dashboard.sections.history') -->
</div>
@endsection

@section('scripts')
@include('dashboard.partials.scripts')
@endsection