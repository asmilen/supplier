angular
    .module('controllers.productCreate', [])
    .controller('ProductCreateController', ProductCreateController);

ProductCreateController.$inject = ['$scope', '$http', '$window'];

/* @ngInject */
function ProductCreateController($scope, $http, $window) {
    function productForm() {
        this.category_id = '';
        this.manufacturer_id = '';
        this.name = '';
        this.code = '';
        this.source_url = '';
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
        categoryId = $scope.productForm.category_id ? $scope.productForm.category_id : 0;

        $http.get('/api/categories/' + categoryId + '/attributes')
            .then(function (response) {
                $scope.attributes = response.data;

                _.each($scope.attributes, function (attribute) {
                    $scope.productForm.attributes[attribute.slug] = '';
                });
            });
    };

    $scope.getCategories();
    $scope.getManufacturers();
    $scope.refreshData();

    $scope.addProduct = function () {
        $scope.productForm.errors = [];
        $scope.productForm.disabled = true;
        $scope.productForm.successful = false;

        $http.post('/products', $scope.productForm)
            .then(function () {
                $scope.productForm.successful = true;
                $scope.productForm.disabled = false;

                $window.location.href = '/products';
            })
            .catch(function (response) {
                if (typeof response.data === 'object') {
                    $scope.productForm.errors = _.flatten(_.toArray(response.data));
                } else {
                    $scope.productForm.errors = ['Something went wrong. Please try again.'];
                }
                $scope.productForm.disabled = false;
            });
    };
}
