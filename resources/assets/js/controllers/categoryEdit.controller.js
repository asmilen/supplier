angular
    .module('controllers.categoryEdit', [])
    .controller('CategoryEditController', CategoryEditController);

CategoryEditController.$inject = ['$scope', '$http'];

/* @ngInject */
function CategoryEditController($scope, $http) {
    $scope.categoryLoaded = false;

    function editCategoryForm() {
        this.name = '';
        this.status = false;
        this.errors = [];
        this.successful = false;
        this.busy = false;
    }

    $scope.editCategoryForm = new editCategoryForm();

    $scope.getCategory = function () {
        $http.get('/categories/' + CATEGORY_ID)
            .then(response => {
                $scope.category = response.data;
                $scope.categoryLoaded = true;

                $scope.editCategoryForm.name = $scope.category.name;
                $scope.editCategoryForm.status = $scope.category.status;
            })
    }

    $scope.getCategory();

    $scope.update = function () {
        $scope.editCategoryForm.errors = [];
        $scope.editCategoryForm.successful = false;
        $scope.editCategoryForm.disabled = true;

        $http.put('/categories/' + CATEGORY_ID, $scope.editCategoryForm)
            .then(response => {
                $scope.editCategoryForm.successful = true;
                $scope.editCategoryForm.disabled = false;
            })
            .catch(response => {
                if (typeof response.data === 'object') {
                    $scope.editCategoryForm.errors = _.flatten(_.toArray(response.data));
                } else {
                    $scope.editCategoryForm.errors = ['Something went wrong. Please try again.'];
                }
                $scope.editCategoryForm.disabled = false;
            });
    }
}
