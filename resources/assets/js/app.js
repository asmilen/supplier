var app = angular.module('app', [
    'controllers.app',
    'controllers.productCreate',
    'controllers.productEdit',
    'controllers.productSaleprice',
    'controllers.transportFeeIndex',
]);

app.config(['$httpProvider', function ($httpProvider) {
    $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
}]);

require('./controllers/app.controller.js');
require('./controllers/productCreate.controller.js');
require('./controllers/productEdit.controller.js');
require('./controllers/productSaleprice.controller.js');
require('./controllers/transportFeeIndex.controller.js');
