window.core = {};

core.escape = function (str) {
    return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
}

core.unique = function (list) {
  var result = [];
  jQuery.each(list, function(i, e) {
    if (jQuery.inArray(e, result) == -1) result.push(e);
  });
  return result;
}
