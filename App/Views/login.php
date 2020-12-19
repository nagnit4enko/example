<div class="fade-in-right-big smooth">
      <div class="container w-xxl w-auto-xs">
        <div class="m-b-lg">
          <div class="wrapper text-center">
            <strong>Авторизация</strong>
          </div>
          <form name="form" class="form-validation ng-pristine ng-valid-email ng-invalid ng-invalid-required">
            <div id="error-info" class="text-danger wrapper text-center ng-binding ng-hide"></div>
            <div class="list-group list-group-sm">
              <div class="list-group-item">
                <input placeholder="Email" name="email" class="form-control no-border " type="email">
              </div>
              <div class="list-group-item">
                 <input placeholder="Password" name="pass" class="form-control no-border " type="password">
              </div>
            </div>
            <button type="submit" class="btn btn-lg btn-primary btn-block">Авторизоваться</button>
          </form>
        </div>
  </div>
</div>
<script>
$('form').on('submit', function(e){
  e.preventDefault();
  var query = {};
  $.each($('[name]', $(this)), function(k, v){
    query[v.name] = v.value;
  });
  if (!query.email.length || !validateEmail(query.email)) return curInput($('[name="email"]').get(0));
  if (!query.pass.length || query.pass.length < 3) return curInput($('[name="pass"]').get(0));
  $.post('/login/auth/', query, function(res){
    if (res.error) {
      if (res.errName !== undefined) {
        return curInput($('[name="' + res.errName + '"]').get(0));
      }
      return $('#error-info').show().html(res.error);
    }
    location.href = '/';
  });
});
</script>