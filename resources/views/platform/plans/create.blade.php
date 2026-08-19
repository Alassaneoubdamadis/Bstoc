@extends('platform.layout')
@section('title', 'Nouvelle offre')
@section('content')
<h1>Nouvelle offre</h1>
@include('platform.plans._form', ['plan' => null, 'action' => route('platform.plans.store'), 'method' => 'POST'])
@endsection
