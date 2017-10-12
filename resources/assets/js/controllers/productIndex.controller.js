angular
    .module('controllers.productIndex', [])
    .controller('ProductIndexController', ProductIndexController);

ProductIndexController.$inject = ['$scope', '$http'];

/* @ngInject */
function ProductIndexController($scope, $http) {
    $scope.productsLoaded = false;

    $scope.totalItems = 0;

    $scope.countAll = 0;

    function searchForm() {
        this.category_id = '';
        this.manufacturer_id = '';
        this.status = '';
        this.q = '';
        this.sorting = 'id';
        this.direction = 'desc';
        this.page = 1;
        this.limit = 25;
    }

    $scope.searchForm = new searchForm();

    $scope.refreshData = function () {
        $http.get('/products/listing?q=' + $scope.searchForm.q +
            '&category_id=' + $scope.searchForm.category_id +
            '&manufacturer_id=' + $scope.searchForm.manufacturer_id +
            '&status=' + $scope.searchForm.status +
            '&sorting=' + $scope.searchForm.sorting +
            '&direction=' + $scope.searchForm.direction +
            '&page=' + $scope.searchForm.page +
            '&limit=' + $scope.searchForm.limit)
            .then(response => {
                $scope.products = response.data.data;
                $scope.totalItems = response.data.total_items;
                $scope.countAll = response.data.all;
                $scope.productsLoaded = true;
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

    $scope.channelText = function (channel) {
        return channel.replace('1', 'Online').replace('2', 'Offline');
    }
}
