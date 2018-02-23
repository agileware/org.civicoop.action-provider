(function(angular, $, _) {
  // "crmApActionConfiguration" is a basic skeletal directive.
  // Example usage: <crm-ap-action-configuration configuration="action.configuration" action="action.type"></crm-ap-action-configuration>
  angular.module('action_provider').directive('crmApActionConfiguration', function() {
    return {
      restrict: 'E',
      templateUrl: '~/action_provider/crmApActionConfiguration.html',
      scope: {
      	action: '=action',
        configuration: '=configuration',
        fields: '=?fields',
        mapping: '=?mapping',
      },
      link: function($scope, $el, $attr) {
      	$scope.ts = CRM.ts(null);
      }
    };
  });
})(angular, CRM.$, CRM._);
