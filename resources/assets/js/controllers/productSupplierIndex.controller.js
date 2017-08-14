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

    $scope.addProductSupplierForm = new addProductSupplierForm();

    $scope.showAddProductSupplierModal = function () {
        $('#modal-add-product-supplier').modal('show');
    }

    $scope.showSelectProductModal = function () {
        $('#modal-select-product').modal('show');
    }

    $scope.showSelectSupplierModal = function () {
        $('#modal-select-supplier').modal('show');
    }
}
