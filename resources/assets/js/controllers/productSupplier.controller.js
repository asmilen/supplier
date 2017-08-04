angular
    .module('controllers.productSupplier', [
        'directives.fileread', 'directives.select2'
    ])
    .controller('ProductSupplierController', ProductSupplierController);

ProductSupplierController.$inject = ['$scope', '$http', '$window'];

/* @ngInject */
function ProductSupplierController($scope, $http, $window) {

    function productForm() {
        this.category_id = '';
        this.manufacturer_id = '';
        this.color_id = '';
        this.type = 'simple';
        this.parent_id = '0';
        this.name = '';
        this.code = '';
        this.source_url = '';
        this.image = {};
        this.description = '';
        this.status = true;
        this.attributes = {};
        this.errors = [];
        this.disabled = false;
        this.successful = false;
    };

    $scope.productForm = new productForm();
    $scope.getCategories = function () {
        $http.get('/api/categories')
            .then(function (response) {
                $scope.categories = response.data;
            });
    };

    $scope.getManufacturers = function () {
        $http.get('/api/manufacturers')
            .then(function (response) {
                $scope.manufacturers = response.data;
            });
    };

    $scope.refreshData = function () {
        if (categoryId = $scope.productForm.category_id) {
            $http.get('/api/categories/' + categoryId + '/attributes')
                .then(function (response) {
                    $scope.attributes = response.data;
                    _.each($scope.attributes, function (attribute) {
                        $scope.productForm.attributes[attribute.slug] = '';
                    });
                });
        }
    };

    $scope.getCategories();
    $scope.getManufacturers();
    $scope.refreshData();

}