<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
</head>
<body class="no-skin" ng-app="app" ng-cloak>
    <div id="app" ng-controller="AppController">
        @include('partials.header')

        <div id="main-container" class="main-container">
            <script type="text/javascript">
                try{ace.settings.check('main-container' , 'fixed')}catch(e){}
            </script>

            @include('partials.sidebar')

            <div class="main-content">
                <div class="main-content-inner">
                    @yield('content')
                </div>
            </div><!-- /.main-content -->

            @include('partials.footer')
        </div>
    </div>

    @include('partials.scripts')

    @include('flash-message::sweetalert')
</body>
</html>
