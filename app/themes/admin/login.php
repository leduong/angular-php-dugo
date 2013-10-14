<!-- BEGIN LOGIN FORM -->
<form class="form-vertical login-form" method="post" action="/admin/login">
  <h3 class="form-title">Login to your account</h3>
  <div class="alert alert-error hide">
    <button class="close" data-dismiss="alert"></button>
    <span>Enter any username and password.</span>
  </div>
  <div class="control-group">
    <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
    <label class="control-label visible-ie8 visible-ie9">Username</label>
    <div class="controls">
      <div class="input-icon left">
        <i class="icon-user"></i>
        <input class="m-wrap placeholder-no-fix" type="text" placeholder="Username" name="username"/>
      </div>
    </div>
  </div>
  <div class="control-group">
    <label class="control-label visible-ie8 visible-ie9">Password</label>
    <div class="controls">
      <div class="input-icon left">
        <i class="icon-lock"></i>
        <input class="m-wrap placeholder-no-fix" type="password" placeholder="Password" name="password"/>
      </div>
    </div>
  </div>
  <div class="form-actions">
    <label class="checkbox">
      <input type="checkbox" name="remember" value="1"/> Remember me
    </label>
    <button type="submit" class="btn green pull-right">
    Login <i class="m-icon-swapright m-icon-white"></i>
    </button>
  </div>
</form>
<!-- END LOGIN FORM -->