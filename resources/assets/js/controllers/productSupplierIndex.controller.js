angular
    .module('controllers.productSupplierIndex', [])
    .controller('ProductSupplierIndexController', ProductSupplierIndexController);

ProductSupplierIndexController.$inject = ['$scope', '$http'];

/* @ngInject */
function ProductSupplierIndexController($scope, $http) {
    $scope.productSuppliersLoaded = false;

    function searchProductSupplierForm() {
        this.category_id = '';
        this.manufacturer_id = '';
        this.supplier_id = '';
        this.q = '';
        this.state = '';
        this.page = 1;
        this.limit = 50;
        this.total_items = 0;
    }

    function addProductSupplierForm() {
        this.product_id = '';
        this.product_name = '';
        this.supplier_id = '';
        this.supplier_name = '';
        this.import_price = '';
        this.from_date = '';
        this.to_date = '';
        this.min_quantity = 0;
        this.price_recommend = 0;
        this.success = false;
        this.errors = [];
        this.disabled = false;
    }

    function editProductSupplierForm() {
        this.import_price = '';
        this.from_date = '';
        this.to_date = '';
        this.min_quantity = 0;
        this.price_recommend = 0;
        this.state = '';
        this.errors = [];
        this.disabled = false;
    }

    function productsListForm() {
        this.q = '';
        this.page = 1;
        this.limit = 10;
        this.total_items = 0;
    }

    function suppliersListForm() {
        this.q = '';
        this.page = 1;
        this.limit = 10;
        this.total_items = 0;
    }

    function updatePricesToMagentoForm() {
        this.disabled = false;
    }

    $scope.searchProductSupplierForm = new searchProductSupplierForm();
    $scope.addProductSupplierForm = new addProductSupplierForm();
    $scope.editProductSupplierForm = new editProductSupplierForm();
    $scope.productsListForm = new productsListForm();
    $scope.suppliersListForm = new suppliersListForm();
    $scope.updatePricesToMagentoForm = new updatePricesToMagentoForm();

    $scope.refreshData = function () {
        $http.get('/api/product-suppliers?page=' + $scope.searchProductSupplierForm.page +
            '&limit=' + $scope.searchProductSupplierForm.limit +
            '&category_id=' + $scope.searchProductSupplierForm.category_id +
            '&manufacturer_id=' + $scope.searchProductSupplierForm.manufacturer_id +
            '&supplier_id=' + $scope.searchProductSupplierForm.supplier_id +
            '&q=' + $scope.searchProductSupplierForm.q +
            '&state=' + $scope.searchProductSupplierForm.state)
            .then(function (response) {
                $scope.productSuppliers = response.data.data;
                $scope.productSuppliersLoaded = true;
                $scope.searchProductSupplierForm.total_items = response.data.total_items;
            });
    }

    $scope.refreshData();

    $scope.showAddProductSupplierModal = function () {
        $('#modal-add-product-supplier').modal('show');
    }

    $scope.showSelectProductModal = function () {
        $('#modal-select-product').modal('show');
    }

    $scope.showSelectSupplierModal = function () {
        $('#modal-select-supplier').modal('show');
    }

    $scope.getProductsList = function () {
        if ($scope.productsListForm.q.length < 2) {
            return;
        }

        $http.get('/api/products/search?q=' + $scope.productsListForm.q
            + '&page=' + $scope.productsListForm.page + '&limit=' + $scope.productsListForm.limit)
            .then(function (response) {
                $scope.productsList = response.data.data;
                $scope.productsListForm.total_items = response.data.total_items;
            });
    }

    $scope.selectProduct = function (product) {
        $scope.addProductSupplierForm.product_id = product.id;
        $scope.addProductSupplierForm.product_name = product.name + ' (' + product.sku + ')';

        $('#modal-select-product').modal('hide');
    }

    $scope.getSuppliersList = function () {
        $http.get('/api/suppliers/search?q=' + $scope.suppliersListForm.q
            + '&page=' + $scope.suppliersListForm.page + '&limit=' + $scope.suppliersListForm.limit)
            .then(function (response) {
                $scope.suppliersList = response.data.data;
                $scope.suppliersListForm.total_items = response.data.total_items;
            });
    }

    $scope.selectSupplier = function (supplier) {
        $scope.addProductSupplierForm.supplier_id = supplier.id;
        $scope.addProductSupplierForm.supplier_name = supplier.name;

        $('#modal-select-supplier').modal('hide');
    }

    $scope.addProductSupplier = function () {
        $scope.addProductSupplierForm.success = false;
        $scope.addProductSupplierForm.errors = [];
        $scope.addProductSupplierForm.disabled = true;

        $http.post('/product-suppliers', $scope.addProductSupplierForm)
            .then(function (response) {
                $scope.addProductSupplierForm.success = true;
                $scope.addProductSupplierForm = new addProductSupplierForm();

                $('#modal-add-product-supplier').modal('hide');
            })
            .catch(function (response) {
                if (typeof response.data === 'object') {
                    $scope.addProductSupplierForm.errors = _.flatten(_.toArray(response.data));
                } else {
                    $scope.addProductSupplierForm.errors = ['Something went wrong. Please try again.'];
                }
                $scope.addProductSupplierForm.disabled = false;
            });
    }

    $('.input-daterange').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true
    });

    $scope.stateText = function (state) {
        if (state == 0) {
            return 'Hết hàng';
        }

        if (state == 1) {
            return 'Còn hàng';
        }

        if (state == 2) {
            return 'Đặt hàng';
        }

        return 'N/A';
    }

    $scope.showEditProductSupplierModal = function (productSupplier) {
        $scope.editProductSupplier = productSupplier;
        $scope.editProductSupplierForm.import_price = productSupplier.import_price;
        $scope.editProductSupplierForm.from_date = productSupplier.from_date;
        $scope.editProductSupplierForm.to_date = productSupplier.to_date;
        $scope.editProductSupplierForm.min_quantity = productSupplier.min_quantity;
        $scope.editProductSupplierForm.price_recommend = productSupplier.price_recommend;
        $scope.editProductSupplierForm.state = productSupplier.state.toString();

        $('#modal-edit-product-supplier').modal('show');
    }

    $scope.updateProductSupplier = function (productSupplier) {
        $scope.editProductSupplierForm.errors = [];
        $scope.editProductSupplierForm.disabled = true;

        $http.put('/product-suppliers/' + productSupplier.id, $scope.editProductSupplierForm)
            .then(function (response) {
                $scope.editProductSupplierForm = new editProductSupplierForm();

                $scope.refreshData();

                $('#modal-edit-product-supplier').modal('hide');
            })
            .catch(function (response) {
                if (typeof response.data === 'object') {
                    $scope.editProductSupplierForm.errors = _.flatten(_.toArray(response.data));
                } else {
                    $scope.editProductSupplierForm.errors = ['Something went wrong. Please try again.'];
                }
                $scope.editProductSupplierForm.disabled = false;
            });
    }

    $scope.showUpdatePricesToMagentoModal = function () {
        $('#modal-update-prices-to-magento').modal('show');
    }

    $scope.updatePricesToMagento = function () {
        $scope.updatePricesToMagentoForm.disabled = true;

        $http.post('/product-suppliers/update-prices-to-magento')
            .then(function (response) {
                $scope.updatePricesToMagentoForm.disabled = false;

                $('#modal-update-prices-to-magento').modal('hide');
            });
    }
}
