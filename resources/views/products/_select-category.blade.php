<form class="form-horizontal" role="form" ng-controller="ProductCreateController">
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right">Danh mục</label>
        <div class="col-sm-6">
            <select class="select2" ng-model="selectCategoryForm.category_id" select2>
                <option value="">-- Chọn danh mục --</option>
                <option ng-repeat="category in categories" value="@{{ category.id }}">@{{ category.name }}</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-9">
            <button type="submit" class="btn btn-info" ng-click="selectCategory()" ng-disabled="selectCategoryForm.disabled">
                <i class="ace-icon fa fa-check-circle bigger-110"></i> Tiếp tục
            </button>
        </div>
    </div>
</form>
