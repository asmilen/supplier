angular
    .module('controllers.categoryEdit', [])
    .controller('CategoryEditController', CategoryEditController);

CategoryEditController.$inject = ['$scope', '$http'];

/* @ngInject */
function CategoryEditController($scope, $http) {
    $scope.categoryLoaded = false;

    $scope.getCategory = function () {
        //
    }
}
