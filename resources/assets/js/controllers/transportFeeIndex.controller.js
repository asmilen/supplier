angular
    .module('controllers.transportFeeIndex', [])
    .controller('TransportFeeIndexController', TransportFeeIndexController);

TransportFeeIndexController.$inject = ['$scope', '$http'];

/* @ngInject */
function TransportFeeIndexController($scope, $http) {
    console.log('Transport Fee Index Controller');

    $scope.transportFeesLoaded = false;

    $scope.refreshData = function () {
        $http.get('/api/transport-fees')
            .then(function (response) {
                $scope.transportFees = response.data;
                $scope.transportFeesLoaded = true;
            });
    };

    $scope.refreshData();
}
