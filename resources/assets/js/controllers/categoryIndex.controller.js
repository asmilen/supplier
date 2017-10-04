angular
    .module('controllers.categoryIndex', [])
    .controller('CategoryIndexController', CategoryIndexController);

CategoryIndexController.$inject = ['$scope', '$http'];

/* @ngInject */
function CategoryIndexController($scope, $http) {
    $scope.categoriesLoaded = false;

    $scope.totalItems = 0;

    function searchForm() {
        this.q = '';
        this.sorting = 'code';
        this.direction = 'asc';
        this.page = 1;
        this.limit = 25;
    }

    $scope.searchForm = new searchForm();

    function marginsForm() {
        this.category_id = '';
        this.margins = {
            1: 5,
            2: 5,
            3: 5
        };
        this.errors = [];
        this.disabled = false;
    }

    $scope.refreshData = function () {
        $http.get('/categories/listing?q=' + $scope.searchForm.q +
            '&sorting=' + $scope.searchForm.sorting +
            '&direction=' + $scope.searchForm.direction +
            '&page=' + $scope.searchForm.page +
            '&limit=' + $scope.searchForm.limit)
            .then(response => {
                $scope.categories = response.data.data;
                $scope.totalItems = response.data.total_items;
                $scope.categoriesLoaded = true;
            });
    }

    $scope.refreshData();

    $scope.updateSorting = function (sorting) {
        if ($scope.searchForm.sorting == sorting) {
            if ($scope.searchForm.direction == 'asc') {
                $scope.searchForm.direction = 'desc';
            } else {
                $scope.searchForm.direction = 'asc';
            }
        } else {
            $scope.searchForm.direction = 'asc';
        }

        $scope.searchForm.sorting = sorting;

        $scope.refreshData();
    }

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
