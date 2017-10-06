<div class="row">
    <div class="col-sm-8">
        <div class="panel panel-primary">
            <div class="panel-heading">Danh sách thuộc tính đang gán</div>
            <div class="panel-body">
                <table class="table table-striped table-bordered no-margin-bottom dataTable no-footer">
                    <tbody>
                        <tr ng-repeat="item in category.attributes">
                            <td>@{{ item.slug }}</td>
                            <td>@{{ item.name }}</td>
                            <td style="width: 20%">
                                <button class="btn btn-danger btn-sm" ng-click="detachAttribute(item)" ng-disabled="detachAttributeForm.disabled">
                                    <i class="ace-icon fa fa-arrow-left bigger-110"></i> Bỏ
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-sm-4">
        <div class="panel panel-default">
            <div class="panel-heading">Thuộc tính chưa gán</div>
            <div class="panel-body">
                <table class="table table-striped table-bordered no-margin-bottom dataTable no-footer">
                    <tbody>
                        <tr ng-repeat="item in unassignedAttributes">
                            <td>@{{ item.slug }}</td>
                            <td>@{{ item.name }}</td>
                            <td style="width: 20%">
                                <button class="btn btn-info btn-sm" ng-click="attachAttribute(item)" ng-disabled="attachAttributeForm.disabled">
                                    <i class="ace-icon fa fa-arrow-right bigger-110"></i> Thêm
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
