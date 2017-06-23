angular.module('directives.select2', [])
       .directive("select2", select2);

function select2($timeout, $parse) {
    return {
        restrict: 'AC',
        require: 'ngModel',
        link: function(scope, element, attrs) {
            $timeout(function() {
                element.select2({
                    placeholder: attrs.placeholder,
                    allowClear: true,
                    width:'100%',
                });
                element.select2Initialized = true;
            });

            var refreshSelect = function() {
                if (!element.select2Initialized) return;
                $timeout(function() {
                    element.trigger('change');
                });
            };

            var recreateSelect = function () {
                if (!element.select2Initialized) return;
                $timeout(function() {
                    element.select2('destroy');
                    element.select2();
                });
            };

            scope.$watch(attrs.ngModel, refreshSelect);

            if (attrs.ngOptions) {
                var list = attrs.ngOptions.match(/ in ([^ ]*)/)[1];
                // watch for option list change
                scope.$watch(list, recreateSelect);
            }

            if (attrs.ngDisabled) {
                scope.$watch(attrs.ngDisabled, refreshSelect);
            }
        }
    };
};