<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ config('app.name', 'Laradmin') }}</title>

<!-- bootstrap & fontawesome -->
<link rel="stylesheet" href="{{ asset('/vendor/ace/assets/css/bootstrap.css') }}" />
<link rel="stylesheet" href="{{ asset('/vendor/ace/assets/css/font-awesome.css') }}" />
<link rel="stylesheet" href="{{ asset('/css/sweetalert.min.css') }}">

@yield('styles')

<!-- ace styles -->
<link rel="stylesheet" href="/vendor/ace/assets/css/ace.css" />

<!--[if lte IE 9]>
<link rel="stylesheet" href="/vendor/ace/assets/css/ace-part2.css" />
<![endif]-->

<!--[if lte IE 9]>
<link rel="stylesheet" href="/vendor/ace/assets/css/ace-ie.css" />
<![endif]-->

<!-- Styles -->
<link href="{{ asset('/css/app.css') }}" rel="stylesheet">

@yield('inline_styles')

<!-- HTML5shiv and Respond.js for IE8 to support HTML5 elements and media queries -->
<!--[if lte IE 8]>
<script src="/vendor/ace/assets/js/html5shiv.js"></script>
<script src="/vendor/ace/assets/js/respond.js"></script>
<![endif]-->

<!-- Scripts -->
<script>
    window.Laradmin = {!! json_encode([
        'csrfToken' => csrf_token(),
        'regions' => config('teko.regions')
    ]) !!};
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/angular.js/1.5.8/angular.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js"></script>
