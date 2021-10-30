<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="https://kit.fontawesome.com/4719078df9.js" crossorigin="anonymous"></script>
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">


    <script>
	    window.App = {!! json_encode([
		    'signedIn' => Auth::check(),
            'user' => Auth::user(),
            'google_api_key' => env('GOOGLE_API_KEY'),
            'app_phase' => env('APP_PHASE')
		]) !!};
	</script>
</head>

<body class="bg-info">
    <div id="app">
        <nav class="navbar navbar-expand-md bg-primary navbar-light shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="/">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">Register</a>
                        </li>
                        @else
                        @if(auth()->user()->is_admin)
                        <li class="nav-item">
                            <router-link class="nav-link" to="/categories">Categories</router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/users" class="nav-link">Users</router-link>
                        </li>
                        @endif
                        <li class="nav-item">
                            <router-link class="nav-link" to="/dojos/new">New Dojo</router-link>
                        </li>
                        @if(env('APP_PHASE') > 0)
                        <li class="nav-item">
                            <a href="/billing" class="nav-link" target="_blank">Billing</a>
                        </li>
                        @endif
                        <li class="nav-item">
                            <a class="nav-link" href="/logout" onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                Logout
                            </a>
                            <form id="logout-form" action="/logout" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
        <flash></flash>
    </div>
    <div id="footer" class="m-0 pb-1 pt-4 container-fluid">
        <div class="row">
            <div class="col-sm-6 text-sm-left text-center">
                <p class="m-0">
                    Built by
                    <u>
                        <a href="https://daydevelops.com" class='text-dark'>DayDevelops</a>
                    </u>
                </p>
            </div>
            <div class="col-sm-6 text-sm-right text-center">
                <p class="m-0">
                    Have questions? Contact me
                    <u>
                        <a href="https://portfolio.daydevelops.com/contact" class='text-dark'>here</a>
                    </u>
                </p>
            </div>
        </div>
    </div>

    
    <div id="loader">
        <div class="sk-cube-grid">
            <div class="sk-cube sk-cube1"></div>
            <div class="sk-cube sk-cube2"></div>
            <div class="sk-cube sk-cube3"></div>
            <div class="sk-cube sk-cube4"></div>
            <div class="sk-cube sk-cube5"></div>
            <div class="sk-cube sk-cube6"></div>
            <div class="sk-cube sk-cube7"></div>
            <div class="sk-cube sk-cube8"></div>
            <div class="sk-cube sk-cube9"></div>
        </div>
    </div>

<script src="https://js.stripe.com/v3/"></script>

</body>

</html>