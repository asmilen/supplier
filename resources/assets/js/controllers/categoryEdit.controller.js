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
        this.disabled = false;
    }

    function attachAttributeForm() {
        this.disabled = false;
    }

    function detachAttributeForm() {
        this.disabled = false;
    }

    $scope.editCategoryForm = new editCategoryForm();
    $scope.attachAttributeForm = new attachAttributeForm();
    $scope.detachAttributeForm = new detachAttributeForm();

    $scope.getCategory = function () {
        $http.get('/categories/' + CATEGORY_ID)
            .then(response => {
                $scope.category = response.data;
                $scope.categoryLoaded = true;

                $scope.editCategoryForm.name = $scope.category.name;
                $scope.editCategoryForm.status = $scope.category.status;
            });
    }

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

    $scope.getUnassignedAttributes = function () {
        $http.get('/categories/' + CATEGORY_ID + '/unassigned-attributes')
            .then(response => {
                $scope.unassignedAttributes = response.data;
            });
    }

    $scope.refreshData = function () {
        $scope.getCategory();
        $scope.getUnassignedAttributes();
    }

    $scope.refreshData();

    $scope.attachAttribute = function (attribute) {
        $scope.attachAttributeForm.disabled = true;

        $http.post('/categories/' + CATEGORY_ID + '/attributes/' + attribute.id)
            .then(response => {
                $scope.refreshData();

                $scope.attachAttributeForm.disabled = false;
            })
    }

    $scope.detachAttribute = function (attribute) {
        $scope.detachAttributeForm.disabled = true;

        $http.delete('/categories/' + CATEGORY_ID + '/attributes/' + attribute.id)
            .then(response => {
                $scope.refreshData();

                $scope.detachAttributeForm.disabled = false;
            });
    }
}
