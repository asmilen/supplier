@if (Sentinel::check())
<!-- #section:basics/sidebar -->
<div id="sidebar" class="sidebar responsive">
    <script type="text/javascript">
        try{ace.settings.check('sidebar' , 'fixed')}catch(e){}
    </script>

    @if ($currentUser->hasAnyAccess(['users.index', 'roles.index', 'permissions.index']))
    <div class="sidebar-shortcuts" id="sidebar-shortcuts">
        <div class="sidebar-shortcuts-large" id="sidebar-shortcuts-large">
            @if ($currentUser->hasAccess('users.index'))
            <a class="btn btn-success" href="{{ url('/users') }}">
                <i class="ace-icon fa fa-user"></i>
            </a>
            @endif

            @if ($currentUser->hasAccess('roles.index'))
            <a class="btn btn-info" href="{{ url('/roles') }}">
                <i class="ace-icon fa fa-users"></i>
            </a>
            @endif

            @if ($currentUser->hasAccess('permissions.index'))
            <a class="btn btn-warning" href="{{ url('/permissions') }}">
                <i class="ace-icon fa fa-lock"></i>
            </a>
            @endif
        </div>

        <div class="sidebar-shortcuts-mini" id="sidebar-shortcuts-mini">
            <span class="btn btn-success"></span>

            <span class="btn btn-info"></span>

            <span class="btn btn-warning"></span>

            <span class="btn btn-danger"></span>
        </div>
    </div><!-- /.sidebar-shortcuts -->
    @endif

    <ul class="nav nav-list">
        <li class="{{ (Request::is('dashboard') || Request::is('dashboard/*')) ? 'active' : '' }}">
            <a href="{{ url('/dashboard') }}"><i class="menu-icon fa fa-tachometer"></i> <span class="menu-text"> Dashboard </span></a>
        </li>

        @if ($currentUser->hasAccess('categories.index'))
        <li class="{{ (Request::is('categories') || Request::is('categories/*')) ? 'active' : '' }}">
            <a href="{{ url('/categories') }}"><i class="menu-icon fa fa-folder"></i> <span class="menu-text"> Danh mục </span></a>
        </li>
        @endif

        @if ($currentUser->hasAccess('manufacturers.index'))
        <li class="{{ (Request::is('manufacturers') || Request::is('manufacturers/*')) ? 'active' : '' }}">
            <a href="{{ url('/manufacturers') }}"><i class="menu-icon fa fa-cube"></i> <span class="menu-text"> Nhà SX </span></a>
        </li>
        @endif

        @if ($currentUser->hasAccess('colors.index'))
            <li class="{{ (Request::is('colors') || Request::is('colors/*')) ? 'active' : '' }}">
                <a href="{{ url('/colors') }}"><i class="menu-icon fa fa-paint-brush"></i> <span class="menu-text"> Màu sắc </span></a>
            </li>
        @endif

        @if ($currentUser->hasAccess('attributes.index'))
        <li class="{{ (Request::is('attributes') || Request::is('attributes/*')) ? 'active' : '' }}">
            <a href="{{ url('/attributes') }}"><i class="menu-icon fa fa-list-alt"></i> <span class="menu-text"> Thuộc tính </span></a>
        </li>
        @endif

        @if ($currentUser->hasAccess('products.index'))
        <li class="{{ (Request::is('products') || Request::is('products/*')) ? 'active' : '' }}">
            <a href="{{ url('/products') }}"><i class="menu-icon fa fa-cubes"></i> <span class="menu-text"> Sản phẩm </span></a>
        </li>
        @endif

        @if ($currentUser->hasAccess('bundles.index'))
            <li class="{{ (Request::is('bundles') || Request::is('bundles/*') || Request::is('bundleCategories') || Request::is('bundleCategories/*')) ? 'active open' : '' }}">
                <a href="#" class="dropdown-toggle">
                    <i class="menu-icon fa fa-list"></i>
                    <span class="menu-text"> Nhóm sản phẩm </span>

                    <b class="arrow fa fa-angle-down"></b>
                </a>

                <b class="arrow"></b>

                <ul class="submenu nav-show" style="display: none;">
                    <li class="{{ (Request::is('bundles') || Request::is('bundles/*')) ? 'active' : '' }}">
                        <a href="{{ url('/bundles') }}"><i class="menu-icon fa fa-caret-right"></i> <span class="menu-text">Danh sách nhóm sản phẩm </span></a>
                    </li>

                    <li class="{{ (Request::is('bundleCategories') || Request::is('bundleCategories/*')) ? 'active' : '' }}">
                        <a href="{{ url('/bundleCategories') }}"><i class="menu-icon fa fa-caret-right"></i> <span class="menu-text">Danh sách danh mục theo nhóm sản phẩm </span></a>
                    </li>
                </ul>
            </li>

        @endif

        @if ($currentUser->hasAccess('suppliers.getList'))
            <li class="{{ (Request::is('suppliers/getList') || Request::is('suppliers/*')) ? 'active' : '' }}">
                <a href="{{ url('/suppliers/getList') }}"><i class="menu-icon fa fa-users"></i> <span class="menu-text"> Nhà cung cấp </span></a>
            </li>
        @endif

        @if ($currentUser->hasAccess('suppliers.index'))
            <li class="{{ (Request::is('suppliers') || Request::is('suppliers/index')) ? 'active' : '' }}">
                <a href="{{ url('/suppliers') }}"><i class="menu-icon fa fa-cubes"></i> <span class="menu-text"> Sản phẩm theo NCC</span></a>
            </li>
        @endif

        @if ($currentUser->hasAccess('supplier.updatePrice'))
            <li class="{{ (Request::is('supplier') || Request::is('supplier/updatePrice')) ? 'active' : '' }}">
                <a href="{{ url('/supplier/updatePrice') }}"><i class="menu-icon fa fa-wrench"></i> <span class="menu-text"> Cập nhật giá </span></a>
            </li>
        @endif

        @if ($currentUser->hasAccess('margins.index'))
            <li class="{{ (Request::is('margins') || Request::is('margins/*')) ? 'active' : '' }}">
                <a href="{{ url('/margins') }}"><i class="menu-icon fa fa-percent"></i> <span class="menu-text"> Margin Order </span></a>
            </li>
        @endif
    </ul><!-- /.nav-list -->

    <!-- #section:basics/sidebar.layout.minimize -->
    <div class="sidebar-toggle sidebar-collapse" id="sidebar-collapse">
        <i class="ace-icon fa fa-angle-double-left" data-icon1="ace-icon fa fa-angle-double-left" data-icon2="ace-icon fa fa-angle-double-right"></i>
    </div>

    <!-- /section:basics/sidebar.layout.minimize -->
    <script type="text/javascript">
        try{ace.settings.check('sidebar' , 'collapsed')}catch(e){}
    </script>
</div>
<!-- /section:basics/sidebar -->
@endif
