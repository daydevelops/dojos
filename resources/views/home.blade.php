@extends('layouts.app')

@section('content')
<div class="container">
    @if (auth()->check())
    you are logged in
    @endif
    <router-view></router-view>
</div>
@endsection
