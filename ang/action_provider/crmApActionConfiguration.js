(function(angular, $, _) {
  // "crmApActionConfiguration" is a basic skeletal directive.
  // Example usage: <crm-ap-action-configuration configuration="action.configuration" action="action.type"></crm-ap-action-configuration>
  angular.module('action_provider').directive('crmApActionConfiguration', ["actionProviderFactory", function(actionProviderFactory) {
    var actions = {};
    return {
      restrict: 'E',
      templateUrl: '~/action_provider/crmApActionConfiguration.html',
      scope: {
        name: '=',
        context: '@',
        metadata: '=',
        configuration: '=',
        fields: '=?',
        mapping: '=?',
      },
      link: function($scope, $el, $attr) {
      	$scope.ts = CRM.ts(null);
      	$scope.action = {};
      	$scope.uiFields = {};

        function loadAction(name, context, metadata) {
          if (!(context in actions)) {
            actions[context] = {};
          }

          if (name in actions[context]) {
            $scope.action = actions[context][name];
            return;
          }

          actionProviderFactory.getAction(name, context, metadata)
          .then(function (data) {
            actions[context][name] = data;
            $scope.action = data;

            for (var spec in data.configuration_spec) {
              if (data.configuration_spec[spec].options) {
                data.configuration_spec[spec].select2Options = [];
                for (var optionValue in data.configuration_spec[spec].options) {
                  var select2Option = {
                    'id': optionValue,
                    'text': data.configuration_spec[spec].options[optionValue],
                  };
                  data.configuration_spec[spec].select2Options.push(select2Option);
                }
              }
            }
          });
        }

        loadAction($scope.name, $scope.context, $scope.metadata);

        // Re-load when the caller switches which action is being configured
        // (e.g. a picker bound to `name`), not just on initial link.
        $scope.$watch('name', function(newName, oldName) {
          if (newName !== oldName) {
            $scope.action = {};
            loadAction(newName, $scope.context, $scope.metadata);
          }
        });
      }
    };
  }]);
})(angular, CRM.$, CRM._);
