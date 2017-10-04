angular
    .module('controllers.app', [])
    .controller('AppController', AppController);

AppController.$inject = ['$scope', '$http'];

/* @ngInject */
function AppController($scope, $http) {
    console.log('Booting App Controller');

    $scope.getSortingDirectionClassHeader = function (current, sorting, direction) {
        if (current != sorting) {
            return '';
        }

        return '_' + direction;
    }
}
