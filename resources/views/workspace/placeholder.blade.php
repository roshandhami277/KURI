@extends('layouts.nav')

@section('title', $title)

@section('content')
    <div class="page-heading">
        <p>Kuri workspace</p>
        <h1>{{ $title }}</h1>
        <span>This page is ready to be built.</span>
    </div>

    <section class="empty-card">
        <strong>{{ $title }}</strong>
        <p>We will add this feature soon.</p>
    </section>
@endsection
