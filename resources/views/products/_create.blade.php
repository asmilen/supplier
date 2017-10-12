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

            <form role="form">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Danh mục</label>
                            <p class="form-control-static"><strong>{{ $category->name }}</strong></p>
                        </div>

                        <div class="form-group">
                            <label for="manufacturer_id">Nhà SX</label>
                            <select class="select2" ng-model="addProductForm.manufacturer_id" placeholder="-- Chọn nhà sản xuất --" select2>
                                <option value=""></option>
                                <option ng-repeat="manufacturer in manufacturers" value="@{{ manufacturer.id }}">@{{ manufacturer.name }}</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="color_id">Màu sắc</label>
                            <select class="form-control" ng-model="addProductForm.color_id">
                                <option value="">--Chọn Màu sắc--</option>
                                <option ng-repeat="color in colors" value="@{{ color.id }}">@{{ color.name }}</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="name">Tên sản phẩm</label>
                            <input type="text" class="form-control" placeholder="Tên sản phẩm" ng-model="addProductForm.name">
                        </div>

                        <div class="form-group">
                            <label for="source_url">URL</label>
                            <input type="text" class="form-control" placeholder="URL" ng-model="addProductForm.source_url">
                            <span class="help-block">URL nguồn sản phẩm.</span>
                        </div>

                        <div class="form-group">
                            <label for="description">Mô tả</label>
                            <textarea class="form-control" placeholder="Mô tả sản phẩm" rows="5" ng-model="addProductForm.description"></textarea>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="status">Kích hoạt</label>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="status" value="1" class="ace ace-switch ace-switch-6" ng-model="addProductForm.status">
                                <span class="lbl"></span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label for="channels">Kênh bán hàng</label>
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
