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
        <li class="active">Phí vận chuyển</li>
    </ul><!-- /.breadcrumb -->
    <!-- /section:basics/content.searchbox -->
</div>
<!-- /section:basics/content.breadcrumbs -->

<div class="page-content" ng-controller="TransportFeeIndexController">
    <div class="page-header">
        <h1>
            Phí vận chuyển
        </h1>
    </div><!-- /.page-header -->
    <div class="row" ng-if="transportFeesLoaded">
        <div class="col-xs-12">
            <div class="alert alert-success" ng-show="transportFeeForm.successful">
                Cập nhật phí vận chuyển thành công.
            </div>

            <div class="alert alert-danger" ng-show="transportFeeForm.errors.length > 0">
                <ul>
                    <li ng-repeat="error in transportFeeForm.errors">@{{ error }}</li>
                </ul>
            </div>

            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Tỉnh / Thành</th>
                        <th>Phí (%)</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="transportFee in transportFees">
                        <td>@{{ ::transportFee.province_name }}</td>
                        <td width="30%">
                            <input type="text" class="form-control" ng-model="transportFeeForm.percentFees[transportFee.province_id]" ng-keyup="$event.keyCode == 13 ? updatePercentFee(transportFee.province_id) : null">
                        </td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
