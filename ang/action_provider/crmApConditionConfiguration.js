(function(angular, $, _) {
  // "crmApConditionConfiguration" is a basic skeletal directive.
  // Example usage: <crm-ap-condition-configuration configuration="condition.configuration" condition="condition.type"></crm-ap-condition-configuration>
  angular.module('action_provider').directive('crmApConditionConfiguration', ["crmApi", function(crmApi) {
    var conditions = {};
    return {
      restrict: 'E',
      templateUrl: '~/action_provider/crmApConditionConfiguration.html',
      scope: {
        action: '=',
        context: '@',
        configuration: '=',
        fields: '=?',
        mapping: '=?',
      },
      link: function($scope, $el, $attr) {
        $scope.ts = CRM.ts(null);
        $scope.condition = {};

        if (!($scope.context in conditions)) {
          conditions[$scope.context] = {};
        }

        $scope.$watch('action.condition_configuration', function(newCondition, oldCondition) {
          if (!newCondition) {
            $scope.condition = null;
            return;
          }

          if ($scope.action.condition_configuration.name in conditions[$scope.context]) {
            $scope.condition = conditions[$scope.context][$scope.action.condition_configuration.name];
            return;
          }

          crmApi('ActionProvider', 'getcondition', {name: $scope.action.condition_configuration.name, context: $scope.context}).
          then(function (data) {
            conditions[$scope.context][$scope.action.condition_configuration.name] = data;
            $scope.condition = data;
          });
        });
      }
    };
  }]);
})(angular, CRM.$, CRM._);
