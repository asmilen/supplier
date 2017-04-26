angular
    .module('controllers.app', [])
    .controller('AppController', AppController);

AppController.$inject = ['$scope', '$http'];

/* @ngInject */
function AppController($scope, $http) {
    console.log('Booting App Controller');
}
