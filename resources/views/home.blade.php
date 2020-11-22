@extends('layouts.app')

@section('content')
<div class="container">
    <router-view :key="$route.path"></router-view>
</div>
@endsection
