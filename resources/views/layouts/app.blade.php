<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laradmin') }}</title>

    <!-- bootstrap & fontawesome -->
    <link rel="stylesheet" href="/vendor/ace/assets/css/bootstrap.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">

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
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet">


    @yield('inline_styles')

    <!-- basic scripts -->

    <!--[if !IE]> -->
    <script type="text/javascript">
        window.jQuery || document.write("<script src='/vendor/ace/assets/js/jquery.js'>"+"<"+"/script>");
    </script>

    <!-- <![endif]-->

    <!--[if IE]>
    <script type="text/javascript">
        window.jQuery || document.write("<script src='/vendor/ace/assets/js/jquery1x.js'>"+"<"+"/script>");
    </script>
    <![endif]-->

    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/angular.js/1.5.8/angular.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js"></script>


    <!-- DataTables -->
    <!-- HTML5shiv and Respond.js for IE8 to support HTML5 elements and media queries -->
    <!--[if lte IE 8]>

    <script src="/vendor/ace/assets/js/html5shiv.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/angular-ui-bootstrap/2.0.0/ui-bootstrap-tpls.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/angular-ui-utils/0.1.1/angular-ui-utils.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.12/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
    <![endif]-->

    <!-- Scripts -->
    <script>
        window.Laradmin = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
    </script>
</head>
<body class="no-skin" ng-app="app" ng-cloak>
    <div id="app" ng-controller="AppController">
        @include('common.header')

        <div id="main-container" class="main-container">
            <script type="text/javascript">
                try{ace.settings.check('main-container' , 'fixed')}catch(e){}
            </script>

            @include('common.sidebar')

            <div class="main-content">
                <div class="main-content-inner">
                    @yield('content')
                </div>
            </div><!-- /.main-content -->

            @include('common.footer')
        </div>
    </div>


    <script type="text/javascript">
        if('ontouchstart' in document.documentElement) document.write("<script src='/vendor/ace/assets/js/jquery.mobile.custom.js'>"+"<"+"/script>");
    </script>
    <script src="/vendor/ace/assets/js/bootstrap.js"></script>

    @yield('scripts')

    <!-- ace scripts -->
    <script src="/vendor/ace/assets/js/ace/elements.scroller.js"></script>
    <script src="/vendor/ace/assets/js/ace/elements.colorpicker.js"></script>
    <script src="/vendor/ace/assets/js/ace/elements.fileinput.js"></script>
    <script src="/vendor/ace/assets/js/ace/elements.typeahead.js"></script>
    <script src="/vendor/ace/assets/js/ace/elements.wysiwyg.js"></script>
    <script src="/vendor/ace/assets/js/ace/elements.spinner.js"></script>
    <script src="/vendor/ace/assets/js/ace/elements.treeview.js"></script>
    <script src="/vendor/ace/assets/js/ace/elements.wizard.js"></script>
    <script src="/vendor/ace/assets/js/ace/elements.aside.js"></script>
    <script src="/vendor/ace/assets/js/ace/ace.js"></script>
    <script src="/vendor/ace/assets/js/ace/ace.ajax-content.js"></script>
    <script src="/vendor/ace/assets/js/ace/ace.touch-drag.js"></script>
    <script src="/vendor/ace/assets/js/ace/ace.sidebar.js"></script>
    <script src="/vendor/ace/assets/js/ace/ace.sidebar-scroll-1.js"></script>
    <script src="/vendor/ace/assets/js/ace/ace.submenu-hover.js"></script>
    <script src="/vendor/ace/assets/js/ace/ace.widget-box.js"></script>
    <script src="/vendor/ace/assets/js/ace/ace.settings.js"></script>
    <script src="/vendor/ace/assets/js/ace/ace.settings-rtl.js"></script>
    <script src="/vendor/ace/assets/js/ace/ace.settings-skin.js"></script>
    <script src="/vendor/ace/assets/js/ace/ace.widget-on-reload.js"></script>
    <script src="/vendor/ace/assets/js/ace/ace.searchbox-autocomplete.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

    <!-- Scripts -->
    <script src="{{ mix('/js/app.js') }}"></script>

    @yield('inline_scripts')

    @include('flash-message::sweetalert')
</body>
</html>
