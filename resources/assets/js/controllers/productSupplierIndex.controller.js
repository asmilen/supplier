angular
    .module('controllers.productSupplierIndex', [])
    .controller('ProductSupplierIndexController', ProductSupplierIndexController);

ProductSupplierIndexController.$inject = ['$scope', '$http'];

/* @ngInject */
function ProductSupplierIndexController($scope, $http) {
    function addProductSupplierForm() {
        this.product_id = '';
        this.product_name = '';
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

    $scope.addProductSupplierForm = new addProductSupplierForm();
    $scope.productsListForm = new productsListForm();
    $scope.suppliersListForm = new suppliersListForm();

    $scope.showAddProductSupplierModal = function () {
        $('#modal-add-product-supplier').modal('show');
    }

    $scope.showSelectProductModal = function () {
        $('#modal-select-product').modal('show');
    }

    $scope.showSelectSupplierModal = function () {
        $('#modal-select-supplier').modal('show');
    }

    $scope.getProductsList = function (currentPage) {
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
}
