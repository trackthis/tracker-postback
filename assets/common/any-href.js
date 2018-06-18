(function() {
  var fw=require('../lib/fw');
  fw(function() {
    fw('[href],[data-href]')
      .filter(function(element) {
        return ['A','LINK'].indexOf(element.tagName) < 0;
      })
      .style('cursor', 'pointer')
      .on('click',function(ev) {
        var target = this.getAttribute('href') || this.dataset.href;
        if(!target) return;
        document.location.href = target;
      });
  });

})();
