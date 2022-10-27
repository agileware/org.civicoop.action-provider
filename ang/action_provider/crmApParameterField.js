(function(angular, $, _) {
  // "crmApActionConfiguration" is a basic skeletal directive.
  // Example usage: <crm-ap-action-configuration configuration="action.configuration" action="action.type"></crm-ap-action-configuration>
  angular.module('action_provider').directive('crmApParameterField', function() {
    return {
      templateUrl: '~/action_provider/crmApParameterField.html',
      restrict: 'E',
      scope: {
        'spec': '=',
        'fields': '='
      },
    };
  });
})(angular, CRM.$, CRM._);
