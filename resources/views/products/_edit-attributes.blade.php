<div class="alert alert-success" ng-if="editProductAttributeForm.successful">
    Cập nhật thuộc tính sản phẩm thành công.
</div>

<div class="alert alert-danger" ng-show="editProductAttributeForm.errors.length > 0">
    <ul>
        <li ng-repeat="error in editProductAttributeForm.errors">@{{ error }}</li>
    </ul>
</div>

<form class="form-horizontal" role="form">
    <div class="form-group" ng-repeat="attribute in product.category.attributes track by attribute.slug">
        <label class="col-sm-3 control-label no-padding-right">@{{ attribute.name }}</label>
        <div class="col-sm-6">
            <input type="text" class="form-control" placeholder="@{{ attribute.name }}" ng-model="editProductAttributeForm.values[attribute.slug]" ng-if="attribute.frontend_input == 'text'">
            <textarea class="form-control" ng-model="editProductAttributeForm.values[attribute.slug]" ng-if="attribute.frontend_input == 'textarea'"></textarea>
            <select class="form-control" ng-model="editProductAttributeForm.values[attribute.slug]" ng-if="attribute.frontend_input == 'select'">
                <option value="">- Chọn @{{ attribute.name }} -</option>
                <option ng-repeat="option in attribute.options" value="@{{ option.id }}">@{{ option.value }}</option>
            </select>
            <select class="form-control" ng-model="editProductAttributeForm.values[attribute.slug]" ng-if="attribute.frontend_input == 'multiselect'" multiple>
                <option ng-repeat="option in attribute.options" value="@{{ option.id }}">@{{ option.value }}</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-9">
            <button type="submit" class="btn btn-info" ng-click="updateProductAttribute()" ng-disabled="editProductAttributeForm.disabled">
                <i class="ace-icon fa fa-save bigger-110"></i> Lưu
            </button>
        </div>
    </div>
</form>
