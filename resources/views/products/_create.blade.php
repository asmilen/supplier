<div ng-controller="CategoryProductCreateController">
    <ul class="nav nav-tabs">
        <li class="active">
            <a data-toggle="tab" href="#tab-general">Thông tin chung</a>
        </li>

        <li>
            <a data-toggle="tab" href="#tab-attributes">Quản lý Thuộc tính</a>
        </li>
    </ul>

    <div class="tab-content">
        <div id="tab-general" class="tab-pane fade in active">
            <div class="alert alert-danger" ng-show="addProductForm.errors.length > 0">
                <ul>
                    <li ng-repeat="error in addProductForm.errors">@{{ error }}</li>
                </ul>
            </div>

            <form class="form-horizontal" role="form">
                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">Danh mục</label>
                    <div class="col-sm-6">
                        <p class="form-control-static"><strong>{{ $category->name }}</strong></p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">Nhà SX</label>
                    <div class="col-sm-6">
                        <select class="select2" ng-model="addProductForm.manufacturer_id" placeholder="-- Chọn nhà sản xuất --" select2>
                            <option value=""></option>
                            <option ng-repeat="manufacturer in manufacturers" value="@{{ manufacturer.id }}">@{{ manufacturer.name }}</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">Màu sắc</label>
                    <div class="col-sm-6">
                        <select class="form-control" ng-model="addProductForm.color_id">
                            <option value="">--Chọn Màu sắc--</option>
                            <option ng-repeat="color in colors" value="@{{ color.id }}">@{{ color.name }}</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">Tên sản phẩm</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" placeholder="Tên sản phẩm" ng-model="addProductForm.name">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">URL</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" placeholder="URL" ng-model="addProductForm.source_url">
                        <span class="help-block">URL nguồn sản phẩm.</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">Mô tả</label>
                    <div class="col-sm-6">
                        <textarea class="form-control" placeholder="Mô tả sản phẩm" rows="5" ng-model="addProductForm.description"></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">Kích hoạt</label>
                    <div class="col-sm-6">
                        @foreach (config('teko.stores') as $k => $v)
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" class="ace" ng-model="addProductForm.channels[{{ $k }}]" />
                                    <span class="lbl"> {{ $v }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="clearfix form-actions">
                    <div class="col-sm-12">
                        <button type="submit" class="btn btn-info" ng-click="store()" ng-disabled="addProductForm.disabled">
                            <i class="ace-icon fa fa-save bigger-110"></i> Lưu
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div id="tab-attributes" class="tab-pane fade">
            Under Construction.
        </div>
    </div>
</div>
