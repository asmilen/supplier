angular
    .module('controllers.productEdit', [])
    .controller('ProductEditController', ProductEditController)
    .directive('select2',select2)
    .directive('datatable',datatable);

ProductEditController.$inject = ['$scope', '$http', '$window'];

/* @ngInject */
function ProductEditController($scope, $http, $window) {
    $scope.productIsLoaded = false;

    $scope.message = '';

    $scope.myCallback = function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
        $('td:eq(2)', nRow).bind('click', function() {
            $scope.$apply(function() {
                $scope.someClickHandler(aData);
            });
        });
        return nRow;
    };

    $scope.someClickHandler = function(info) {
        $scope.message = 'clicked: '+ info.price;
    };

    $scope.columnDefs = [
        { "mDataProp": "category", "aTargets":[0]},
        { "mDataProp": "name", "aTargets":[1] },
        { "mDataProp": "price", "aTargets":[2] }
    ];

    $scope.overrideOptions = {
        "bStateSave": true,
        "iCookieDuration": 2419200, /* 1 month */
        "bJQueryUI": true,
        "bPaginate": true,
        "bLengthChange": false,
        "bFilter": true,
        "bInfo": true,
        "bDestroy": true
    };


    $scope.sampleProductCategories = [

        {
            "name": "1948 Porsche 356-A Roadster",
            "price": 53.9,
            "category": "Classic Cars",
            "action":"x"
        },
        {
            "name": "1948 Porsche Type 356 Roadster",
            "price": 62.16,
            "category": "Classic Cars",
            "action":"x"
        },
        {
            "name": "1949 Jaguar XK 120",
            "price": 47.25,
            "category": "Classic Cars",
            "action":"x"
        }
        ,
        {
            "name": "1936 Harley Davidson El Knucklehead",
            "price": 24.23,
            "category": "Motorcycles",
            "action":"x"
        },
        {
            "name": "1957 Vespa GS150",
            "price": 32.95,
            "category": "Motorcycles",
            "action":"x"
        },
        {
            "name": "1960 BSA Gold Star DBD34",
            "price": 37.32,
            "category": "Motorcycles",
            "action":"x"
        }
        ,
        {
            "name": "1900s Vintage Bi-Plane",
            "price": 34.25,
            "category": "Planes",
            "action":"x"
        },
        {
            "name": "1900s Vintage Tri-Plane",
            "price": 36.23,
            "category": "Planes",
            "action":"x"
        },
        {
            "name": "1928 British Royal Navy Airplane",
            "price": 66.74,
            "category": "Planes",
            "action":"x"
        },
        {
            "name": "1980s Black Hawk Helicopter",
            "price": 77.27,
            "category": "Planes",
            "action":"x"
        },
        {
            "name": "ATA: B757-300",
            "price": 59.33,
            "category": "Planes",
            "action":"x"
        }

    ];

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

function datatable() {
    return {
        restrict: 'AC',
        require: 'ngModel',
        link: function(scope, element, attrs) {
            // apply DataTable options, use defaults if none specified by user
            var options = {};
            if (attrs.myTable.length > 0) {
                options = scope.$eval(attrs.myTable);
            } else {
                options = {
                    "bStateSave": true,
                    "iCookieDuration": 2419200, /* 1 month */
                    "bJQueryUI": true,
                    "bPaginate": false,
                    "bLengthChange": false,
                    "bFilter": false,
                    "bInfo": false,
                    "bDestroy": true
                };
            }

            // Tell the dataTables plugin what columns to use
            // We can either derive them from the dom, or use setup from the controller
            var explicitColumns = [];
            element.find('th').each(function(index, elem) {
                explicitColumns.push($(elem).text());
            });
            if (explicitColumns.length > 0) {
                options["aoColumns"] = explicitColumns;
            } else if (attrs.aoColumns) {
                options["aoColumns"] = scope.$eval(attrs.aoColumns);
            }

            // aoColumnDefs is dataTables way of providing fine control over column config
            if (attrs.aoColumnDefs) {
                options["aoColumnDefs"] = scope.$eval(attrs.aoColumnDefs);
            }

            if (attrs.fnRowCallback) {
                options["fnRowCallback"] = scope.$eval(attrs.fnRowCallback);
            }

            // apply the plugin
            var dataTable = element.dataTable(options);



            // watch for any changes to our data, rebuild the DataTable
            scope.$watch(attrs.aaData, function(value) {
                var val = value || null;
                if (val) {
                    dataTable.fnClearTable();
                    dataTable.fnAddData(scope.$eval(attrs.aaData));
                }
            });
        }
    };
}

