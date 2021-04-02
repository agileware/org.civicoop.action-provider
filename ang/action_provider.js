(function(angular, $, _) {
  // Declare a list of dependencies.
  angular.module('action_provider', [
    'crmUi', 'crmUtil', 'ngRoute'
  ]);
  angular.module('action_provider').factory('actionProviderFactory', ["crmApi", "$q", function(crmApi, $q) {
    var actionTypes = {};
    var conditionTypes = {};

    // Initialize the context.
    var setContext = function (context) {
      if (!(context in actionTypes)) {
        actionTypes[context] = {};
      }
      if (!(context in conditionTypes)) {
        conditionTypes[context] = {};
      }
    }

    var retrieveAction = function (name, context) {
      setContext(context);
      if (!(name in actionTypes[context])) {
        var defer = $q.defer();
        crmApi('ActionProvider', 'getaction', {
          name: name,
          context: context
        }).then(function (data) {
          actionTypes[context][name] = data;
          defer.resolve(actionTypes[context][name]);
        });
        return defer.promise;
      }
      return $q.resolve(actionTypes[context][name]);
    };

    var retrieveCondition = function (name, context) {
      setContext(context);
      if (!(name in conditionTypes[context])) {
        var defer = $q.defer();
        crmApi('ActionProvider', 'getcondition', {
          name: name,
          context: context
        }).then(function (data) {
          conditionTypes[context][name] = data;
          defer.resolve(conditionTypes[context][name]);
        });
        return defer.promise;
      }
      return $q.resolve(conditionTypes[context][name]);
    };

    return {
      getAction: function (name, context) {
        return retrieveAction(name, context);
      },

      setAction: function(name, context, action) {
        setContext(context);
        actionTypes[context][name] = action;
      },

      getCondition: function (name, context) {
        return retrieveCondition(name, context);
      }
    };
  }]);
})(angular, CRM.$, CRM._);
