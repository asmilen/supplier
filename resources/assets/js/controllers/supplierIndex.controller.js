angular
    .module('controllers.supplierIndex', [])
    .controller('SupplierIndexController', SupplierIndexController);

SupplierIndexController.$inject = ['$scope', '$http'];

/* @ngInject */
function SupplierIndexController($scope, $http) {
    $scope.suppliersLoaded = false;

    $scope.totalItems = 0;

    $scope.countAll = 0;

    function searchForm() {
        this.status = '';
        this.q = '';
        this.sorting = 'id';
        this.direction = 'desc';
        this.page = 1;
        this.limit = 25;
    }

    $scope.searchForm = new searchForm();

    $scope.refreshData = function () {
        $http.get('/suppliers/listing?q=' + $scope.searchForm.q +
            '&status=' + $scope.searchForm.status +
            '&sorting=' + $scope.searchForm.sorting +
            '&direction=' + $scope.searchForm.direction +
            '&page=' + $scope.searchForm.page +
            '&limit=' + $scope.searchForm.limit)
            .then(response => {
                console.log(response.data);
                $scope.suppliers = response.data.data;
                $scope.suppliersLoaded = true;

                $scope.totalItems = response.data.total_items;
                $scope.countAll = response.data.all;
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
}
