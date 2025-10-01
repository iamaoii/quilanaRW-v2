<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign in & Sign up Form</title>
    <link rel="stylesheet" href="assets/css/login_signup.css">
</head>
<body>
    <main>
        <div class="box">
            <div class="inner-box">
                <div class="forms-wrap">
                    <!-- Sign In Form -->
                    <form id="signin-form" autocomplete="off" class="sign-in-form">
                        <div class="logo">
                            <img src="image/Lana.png" alt="quilana" />
                            <h4>Quilana</h4>
                        </div>

                        <div class="heading">
                            <h2>Welcome Back</h2>
                            <h6> </h6>
                        </div>

                        <div class="actual-form">
                            <!-- Sign As Selection -->
                            <div class="input-wrap">
                                <select id="user_type" class="input-field" name="user_type" required>
                                    <option value="" disabled selected>Sign in as</option>
                                    <option value="2">Faculty</option>
                                    <option value="3">Student</option>
                                </select>
                            </div>

                            <!-- Username Input -->
                            <div class="input-wrap">
                                <input type="text" id="username" name="username" class="input-field" autocomplete="off" required />
                                <label for="username">Username</label>
                            </div>

                            <!-- Password Input -->
                            <div class="input-wrap">
                                <input type="password" id="password" name="password" class="input-field" autocomplete="off" required />
                                <label for="password">Password</label>
                            </div>

                            <input type="submit" value="Sign In" class="sign-btn" />
                            
                        </div>
                    </form>
                </div>

                <!-- Carousel Section -->
                <div class="carousel">
                    <div class="images-wrapper">
                        <img src="image/FloatingLana.gif" class="image img-1 show" alt="Lana" />
                        <img src="image/User.png" class="image img-2" alt="" />
                        <img src="image/image3.png" class="image img-3" alt="" />
                    </div>

                    <div class="text-slider">
                        <div class="text-wrap">
                            <div class="text-group">
                                <h2>Create your own courses</h2>
                                <h2>Customize as you like</h2>
                                <h2>Invite students to your class</h2>
                            </div>
                        </div>

                        <div class="bullets">
                            <span class="active" data-value="1"></span>
                            <span data-value="2"></span>
                            <span data-value="3"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/sign_signup.js"></script>
</body>
</html>