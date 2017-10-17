angular
    .module('controllers.productEdit', ['naif.base64'])
    .controller('ProductEditController', ProductEditController);

ProductEditController.$inject = ['$scope', '$http', '$window'];

/* @ngInject */
function ProductEditController($scope, $http, $window) {
    $scope.productIsLoaded = false;

    function editProductForm() {
        this.name = '';
        this.source_url = '';
        this.description = '';
        this.channels = {1: false, 2: false};
        this.status = true;
        this.image_base64 = null;
        this.errors = [];
        this.successful = false;
        this.disabled = false;
    };

    function editProductAttributeForm() {
        this.values = {};
        this.errors = [];
        this.successful = false;
        this.disabled = false;
    }

    $scope.editProductForm = new editProductForm();
    $scope.editProductAttributeForm = new editProductAttributeForm();

    $scope.getProduct = function () {
        $http.get('/products/' + PRODUCT_ID)
            .then(response => {
                $scope.product = response.data;

                $scope.productIsLoaded = true;

                $scope.populateProductForm();

                $scope.populateProductAttributeForm();
            });
    };

    $scope.populateProductForm = function () {
        $scope.editProductForm.name = $scope.product.name;
        $scope.editProductForm.source_url = $scope.product.source_url;
        $scope.editProductForm.description = $scope.product.description;
        $scope.editProductForm.status = $scope.product.status;

        _.each($scope.product.channel.split(','), function (channelKey) {
            if (channelKey) {
                $scope.editProductForm.channels[channelKey] = true;
            }
        });
    };

    $scope.getProduct();

    $scope.update = function () {
        $scope.editProductForm.errors = [];
        $scope.editProductForm.disabled = true;
        $scope.editProductForm.successful = false;

        $http.put('/products/' + PRODUCT_ID, $scope.editProductForm)
            .then(function (response) {
                $scope.editProductForm.successful = true;
                $scope.editProductForm.disabled = false;

                $scope.product = response.data;
            })
            .catch(response => {
                if (typeof response.data === 'object') {
                    $scope.editProductForm.errors = _.flatten(_.toArray(response.data));
                } else {
                    $scope.editProductForm.errors = ['Something went wrong. Please try again.'];
                }
                $scope.editProductForm.disabled = false;
            });
    };

    $scope.populateProductAttributeForm = function () {
        var productAttributes = JSON.parse($scope.product.attributes);

        _.each($scope.product.category.attributes, function (attribute) {
            if (! productAttributes || typeof productAttributes[attribute.slug] == 'undefined') {
                $scope.editProductAttributeForm.values[attribute.slug] = (attribute.frontend_input == 'multiselect') ? [] : '';
            } else {
                $scope.editProductAttributeForm.values[attribute.slug] = (attribute.frontend_input == 'multiselect') ?
                    productAttributes[attribute.slug] :
                    '' + productAttributes[attribute.slug];
            }
        });
    }

    $scope.updateProductAttribute = function () {
        $scope.editProductAttributeForm.errors = [];
        $scope.editProductAttributeForm.disabled = true;
        $scope.editProductAttributeForm.successful = false;

        $http.put('/products/' + PRODUCT_ID + '/attributes', $scope.editProductAttributeForm)
            .then(response => {
                $scope.editProductAttributeForm.successful = true;
                $scope.editProductAttributeForm.disabled = false;
            })
            .catch(response => {
                if (typeof response.data === 'object') {
                    $scope.editProductAttributeForm.errors = _.flatten(_.toArray(response.data));
                } else {
                    $scope.editProductAttributeForm.errors = ['Something went wrong. Please try again.'];
                }
                $scope.editProductAttributeForm.disabled = false;
            });
    }
}
