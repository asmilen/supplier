angular
    .module('controllers.supplierCreate', [])
    .controller('SupplierCreateController', SupplierCreateController);

SupplierCreateController.$inject = ['$scope', '$http', '$window'];

/* @ngInject */
function SupplierCreateController($scope, $http, $window) {
    function addSupplierForm() {
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

        // Address
        this.province_id = '0';
        this.district_id = '0';
        this.address = '';
        this.addressCode = '';
        this.contact_name = '';
        this.contact_mobile = '';
        this.contact_phone = '';
        this.contact_email = '';

        // Bank Account
        this.bank_account = '';
        this.bank_account_name = '';
        this.bank_name = '';
        this.bank_code = '';
        this.bank_branch = '';
        this.bank_province = '';

        this.status = true;
        this.errors = [];
        this.disabled = false;
    }

    $scope.addSupplierForm = new addSupplierForm();

    $scope.getProvinces = function () {
        $http.get('/provinces')
            .then(response => {
                $scope.provinces = response.data;
            });
    }

    $scope.getProvinces();

    $scope.getDistricts = function () {
        $http.get('/provinces/' + $scope.addSupplierForm.province_id + '/districts')
            .then(response => {
                $scope.districts = response.data;
            });
    }

    $scope.getAddressCode = function () {
        $http.get('/provinces/' + $scope.addSupplierForm.province_id + '/address-code')
            .then(response => {
                $scope.addSupplierForm.addressCode = response.data;
            });
    }

    $scope.changeProvince = function () {
        $scope.getDistricts();
        $scope.getAddressCode();
    }

    $scope.store = function () {
        $scope.addSupplierForm.errors = [];
        $scope.addSupplierForm.disabled = true;

        $http.post('/suppliers', $scope.addSupplierForm)
            .then(response => {
                $scope.addSupplierForm.disabled = false;

                $window.location.href = '/suppliers';
            })
            .catch(response => {
                if (typeof response.data === 'object') {
                    $scope.addSupplierForm.errors = _.flatten(_.toArray(response.data));
                } else {
                    $scope.addSupplierForm.errors = ['Something went wrong. Please try again.'];
                }
                $scope.addSupplierForm.disabled = false;
            });
    }
}
