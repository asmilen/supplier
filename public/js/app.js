/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId])
/******/ 			return installedModules[moduleId].exports;
/******/
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// identity function for calling harmony imports with the correct context
/******/ 	__webpack_require__.i = function(value) { return value; };
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 6);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

var app = angular.module('app', ['controllers.app', 'controllers.productCreate', 'controllers.productEdit', 'controllers.productSaleprice']);

app.config(['$httpProvider', function ($httpProvider) {
    $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
}]);

__webpack_require__(2);
__webpack_require__(3);
__webpack_require__(4);
__webpack_require__(5);

/***/ }),
/* 1 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 2 */
/***/ (function(module, exports) {

angular.module('controllers.app', []).controller('AppController', AppController);

AppController.$inject = ['$scope', '$http'];

/* @ngInject */
function AppController($scope, $http) {
    console.log('Booting App Controller');
}

/***/ }),
/* 3 */
/***/ (function(module, exports) {

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

angular.module('controllers.productCreate', []).controller('ProductCreateController', ProductCreateController);

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
        this.description = '';
        this.status = true;
        this.attributes = {};
        this.errors = [];
        this.disabled = false;
        this.successful = false;
    };

    $scope.productForm = new productForm();

    $scope.getCategories = function () {
        $http.get('/api/categories').then(function (response) {
            $scope.categories = response.data;
        });
    };

    $scope.getManufacturers = function () {
        $http.get('/api/manufacturers').then(function (response) {
            $scope.manufacturers = response.data;
        });
    };

    $scope.getColors = function () {
        console.log(10);
        $http.get('/api/colors').then(function (response) {
            $scope.colors = response.data;
        });
    };

    $scope.refreshData = function () {
        categoryId = $scope.productForm.category_id ? $scope.productForm.category_id : 0;

        $http.get('/api/categories/' + categoryId + '/attributes').then(function (response) {
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

        $http.post('/products', $scope.productForm).then(function () {
            $scope.productForm.successful = true;
            $scope.productForm.disabled = false;

            $window.location.href = '/products';
        }).catch(function (response) {
            if (_typeof(response.data) === 'object') {
                $scope.productForm.errors = _.flatten(_.toArray(response.data));
            } else {
                $scope.productForm.errors = ['Something went wrong. Please try again.'];
            }
            $scope.productForm.disabled = false;
        });
    };
}

/***/ }),
/* 4 */
/***/ (function(module, exports) {

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

angular.module('controllers.productEdit', []).controller('ProductEditController', ProductEditController);

ProductEditController.$inject = ['$scope', '$http', '$window'];

/* @ngInject */
function ProductEditController($scope, $http, $window) {
    $scope.productIsLoaded = false;

    function productForm() {
        this.category_id = '';
        this.manufacturer_id = '';
        this.color_id = '';
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
        $http.get('/api/products/' + PRODUCT_ID).then(function (response) {
            $scope.product = response.data;

            if (!$scope.productIsLoaded) {
                $scope.productIsLoaded = true;

                $scope.populateProductForm();

                $scope.refreshData();
            }
        });
    };

    $scope.populateProductForm = function () {
        $scope.productForm.category_id = $scope.product.category_id;
        $scope.productForm.manufacturer_id = $scope.product.manufacturer_id;
        $scope.productForm.color_id = $scope.product.color_id;
        $scope.productForm.name = $scope.product.name;
        $scope.productForm.code = $scope.product.code;
        $scope.productForm.source_url = $scope.product.source_url;
        $scope.productForm.description = $scope.product.description;
        $scope.productForm.status = $scope.product.status;
        $scope.productForm.attributes = $scope.product.attributes ? JSON.parse($scope.product.attributes) : {};
    };

    $scope.getCategories = function () {
        $http.get('/api/categories').then(function (response) {
            $scope.categories = response.data;
        });
    };

    $scope.getManufacturers = function () {
        $http.get('/api/manufacturers').then(function (response) {
            $scope.manufacturers = response.data;
        });
    };

    $scope.getColors = function () {
        $http.get('/api/colors').then(function (response) {
            $scope.colors = response.data;
        });
    };

    $scope.refreshData = function () {
        categoryId = $scope.productForm.category_id ? $scope.productForm.category_id : 0;

        $http.get('/api/categories/' + categoryId + '/attributes').then(function (response) {
            $scope.attributes = response.data;

            productAttributes = $scope.product.attributes ? JSON.parse($scope.product.attributes) : {};

            _.each($scope.attributes, function (attribute) {
                $scope.productForm.attributes[attribute.slug] = attribute.slug in productAttributes ? productAttributes[attribute.slug] : '';
            });
        }).catch(function () {
            $scope.attributes = {};
        });
    };

    $scope.getCategories();
    $scope.getManufacturers();
    $scope.getColors();
    $scope.getProduct();

    $scope.updateProduct = function () {
        $scope.productForm.errors = [];
        $scope.productForm.disabled = true;
        $scope.productForm.successful = false;

        $http.put('/products/' + PRODUCT_ID, $scope.productForm).then(function () {
            $scope.productForm.successful = true;
            $scope.productForm.disabled = false;

            $window.location.href = '/products';
        }).catch(function (response) {
            if (_typeof(response.data) === 'object') {
                $scope.productForm.errors = _.flatten(_.toArray(response.data));
            } else {
                $scope.productForm.errors = ['Something went wrong. Please try again.'];
            }
            $scope.productForm.disabled = false;
        });
    };
}

/***/ }),
/* 5 */
/***/ (function(module, exports) {

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

angular.module('controllers.productSaleprice', []).controller('ProductSalepriceController', ProductSalepriceController);

ProductSalepriceController.$inject = ['$scope', '$http', '$window'];

/* @ngInject */
function ProductSalepriceController($scope, $http, $window) {
    $scope.productIsLoaded = false;

    function productSalepriceForm() {
        this.price = 0;
        this.stores = {
            1: false,
            2: false,
            3: false
        };
        this.errors = [];
        this.disabled = false;
        this.successful = false;
    };

    $scope.productSalepriceForm = new productSalepriceForm();

    $scope.updateMargin = function () {
        if (BEST_PRICE == 0) $scope.productMargin = 'Chưa có giá nhập';else $scope.productMargin = 'Lợi nhuận : ' + (($scope.productSalepriceForm.price / BEST_PRICE - 1) * 100).toFixed(2) + ' %';
    };

    $scope.updateMargin();

    $scope.getProduct = function () {
        $http.get('/api/products/' + PRODUCT_ID).then(function (response) {
            $scope.product = response.data;

            $scope.productIsLoaded = true;
        });
    };

    $scope.getProduct();

    $scope.update = function () {
        $scope.productSalepriceForm.errors = [];
        $scope.productSalepriceForm.disabled = true;
        $scope.productSalepriceForm.successful = false;

        $http.put('/products/' + PRODUCT_ID + '/saleprice', $scope.productSalepriceForm).then(function () {
            $scope.productSalepriceForm.successful = true;
            $scope.productSalepriceForm.disabled = false;
        }).catch(function (response) {
            if (_typeof(response.data) === 'object') {
                $scope.productSalepriceForm.errors = _.flatten(_.toArray(response.data));
            } else {
                $scope.productSalepriceForm.errors = ['Something went wrong. Please try again.'];
            }
            $scope.productSalepriceForm.disabled = false;
        });
    };
}

/***/ }),
/* 6 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(0);
module.exports = __webpack_require__(1);


/***/ })
/******/ ]);