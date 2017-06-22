angular
    .module('controllers.productCreate', [])
    .controller('ProductCreateController', ProductCreateController)
    .directive('select2',select2)
    .directive("fileread", fileread);

ProductCreateController.$inject = ['$scope', '$http', '$window'];

/* @ngInject */
function ProductCreateController($scope, $http, $window) {

    function productForm() {
        this.category_id = '';
        this.manufacturer_id = '';
        this.color_id = '';
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

    $scope.getColors = function () {
        $http.get('/api/colors')
            .then(function (response) {
                $scope.colors = response.data;
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
    $scope.getColors();
    $scope.refreshData();

    $scope.addProduct = function () {
        $scope.productForm.errors = [];
        $scope.productForm.disabled = true;
        $scope.productForm.successful = false;
        var formData = new FormData();
        formData.append("image", $scope.productForm.image);
        $http({
            method  : 'POST',
            url     : '/products',
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData(data);
                for ( var key in data ) {
                    formData.append(key, data[key]);
                }
                return formData;
            },
            data : $scope.productForm,
            headers: {
                'Content-Type': undefined
            }
        }).success(function(data){
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

        // $http.post('/products', [$scope.productForm, formData], {
        //     headers: {'Content-Type': 'multipart/form-data'}
        // })
        //     .then(function () {
        //         // $scope.productForm.successful = true;
        //         // $scope.productForm.disabled = false;
        //         //
        //         // $window.location.href = '/products';
        //     })
        //     .catch(function (response) {
        //         if (typeof response.data === 'object') {
        //             $scope.productForm.errors = _.flatten(_.toArray(response.data));
        //         } else {
        //             $scope.productForm.errors = ['Something went wrong. Please try again.'];
        //         }
        //         $scope.productForm.disabled = false;
        //     });
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

function fileread() {
    return {
        scope: {
            fileread: "="
        },
        link: function (scope, element, attributes) {
            element.bind("change", function (changeEvent) {
                scope.$apply(function () {
                    scope.fileread = changeEvent.target.files[0];
                });
            });
        }
    }
}
