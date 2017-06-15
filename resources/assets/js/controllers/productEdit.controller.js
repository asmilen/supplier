angular
    .module('controllers.productEdit', [])
    .controller('ProductEditController', ProductEditController)
    .directive('select2',select2);

ProductEditController.$inject = ['$scope', '$http', '$window'];

/* @ngInject */
function ProductEditController($scope, $http, $window) {
    $scope.productIsLoaded = false;

    function productForm() {
        this.category_id = '';
        this.manufacturer_id = '';
        this.color_id = '';
        this.parent_id = '';
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

    $scope.getProduct = function () {
        $http.get('/api/products/' + PRODUCT_ID)
            .then(function (response) {
                $scope.product = response.data;

                if (! $scope.productIsLoaded) {
                    $scope.productIsLoaded = true;

                    $scope.populateProductForm();

                    $scope.refreshData();
                }
            });
    };

    $scope.populateProductForm = function () {
        $scope.productForm.category_id = $scope.product.category_id;
        $scope.productForm.manufacturer_id = $scope.product.manufacturer_id;
        $scope.productForm.color_id = $scope.product.color_id ;
        $scope.productForm.parent_id = $scope.product.parent_id ? $scope.product.parent_id : 0;
        $scope.productForm.name = $scope.product.name;
        $scope.productForm.code = $scope.product.code;
        $scope.productForm.source_url = $scope.product.source_url;
        $scope.productForm.description = $scope.product.description;
        $scope.productForm.status = $scope.product.status;
        $scope.productForm.attributes = $scope.product.attributes ? JSON.parse($scope.product.attributes) : {};
    };

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

    $scope.getColors = function () {
        $http.get('/api/colors')
            .then(function (response) {
                $scope.colors = response.data;
            });
    };

    $scope.getProductConfigurables = function () {
        $http.get('/api/products/configurable')
            .then(function (response) {
                $scope.productConfigurables = response.data;
            });
    };

    $scope.refreshData = function () {
        categoryId = $scope.productForm.category_id ? $scope.productForm.category_id : 0;

        $http.get('/api/categories/' + categoryId + '/attributes')
            .then(function (response) {
                $scope.attributes = response.data;

                productAttributes = $scope.product.attributes ? JSON.parse($scope.product.attributes) : {};

                _.each($scope.attributes, function (attribute) {
                    $scope.productForm.attributes[attribute.slug] = (attribute.slug in productAttributes) ?
                        productAttributes[attribute.slug] :
                        '';
                });
            })
            .catch(function () {
                $scope.attributes = {};
            });
    };

    $scope.getCategories();
    $scope.getManufacturers();
    $scope.getColors();
    $scope.getProductConfigurables();
    $scope.getProduct();

    $scope.updateProduct = function () {
        $scope.productForm.errors = [];
        $scope.productForm.disabled = true;
        $scope.productForm.successful = false;

        $http.put('/products/' + PRODUCT_ID, $scope.productForm)
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

function select2($timeout, $parse) {
    return {
        restrict: 'AC',
        require: 'ngModel',
        link: function(scope, element, attrs) {
            $timeout(function() {
                element.select2({
                    placeholder: attrs.placeholder,
                    allowClear: true,
                    width:'100%',
                });
                element.select2Initialized = true;
            });

            var refreshSelect = function() {
                if (!element.select2Initialized) return;
                $timeout(function() {
                    element.trigger('change');
                });
            };

            var recreateSelect = function () {
                if (!element.select2Initialized) return;
                $timeout(function() {
                    element.select2('destroy');
                    element.select2();
                });
            };

            scope.$watch(attrs.ngModel, refreshSelect);

            if (attrs.ngOptions) {
                var list = attrs.ngOptions.match(/ in ([^ ]*)/)[1];
                // watch for option list change
                scope.$watch(list, recreateSelect);
            }

            if (attrs.ngDisabled) {
                scope.$watch(attrs.ngDisabled, refreshSelect);
            }
        }
    };
};

