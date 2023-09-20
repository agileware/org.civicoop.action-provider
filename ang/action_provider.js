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

    var retrieveAction = function (name, context, metadata={}) {
      var fullContext = context + JSON.stringify(metadata);
      setContext(fullContext);
      if (!(name in actionTypes[fullContext])) {
        var defer = $q.defer();
        crmApi('ActionProvider', 'getaction', {
          name: name,
          context: context,
          metadata: metadata
        }).then(function (data) {
          actionTypes[fullContext][name] = data;
          defer.resolve(actionTypes[fullContext][name]);
        });
        return defer.promise;
      }
      return $q.resolve(actionTypes[fullContext][name]);
    };

    var retrieveCondition = function (name, context, metadata = {}) {
      var fullContext = context + JSON.stringify(metadata);
      setContext(fullContext);
      if (!(name in conditionTypes[fullContext])) {
        var defer = $q.defer();
        crmApi('ActionProvider', 'getcondition', {
          name: name,
          context: context,
          metadata: metadata
        }).then(function (data) {
          conditionTypes[fullContext][name] = data;
          defer.resolve(conditionTypes[fullContext][name]);
        });
        return defer.promise;
      }
      return $q.resolve(conditionTypes[fullContext][name]);
    };

    return {
      getAction: function (name, context, metadata = {}) {
        return retrieveAction(name, context, metadata);
      },

      setAction: function(name, context, action, metadata = {}) {
        var fullContext = context + JSON.stringify(metadata);
        setContext(fullContext);
        actionTypes[fullContext][name] = action;
      },

      getCondition: function (name, context, metadata = {}) {
        return retrieveCondition(name, context, metadata);
      }
    };
  }]);
})(angular, CRM.$, CRM._);
