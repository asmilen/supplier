angular
    .module('controllers.supplierEdit', [])
    .controller('SupplierEditController', SupplierEditController);

SupplierEditController.$inject = ['$scope', '$http', '$window'];

/* @ngInject */
function SupplierEditController($scope, $http, $window) {
    function editSupplierForm() {
        this.name = '';
        this.full_name = '';
        this.code = '';
        this.phone = '';
        this.fax = '';
        this.email = '';
        this.website = '';
        this.tax_number = '';
        this.type = '0';
        this.sup_type = '1';
        this.price_active_time = 0;
        this.status = true;
        this.errors = [];
        this.disabled = false;
    }

    $scope.editSupplierForm = new editSupplierForm();

    $scope.getSupplier = function () {
        $http.get('/suppliers/' + SUPPLIER_ID)
            .then(response => {
                $scope.supplier = response.data;

                $scope.editSupplierForm.name = response.data.name;
                $scope.editSupplierForm.full_name = response.data.full_name;
                $scope.editSupplierForm.code = response.data.code;
                $scope.editSupplierForm.phone = response.data.phone;
                $scope.editSupplierForm.fax = response.data.fax;
                $scope.editSupplierForm.email = response.data.email;
                $scope.editSupplierForm.website = response.data.website;
                $scope.editSupplierForm.tax_number = response.data.tax_number;
                $scope.editSupplierForm.type = response.data.type.toString();
                $scope.editSupplierForm.sup_type = response.data.sup_type.toString();
                $scope.editSupplierForm.price_active_time = response.data.price_active_time / 24;
                $scope.editSupplierForm.status = response.data.status;
            });
    }

    $scope.getSupplier();

    $scope.update = function () {
        $scope.editSupplierForm.errors = [];
        $scope.editSupplierForm.disabled = true;

        $http.put('/suppliers/' + SUPPLIER_ID, $scope.editSupplierForm)
            .then(response => {
                $scope.editSupplierForm.disabled = false;

                $window.location.href = '/suppliers';
            })
            .catch(response => {
                if (typeof response.data === 'object') {
                    $scope.editSupplierForm.errors = _.flatten(_.toArray(response.data));
                } else {
                    $scope.editSupplierForm.errors = ['Something went wrong. Please try again.'];
                }
                $scope.editSupplierForm.disabled = false;
            });
    }
}
