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

<script type="text/javascript">
    if('ontouchstart' in document.documentElement) document.write("<script src='/vendor/ace/assets/js/jquery.mobile.custom.js'>"+"<"+"/script>");
</script>
<script src="/vendor/ace/assets/js/bootstrap.js"></script>

@yield('scripts')

<!-- ace scripts -->
<script src="/vendor/ace/assets/js/ace/ace.js"></script>
<script src="/vendor/ace/assets/js/ace/ace.sidebar.js"></script>
<script src="/vendor/ace/assets/js/ace/ace.sidebar-scroll-1.js"></script>
<script src="/vendor/ace/assets/js/ace/ace.submenu-hover.js"></script>
<script src="/js/sweetalert.min.js"></script>

<!-- Scripts -->
<script src="{{ asset('/js/app.js') }}"></script>

@yield('inline_scripts')
