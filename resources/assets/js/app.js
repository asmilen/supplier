require('angular-ui-bootstrap');
require('angular-base64-upload');

var app = angular.module('app', [
    'ui.bootstrap',
    'controllers.app',
    'controllers.categoryIndex',
    'controllers.categoryEdit',
    'controllers.attributeIndex',
    'controllers.productIndex',
    'controllers.productEdit',
    'controllers.productCreate',
    'controllers.supplierIndex',
    'controllers.supplierCreate',
    'controllers.supplierEdit',
    'controllers.categoryProductCreate',
    'controllers.productSupplier',
    'controllers.productSaleprice',
    'controllers.transportFeeIndex',
    'controllers.productSupplierIndex',
    'directives.format',
    'directives.currencyInput',
    'directives.select2',
]);

app.config(['$httpProvider', function ($httpProvider) {
    $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
}]);

require('./controllers/app.controller.js');
require('./controllers/categoryIndex.controller.js');
require('./controllers/categoryEdit.controller.js');
require('./controllers/attributeIndex.controller.js');
require('./controllers/productIndex.controller.js');
require('./controllers/productEdit.controller.js');
require('./controllers/productCreate.controller.js');
require('./controllers/categoryProductCreate.controller.js');
require('./controllers/supplierIndex.controller.js');
require('./controllers/supplierCreate.controller.js');
require('./controllers/supplierEdit.controller.js');
require('./controllers/productSupplier.controller.js');
require('./controllers/productSaleprice.controller.js');
require('./controllers/transportFeeIndex.controller.js');
require('./controllers/productSupplierIndex.controller.js');

require('./directives/fileread.directive.js');
require('./directives/select2.directive.js');
require('./directives/format.directive.js');
require('./directives/currencyInput.directive.js');
