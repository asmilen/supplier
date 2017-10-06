angular
    .module('controllers.productEdit', [])
    .controller('ProductEditController', ProductEditController);

ProductEditController.$inject = ['$scope', '$http', '$window'];

/* @ngInject */
function ProductEditController($scope, $http, $window) {
    $scope.productIsLoaded = false;

    function editProductForm() {
        this.name = '';
        this.source_url = '';
        this.image = '';
        this.description = '';
        this.channel = {1: false, 2: false};
        this.status = true;
        this.errors = [];
        this.successful = false;
        this.disabled = false;
    };

    $scope.editProductForm = new editProductForm();

    $scope.getProduct = function () {
        $http.get('/products/' + PRODUCT_ID)
            .then(function (response) {
                $scope.product = response.data;

                $scope.productIsLoaded = true;

                $scope.populateProductForm();
            });
    };

    $scope.populateProductForm = function () {
        $scope.editProductForm.name = $scope.product.name;
        $scope.editProductForm.source_url = $scope.product.source_url;
        $scope.editProductForm.description = $scope.product.description;
        $scope.editProductForm.status = $scope.product.status;
        $scope.editProductForm.image = $scope.product.image;

        _.each($scope.product.channel.split(','), function (channelKey) {
            $scope.editProductForm.channel[channelKey] = true;
        });
    };

    $scope.getProduct();

    $scope.update = function () {
        $scope.editProductForm.errors = [];
        $scope.editProductForm.disabled = true;
        $scope.editProductForm.successful = false;

        $http.post('/products/' + PRODUCT_ID, $scope.editProductForm)
            .then(function () {
                $scope.editProductForm.successful = true;
                $scope.editProductForm.disabled = false;
            })
            .catch(function (response) {
                if (typeof response.data === 'object') {
                    $scope.editProductForm.errors = _.flatten(_.toArray(response.data));
                } else {
                    $scope.editProductForm.errors = ['Something went wrong. Please try again.'];
                }
                $scope.editProductForm.disabled = false;
            });
    };
}



