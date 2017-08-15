@extends('layouts.app')

@section('content')
<!-- #section:basics/content.breadcrumbs -->
<div class="breadcrumbs" id="breadcrumbs">
    <script type="text/javascript">
        try{ace.settings.check('breadcrumbs' , 'fixed')}catch(e){}
    </script>

    <ul class="breadcrumb">
        <li>
            <i class="ace-icon fa fa-home home-icon"></i>
            <a href="{{ url('/dashboard') }}">Dashboard</a>
        </li>
        <li>
            <a href="{{ route('categories.index') }}">Danh mục</a>
        </li>
        <li class="active">Danh sách</li>
    </ul><!-- /.breadcrumb -->
    <!-- /section:basics/content.searchbox -->
</div>
<!-- /section:basics/content.breadcrumbs -->

<div class="page-content" id="categoryController" ng-controller="CategoryIndexController">
    <div class="page-header">
        <h1>
            Danh mục
            <small>
                <i class="ace-icon fa fa-angle-double-right"></i>
                Danh sách
            </small>
            <a class="btn btn-primary pull-right" href="{{ route('categories.create') }}">
                <i class="ace-icon fa fa-plus" aria-hidden="true"></i>
                <span class="hidden-xs">Thêm</span>
            </a>
        </h1>
    </div><!-- /.page-header -->
    <div class="row" ng-show="categoriesLoaded">
        <div class="col-xs-12">
            <table id="dataTables-categories" class="table table-striped table-bordered table-hover no-margin-bottom no-border-top">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Tên</th>
                        <th>Trạng thái</th>
                        <th>Margin</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="category in categories">
                        <td>@{{ category.code }}</td>
                        <td>@{{ category.name }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Show Edit Margins Modal -->
    <div class="modal fade" id="modal-edit-margins" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button " class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Quản lý Margin Danh mục @{{ marginCategoryName }}</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger" ng-show="marginsForm.errors.length > 0">
                        <ul>
                            <li ng-repeat="error in marginsForm.errors">@{{ error }}</li>
                        </ul>
                    </div>
                    <form class="form-horizontal" role="form">
                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Miền Bắc (%)</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" ng-model="marginsForm.margins[1]" placeholder="Miền Bắc" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Miền Trung (%)</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" ng-model="marginsForm.margins[2]" placeholder="Miền Trung" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">Miền Nam (%)</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" ng-model="marginsForm.margins[3]" placeholder="Miền Nam" />
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-success" ng-click="updateMargins()" ng-disabled="marginsForm.disabled"><i class="fa fa-save"></i> Cập nhật</button>
                </div>
            </div>
        </div>
    </div>
</div><!-- /.page-content -->
@endsection
@section('scripts')
    <script src="/vendor/ace/assets/js/dataTables/jquery.dataTables.js"></script>
    <script src="/vendor/ace/assets/js/dataTables/jquery.dataTables.bootstrap.js"></script>
@endsection
@section('inline_scripts')
    <script>
        $(function () {
            var datatable = $("#dataTables-categories").DataTable({
                searching: true,
                autoWidth: false,
                processing: true,
                serverSide: true,
                pageLength: 25,
                ajax: {
                    url: '{!! route('categories.datatables') !!}',
                    data: function (d) {
                        d.keyword = $('input[name=keyword]').val();
                        d.typeId = $('select[name=typeId]').val();
                        d.status = $('select[name=status]').val();
                    }
                },
                columns: [
                    {data: 'code', name: 'code'},
                    {data: 'name', name: 'name'},
                    {data: 'status', name: 'status'},
                    {data: 'margin', name: 'margin', orderable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ]
            });

            $('#search-form').on('submit', function(e) {
                datatable.draw();
                e.preventDefault();
            });
            $(document).on("click", ".orange", function () {
                    var data = datatable.row( $(this).parents('tr') ).data();
                angular.element('#categoryController').scope().$apply();
                angular.element('#categoryController').scope().showEditMarginsModal(data);
            });
        });


    </script>
@endsection
