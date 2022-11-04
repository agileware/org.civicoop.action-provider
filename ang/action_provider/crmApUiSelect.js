(function(angular, $, _) {

// Display a fancy SELECT (based on select2).
// usage: <select crm-form-processor-ui-select="{placeholder:'Something',allowClear:true,...}" ng-model="myobj.field"><option...></select>
angular.module('action_provider').directive('crmApUiSelect', function () {
  return {
    require: '?ngModel',
    priority: 1,
    scope: {
      crmApUiSelect: '='
    },
    link: function (scope, element, attrs, ngModel) {
      scope.ts = CRM.ts(null);
      if (scope.crmApUiSelect.placeholder) {
        scope.crmApUiSelect.placeholder = scope.ts(scope.crmApUiSelect.placeholder);
      }
      element.select2(scope.crmApUiSelect);
      scope.$watchCollection('crmApUiSelect.data.results', function(newData, oldData) {
        element.trigger('change.select2');
      });
    }
  };
});
})(angular, CRM.$, CRM._);