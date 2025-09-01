<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification | </title>
    <link rel="stylesheet" type="text/css" href="{{ url('public/css/style.css') }}" >
        <link rel="stylesheet" href=" https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" >
   
</head>
<body>

  <div class="container">
    <div class="wrapper">
        <div class="title"> <span>Welcome to Nigerian Copyright</span></div>
        <form action="">
             <div class="row">
                <i class="fas fa-user"> </i>
                <input type="text" name="" placeholder="Username" required>

             </div>
             <div class="row">
                <i class="fas fa-user"> </i>
                <input type="email" name="" placeholder="Email" required>

             </div>
              <div class="row">
                <i class="fas fa-user"> </i>
                <input type="password" name="" placeholder="Password" required>

             </div>
               <div class="row">
                
                  <select class="selectbox" required>
                      <option value="">::: Select Role :::</option>
                      <option value="2">::: Super Admin :::</option>
                      <option value="1">::: Admin :::</option>
                      <option value="0">::: User :::</option>
                  </select>
               </div>
             <div class="pass">Forgot password</div>
             <div class="row button"><input type="submit" value="Registration"></div>
             <div class="signup-link">Sign In? <a href="">Login</a></div>
             <div class="signup-link">Welcome page? <a href=" href="{{ url('/') }}"">Welcome Page</a></div>
             
           
        </form>
            
    </div>
  </div>
</body>
</html>