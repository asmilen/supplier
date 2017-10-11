angular
    .module('controllers.productCreate', [])
    .controller('ProductCreateController', ProductCreateController);

ProductCreateController.$inject = ['$scope', '$http', '$window'];

/* @ngInject */
function ProductCreateController($scope, $http, $window) {
    function selectCategoryForm() {
        this.category_id = '';
        this.disabled = false;
    }

    $scope.selectCategoryForm = new selectCategoryForm();

    $scope.getCategories = function () {
        $http.get('/api/categories')
            .then(function (response) {
                $scope.categories = response.data;
            });
    };

    $scope.getCategories();

    $scope.selectCategory = function () {
        $scope.selectCategoryForm.disabled = true;

        if ($scope.selectCategoryForm.category_id == '') {
            alert('Vui lòng chọn danh mục.');

            $scope.selectCategoryForm.disabled = false;

            return false;
        }

        $window.location = '/products/create?category_id=' + $scope.selectCategoryForm.category_id;
    }
}
