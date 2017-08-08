@extends('layouts.app')

@section('content')
<!-- #section:basics/content.breadcrumbs -->
<div class="breadcrumbs" id="breadcrumbs">
    <script type="text/javascript">
        try{ace.settings.check('breadcrumbs' , 'fixed')}catch(e){}
    </script>

    <ul class="breadcrumb">
        <li class="active"><i class="ace-icon fa fa-dashboard home-icon"></i>Dashboard</li>
    </ul><!-- /.breadcrumb -->

    <!-- #section:basics/content.searchbox -->
    <!-- <div class="nav-search" id="nav-search">
        <form class="form-search">
            <span class="input-icon">
                <input type="text" placeholder="Search ..." class="nav-search-input" id="nav-search-input" autocomplete="off" />
                <i class="ace-icon fa fa-search nav-search-icon"></i>
            </span>
        </form>
    </div> --><!-- /.nav-search -->

    <!-- /section:basics/content.searchbox -->
</div>

<!-- /section:basics/content.breadcrumbs -->
<div class="page-content">
    <div class="page-header">
        <h1>
            Dashboard
        </h1>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="space-6"></div>

            <div class="row">
                <div class="col-sm-12 infobox-container">
                    <div class="infobox infobox-green">
                        <div class="infobox-icon">
                            <i class="ace-icon fa fa-folder"></i>
                        </div>
                        <div class="infobox-data">
                            <span class="infobox-data-number">{{ $countCategories }}</span>
                            <div class="infobox-content"><a href="{{ url('/categories') }}">danh mục</a> hoạt động</div>
                        </div>
                    </div>

                    <div class="infobox infobox-green">
                        <div class="infobox-icon">
                            <i class="ace-icon fa fa-cube"></i>
                        </div>
                        <div class="infobox-data">
                            <span class="infobox-data-number">{{ $countManufacturers }}</span>
                            <div class="infobox-content"><a href="{{ url('/manufacturers') }}">thương hiệu</a> hoạt động</div>
                        </div>
                    </div>

                    <div class="infobox infobox-green">
                        <div class="infobox-icon">
                            <i class="ace-icon fa fa-users"></i>
                        </div>
                        <div class="infobox-data">
                            <span class="infobox-data-number">{{ $countSuppliers }}</span>
                            <div class="infobox-content"><a href="{{ url('/suppliers') }}">nhà cung cấp</a> hoạt động</div>
                        </div>
                    </div>

                    <div class="infobox infobox-green">
                        <div class="infobox-icon">
                            <i class="ace-icon fa fa-cubes"></i>
                        </div>
                        <div class="infobox-data">
                            <span class="infobox-data-number">{{ $countProducts }}</span>
                            <div class="infobox-content"><a href="{{ url('/products') }}">sản phẩm</a> kích hoạt</div>
                        </div>
                    </div>

                    <div class="space-6"></div>

                    <div class="infobox infobox-blue">
                        <div class="infobox-icon">
                            <i class="ace-icon fa fa-cubes"></i>
                        </div>
                        <div class="infobox-data">
                            <span class="infobox-data-number">{{ $countProductsHasNoSuppliers }}</span>
                            <div class="infobox-content">sản phẩm không có nhà cung cấp</div>
                        </div>
                    </div>

                    <div class="infobox infobox-blue">
                        <div class="infobox-icon">
                            <i class="ace-icon fa fa-users"></i>
                        </div>
                        <div class="infobox-data">
                            <span class="infobox-data-number">{{ $countSuppliersHasNoProducts }}</span>
                            <div class="infobox-content">nhà cung cấp không có sản phẩm</div>
                        </div>
                    </div>

                    <div class="infobox infobox-orange2">
                        <div class="infobox-icon">
                            <i class="ace-icon fa fa-cube"></i>
                        </div>
                        <div class="infobox-data">
                            <span class="infobox-data-number">{{ $countProductsHasImportPriceExpired }}</span>
                            <div class="infobox-content">sản phẩm có giá nhập hết hạn</div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div>
@endsection
