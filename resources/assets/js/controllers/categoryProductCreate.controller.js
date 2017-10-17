angular
    .module('controllers.categoryProductCreate', [])
    .controller('CategoryProductCreateController', CategoryProductCreateController);

CategoryProductCreateController.$inject = ['$scope', '$http', '$window'];

/* @ngInject */
function CategoryProductCreateController($scope, $http, $window) {
    function addProductForm() {
        this.manufacturer_id = '';
        this.color_id = '';
        this.name = '';
        this.source_url = '';
        this.description = '';
        this.channels = {1: false, 2: false};
        this.status = true;
        this.image_base64 = null;
        this.errors = [];
        this.disabled = false;
        this.successful = false;
    }

    $scope.addProductForm = new addProductForm();

    $scope.getManufacturers = function () {
        $http.get('/api/manufacturers')
            .then(response => {
                $scope.manufacturers = response.data;
            });
    };

    $scope.getColors = function () {
        $http.get('/api/colors')
            .then(response => {
                $scope.colors = response.data;
            });
    };

    $scope.getManufacturers();
    $scope.getColors();

    $scope.store = function () {
        $scope.addProductForm.errors = [];
        $scope.addProductForm.disabled = true;

        $http.post('/categories/' + CATEGORY_ID + '/products', $scope.addProductForm)
            .then(response => {
                $scope.addProductForm.successful = true;
                $scope.addProductForm.disabled = false;

                $window.location.href = '/products/' + response.data.id + '/edit';
            })
            .catch(response => {
                if (typeof response.data === 'object') {
                    $scope.addProductForm.errors = _.flatten(_.toArray(response.data));
                } else {
                    $scope.addProductForm.errors = ['Something went wrong. Please try again.'];
                }
                $scope.addProductForm.disabled = false;
            });
    }
}
