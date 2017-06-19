angular
    .module('controllers.categoryIndex', [])
    .controller('CategoryIndexController', CategoryIndexController);

CategoryIndexController.$inject = ['$scope', '$http'];

/* @ngInject */
function CategoryIndexController($scope, $http) {
    console.log('Category Index Controller');
    $scope.categoriesLoaded = false;

    function marginsForm() {
        this.category_id = '';
        this.margins = {
            1: 0,
            2: 0,
            3: 0
        };
        this.errors = [];
        this.disabled = false;
    }

    $scope.refreshData = function () {
        $http.get('/categories/all')
            .then(response => {
                $scope.categories = response.data;
                $scope.categoriesLoaded = true;
            });
    }

    $scope.refreshData();

    $scope.showEditMarginsModal = function (category) {
        $scope.marginCategoryName = category.name;

        $scope.marginsForm = new marginsForm();
        $scope.marginsForm.category_id = category.id;

        $http.get('/categories/' + category.id + '/margins')
            .then(response => {
                _.each(response.data, function (margin, regionId) {
                    $scope.marginsForm.margins[regionId] = margin.margin;
                });
            });

        $('#modal-edit-margins').modal('show');
    }

    $scope.updateMargins = function () {
        $scope.marginsForm.errors = [];
        $scope.marginsForm.disabled = true;

        $http.put('/categories/' + $scope.marginsForm.category_id + '/margins', {
                'north_region': this.marginsForm.margins[1],
                'middle_region': this.marginsForm.margins[2],
                'south_region': this.marginsForm.margins[3]
            })
            .then(response => {
                console.log(response.data);
                $scope.marginsForm = new marginsForm();
                $('#modal-edit-margins').modal('hide');
            })
            .catch(function (response) {
                if (typeof response.data === 'object') {
                    $scope.marginsForm.errors = _.flatten(_.toArray(response.data));
                } else {
                    $scope.marginsForm.errors = ['Something went wrong. Please try again.'];
                }
                $scope.marginsForm.disabled = false;
            });
    }
}
