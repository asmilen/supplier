var app = angular.module('app', [
    'controllers.app',
    'controllers.productCreate',
    'controllers.productEdit',
]);

app.config(['$httpProvider', function ($httpProvider) {
    $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
}]);

require('./controllers/app.controller.js');
require('./controllers/productCreate.controller.js');
require('./controllers/productEdit.controller.js');
