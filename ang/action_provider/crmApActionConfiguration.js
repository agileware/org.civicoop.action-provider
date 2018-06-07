(function(angular, $, _) {
  // "crmApActionConfiguration" is a basic skeletal directive.
  // Example usage: <crm-ap-action-configuration configuration="action.configuration" action="action.type"></crm-ap-action-configuration>
  angular.module('action_provider').directive('crmApActionConfiguration', ["crmApi", function(crmApi) {
    var actions = {};
    return {
      restrict: 'E',
      templateUrl: '~/action_provider/crmApActionConfiguration.html',
      scope: {
        name: '=',
        context: '@',
        configuration: '=',
        fields: '=?',
        mapping: '=?',
      },
      link: function($scope, $el, $attr) {
      	$scope.ts = CRM.ts(null);
      	$scope.action = {};
      	
      	if (!($scope.context in actions)) {
      	  actions[$scope.context] = {};
      	}
      	
      	if ($scope.name in actions[$scope.context]) {
      	  $scope.action = actions[$scope.context][$scope.name];
      	  return;
      	}
      	
      	crmApi('ActionProvider', 'getaction', {name: $scope.name, context: $scope.context}).
      	then(function (data) {
      	  actions[$scope.context][$scope.name] = data;
      	  $scope.action = data;
      	});
      }
    };
  }]);
})(angular, CRM.$, CRM._);
