angular
    .module('controllers.attributeIndex', [])
    .controller('AttributeIndexController', AttributeIndexController);

AttributeIndexController.$inject = ['$scope', '$http'];

/* @ngInject */
function AttributeIndexController($scope, $http) {
    $scope.attributesLoaded = false;

    $scope.totalItems = 0;

    $scope.editingAttribute = null;

    $scope.frontendInputs = {
        'text': 'Text',
        'textarea': 'Textarea',
        'select': 'Dropdown',
        'multiselect': 'Multiple Select',
    };

    $scope.backendTypes = {
        'varchar': 'varchar',
        'int': 'int',
        'decimal': 'decimal',
        // 'text': 'text'
    };

    function searchForm() {
        this.q = '';
        this.sorting = 'slug';
        this.direction = 'asc';
        this.page = 1;
        this.limit = 25;
    }

    function addAttributeForm() {
        this.slug = '';
        this.name = '';
        this.frontend_input = 'text';
        this.backend_type = 'varchar';
        this.errors = [];
        this.successful = false;
        this.disabled = false;
    }

    function editAttributeForm() {
        this.name = '';
        this.errors = [];
        this.disabled = false;
    }

    $scope.searchForm = new searchForm();
    $scope.addAttributeForm = new addAttributeForm();

    $scope.refreshData = function () {
        $http.get('/attributes/listing?q=' + $scope.searchForm.q +
            '&sorting=' + $scope.searchForm.sorting +
            '&direction=' + $scope.searchForm.direction +
            '&page=' + $scope.searchForm.page +
            '&limit=' + $scope.searchForm.limit)
            .then(response => {
                $scope.attributes = response.data.data;
                $scope.totalItems = response.data.total_items;
                $scope.attributesLoaded = true;
            });
    }

    $scope.refreshData();

    $scope.updateSorting = function (sorting) {
        if ($scope.searchForm.sorting == sorting) {
            if ($scope.searchForm.direction == 'asc') {
                $scope.searchForm.direction = 'desc';
            } else {
                $scope.searchForm.direction = 'asc';
            }
        } else {
            $scope.searchForm.direction = 'asc';
        }

        $scope.searchForm.sorting = sorting;

        $scope.refreshData();
    }

    $scope.mapBackendType = function () {
        if ($scope.addAttributeForm.frontend_input == 'textarea') {
            $scope.addAttributeForm.backend_type = 'text';
        } else if ($scope.addAttributeForm.frontend_input == 'select' || $scope.addAttributeForm.frontend_input == 'multiselect') {
            $scope.addAttributeForm.backend_type = 'int';
        } else {
            $scope.addAttributeForm.backend_type = 'varchar';
        }
    }

    $scope.store = function () {
        $scope.addAttributeForm.errors = [];
        $scope.addAttributeForm.successful = false;
        $scope.addAttributeForm.disabled = true;

        $http.post('/attributes', $scope.addAttributeForm)
            .then(response => {
                $scope.addAttributeForm = new addAttributeForm();
                $scope.addAttributeForm.successful = true;

                $scope.refreshData();

                if (response.data.frontend_input == 'select' || response.data.frontend_input == 'multiselect') {
                    $scope.showEditOptionsModal(response.data);
                }
            })
            .catch(response => {
                if (typeof response.data === 'object') {
                    $scope.addAttributeForm.errors = _.flatten(_.toArray(response.data));
                } else {
                    $scope.addAttributeForm.errors = ['Something went wrong. Please try again.'];
                }
                $scope.addAttributeForm.disabled = false;
            });
    }

    $scope.showEditForm = function (attribute) {
        $scope.editingAttribute = attribute;

        $scope.editAttributeForm = new editAttributeForm();
        $scope.editAttributeForm.name = attribute.name;
    }

    $scope.cancelEditing = function () {
        $scope.editingAttribute = null;
    }

    $scope.update = function () {
        $scope.editAttributeForm.errors = [];
        $scope.editAttributeForm.disabled = true;

        $http.put('/attributes/' + $scope.editingAttribute.id, $scope.editAttributeForm)
            .then(response => {
                $scope.editAttributeForm = new editAttributeForm();
                $scope.editingAttribute = null;

                $scope.refreshData();
            })
            .catch(response => {
                if (typeof response.data === 'object') {
                    $scope.editAttributeForm.errors = _.flatten(_.toArray(response.data));
                } else {
                    $scope.editAttributeForm.errors = ['Something went wrong. Please try again.'];
                }
                $scope.editAttributeForm.disabled = false;
            });
    }

    $scope.showEditOptionsModal = function (attribute) {
        console.log(attribute);
    }
}
