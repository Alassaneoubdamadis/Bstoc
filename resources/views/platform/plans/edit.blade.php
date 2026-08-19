@extends('platform.layout')
@section('title', 'Modifier offre')
@section('content')
<h1>Modifier {{ $plan->name }}</h1>
@include('platform.plans._form', ['plan' => $plan, 'action' => route('platform.plans.update', $plan), 'method' => 'PUT'])
@endsection
