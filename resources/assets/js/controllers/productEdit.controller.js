angular
    .module('controllers.productEdit', [
        'directives.fileread', 'directives.select2'
    ])
    .controller('ProductEditController', ProductEditController);

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
        this.image = {};
        this.description = '';
        this.status = true;
        this.attributes = {};
        this.errors = [];
        this.disabled = false;
        this.successful = false;
        this.channel = '';
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
        $scope.productForm.image = $scope.product.image ? $scope.product.image : '';
        $scope.productForm.description = $scope.product.description;
        $scope.productForm.status = $scope.product.status;
        $scope.productForm.attributes = $scope.product.attributes ? JSON.parse($scope.product.attributes) : {};
        $scope.productForm.channel = $scope.product.channel.toString();
        $scope.channel = $scope.product.product_options;
        $scope.channels = $scope.product.channels;
        $scope.productForm.channel = [];
        $.each($scope.product.product_options, function (index, value) {
            if($scope.channels.indexOf(index.toString()) >= 0)
                $scope.productForm.channel[index] = 1;
            else
                $scope.productForm.channel[index] = 0;
        });
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
        $http.get('/api/products/getConfigurableList')
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

    $scope.removeChild = function (childId) {
        if (confirm("Are you sure?")) {
            $http.post('/products/' + PRODUCT_ID + '/removeChild/' + childId)
                .then(function (response) {
                });
            $window.location.reload();
        }
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
        $http({
            method  : 'POST',
            url     : '/products/' + PRODUCT_ID,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                for ( var key in data ) {
                    formData.append(key, data[key]);
                }

                return formData;
            },
            data : $scope.productForm,
            headers: {
                'Content-Type': undefined
            }
        }).success(function(response){
            $scope.productForm.successful = true;
            $scope.productForm.disabled = false;

            $window.location.href = '/products';
        }).catch(function (response) {
            if (typeof response.data === 'object') {
                $scope.productForm.errors = _.flatten(_.toArray(response.data));
            } else {
                $scope.productForm.errors = ['Something went wrong. Please try again.'];
            }
            $scope.productForm.disabled = false;
        });
    };
}



