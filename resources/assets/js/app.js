var app = angular.module('app', [
    'controllers.app',
    'controllers.productCreate',
    'controllers.productEdit',
    'controllers.productSaleprice',
    'controllers.transportFeeIndex',
    'controllers.categoryIndex',
]);

app.config(['$httpProvider', function ($httpProvider) {
    $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
}]);

require('./controllers/app.controller.js');
require('./controllers/productCreate.controller.js');
require('./controllers/productEdit.controller.js');
require('./controllers/productSaleprice.controller.js');
require('./controllers/transportFeeIndex.controller.js');
require('./controllers/categoryIndex.controller.js');

require('./directives/fileread.directive.js');
require('./directives/select2.directive.js');
