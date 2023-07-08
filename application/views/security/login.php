<!DOCTYPE html>
<!--[if lt IE 7]> <html class="lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]> <html class="lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]> <html class="lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html lang="en"> <!--<![endif]-->
 

<form id='frm-login' method='POST'>
  <section class="container">
    <div class="login">
      <h1>Login to <?=PROJECT_NAME?></h1>
      <form method="post" action="index.html">
        <p><input type='text' name='user_name' class='required'  placeholder='username'></p>
        <p><input type='password' name='user_password' class='required' placeholder='password'></p>
		
		<p class="remember_me">
		  <label>
			<?=$message?>
		  </label>
		</p> 
        <p class="submit"><input type='submit' value = ' login ' ></p>
      </form>
    </div> 
  </section>
</form>   
 