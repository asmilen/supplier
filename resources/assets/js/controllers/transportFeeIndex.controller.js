angular
    .module('controllers.transportFeeIndex', [])
    .controller('TransportFeeIndexController', TransportFeeIndexController);

TransportFeeIndexController.$inject = ['$scope', '$http'];

/* @ngInject */
function TransportFeeIndexController($scope, $http) {
    $scope.transportFeesLoaded = false;

    function transportFeeForm() {
        this.percentFees = {};
        this.errors = [];
        this.disabled = false;
        this.successful = false;
    }

    $scope.transportFeeForm = new transportFeeForm();

    $scope.refreshData = function () {
        $http.get('/api/transport-fees')
            .then(function (response) {
                $scope.transportFees = response.data;
                $scope.transportFeesLoaded = true;

                _.each($scope.transportFees, function (transportFee) {
                    $scope.transportFeeForm.percentFees[transportFee.province_id] = transportFee.percent_fee;
                });
            });
    };

    $scope.refreshData();

    $scope.updatePercentFee = function (provinceId) {
        $scope.transportFeeForm.errors = [];
        $scope.transportFeeForm.disabled = true;
        $scope.transportFeeForm.successful = false;

        $http.put('/api/provinces/' + provinceId + '/transport-fee', {percent_fee: $scope.transportFeeForm.percentFees[provinceId]})
            .then(function () {
                $scope.transportFeeForm.successful = true;
                $scope.transportFeeForm.disabled = false;
            })
            .catch(function (response) {
                if (typeof response.data === 'object') {
                    $scope.transportFeeForm.errors = _.flatten(_.toArray(response.data));
                } else {
                    $scope.transportFeeForm.errors = ['Something went wrong. Please try again.'];
                }
                $scope.transportFeeForm.disabled = false;
            });
    };
}
