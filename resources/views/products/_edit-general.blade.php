<div class="alert alert-success" ng-if="editProductForm.successful">
    Cập nhật sản phẩm thành công.
</div>

<div class="alert alert-danger" ng-show="editProductForm.errors.length > 0">
    <ul>
        <li ng-repeat="error in editProductForm.errors">@{{ error }}</li>
    </ul>
</div>

<form role="form" enctype="multipart/form-data">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Mã SKU</label>
                <p class="form-control-static"><strong>@{{ product.sku }}</strong></p>
            </div>

            <div class="form-group">
                <label>Danh mục</label>
                <p class="form-control-static"><strong>@{{ product.category.name }}</strong></p>
            </div>

            <div class="form-group">
                <label>Nhà SX</label>
                <p class="form-control-static"><strong>@{{ product.manufacturer.name }}</strong></p>
            </div>

            <div class="form-group" ng-if="product.color">
                <label>Màu sắc</label>
                <p class="form-control-static"><strong>@{{ product.color.name }}</strong></p>
            </div>

            <div class="form-group">
                <label for="name">Tên sản phẩm</label>
                <input type="text" class="form-control" placeholder="Tên sản phẩm" ng-model="editProductForm.name">
            </div>

            <div class="form-group">
                <label for="source_url">URL</label>
                <input type="text" class="form-control" placeholder="URL" ng-model="editProductForm.source_url">
                <span class="help-block">URL nguồn sản phẩm.</span>
            </div>

            <div class="form-group">
                <label for="description">Mô tả</label>
                <textarea class="form-control" placeholder="Mô tả sản phẩm" rows="5" ng-model="editProductForm.description"></textarea>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="status">Kích hoạt</label>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="status" value="1" class="ace ace-switch ace-switch-6" ng-model="editProductForm.status">
                    <span class="lbl"></span>
                </label>
            </div>

            <div class="form-group">
                <label for="channels">Kênh bán hàng</label>
                @foreach (config('teko.stores') as $k => $v)
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" class="ace" ng-model="editProductForm.channels[{{ $k }}]" />
                            <span class="lbl"> {{ $v }}</span>
                        </label>
                    </div>
                @endforeach
            </div>

            <div class="form-group">
                <label for="image">Ảnh sản phẩm</label>
                <input type="file" class="form-control" base-sixty-four-input ng-model="editProductForm.image_base64" accept="image/*">
            </div>

            <div class="form-group" ng-if="product.image">
                <label></label>
                <img ng-src="@{{ product.image }}" style="width: 100%">
            </div>
        </div>
    </div>

    <div class="clearfix form-actions">
        <div class="col-md-12">
            <button type="submit" class="btn btn-info" ng-click="update()" ng-disabled="editProductForm.disabled">
                <i class="ace-icon fa fa-save bigger-110"></i>Lưu
            </button>
        </div>
    </div>
</form>
