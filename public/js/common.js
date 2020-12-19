function validateEmail(email){
    var re = /\S+@\S+\.\S+/;
    return re.test(email);
}
function curInput(el, color){
    $(el).css('background-color', color || '#ffe9e9').focus();
    setTimeout(function(){
      $(el).css('background-color', '');
    }, 1000);
}
