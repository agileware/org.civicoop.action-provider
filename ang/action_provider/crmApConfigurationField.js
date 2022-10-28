(function(angular, $, _) {
  // "crmApActionConfiguration" is a basic skeletal directive.
  // Example usage: <crm-ap-action-configuration configuration="action.configuration" action="action.type"></crm-ap-action-configuration>
  angular.module('action_provider').directive('crmApConfigurationField', function() {
    return {
      templateUrl: '~/action_provider/crmApConfigurationField.html',
      restrict: 'E',
      scope: {
        'spec': '=',
        'configuration': '='
      },
    };
  });
})(angular, CRM.$, CRM._);
