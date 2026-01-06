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
                            <h6>Not registered yet?</h6>
                            <a href="#" class="toggle">Sign up</a>
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
                                <input type="text" name="username" class="input-field" autocomplete="off" required />
                                <label for="username">Username</label>
                            </div>

                            <!-- Password Input -->
                            <div class="input-wrap">
                                <input type="password" name="password" class="input-field" autocomplete="off" required />
                                <label>Password</label>
                            </div>

                            <input type="submit" value="Sign In" class="sign-btn" />
                            <p class="text">
                                Forgotten your password or login details?
<<<<<<< Updated upstream
                                <a href="#">Get help</a> signing in
=======
                                <a href="#" id="forgot-password-link">Forgot Password</a>
>>>>>>> Stashed changes
                            </p>
                        </div>
                    </form>

                    <!-- Sign Up Form -->
                    <form id="signup-form" autocomplete="off" class="sign-up-form">
                        <div class="logo">
                            <img src="image/Lana.png" alt="quilana" />
                            <h4>Quilana</h4>
                        </div>

                        <div class="heading">
                            <h2>Get Started</h2>
                            <h6>Already have an account?</h6>
                            <a href="#" class="toggle">Sign in</a>
                        </div>

                        <div class="actual-form">
                            <div class="input-wrap">
                                <select id="userType" name="user_type" class="input-field" onchange="toggleFormFields()" required>
                                    <option value="" disabled selected>Sign up as</option>
                                    <option value="3">Student</option>
                                    <option value="2">Faculty</option>
                                </select>
                            </div>

                            <!-- Student/Faculty Form Fields -->
                            <div id="registrationFields" style="display: none;">
                                <div class="input-wrap">
                                    <input type="text" id="firstname" name="first_name" class="input-field" required />
                                    <label>First Name</label>
                                </div>

                                <div class="input-wrap">
                                    <input type="text" id="lastname" name="last_name" class="input-field" required />
                                    <label>Last Name</label>
                                </div>

                                <div class="input-wrap">
                                    <input type="email" id="webmail" name="webmail" class="input-field" required />
                                    <label>Webmail</label>
                                </div>
                                <div class="validation-note webmail-note" id="facultywebmail-validation" style="display: none;">Faculty webmail must be xxxxxx@pup.edu.ph</div>
                                <div class="validation-note webmail-note" id="studentwebmail-validation" style="display: none;">Student webmail must be xxxxxxxxx@iskolarngbayan.pup.edu.ph</div>

                                <div class="input-wrap" id="faculty_number_container" style="display:none;">
                                    <input type="text" id="faculty_number" name="faculty_number" class="input-field" />
                                    <label>Faculty Number</label>
                                </div>
                                <div class="validation-note faculty-number-note" id="facultynumber-validation" style="display: none;">Faculty number must be xxxx-xxxxx-MN-0</div>
                                
                                <div class="input-wrap" id="student_number_container" style="display:none;">
                                    <input type="text" id="student_number" name="student_number" class="input-field" />
                                    <label>Student Number</label>
                                </div>
                                <div class="validation-note student-number-note" id="studentnumber-validation" style="display: none;">Student number must be xxxx-xxxxx-MN-0</div>

                                <div class="input-wrap">
                                    <input type="text" id="username" name="username" class="input-field" required />
                                    <label>Username</label>
                                </div>

                                <div class="input-wrap">
                                    <input type="password" id="password" name="password" class="input-field" required />
                                    <label>Password</label>
                                </div>
                                <div class="validation-note password-note" id="password-validation" style="display: none;">Password must be at least 8 characters long and include uppercase, lowercase letters, numbers, and special characters.</div>

                                <div class="input-wrap">
                                    <input type="password" id="confirm_password" name="confirm_password" class="input-field" required />
                                    <label for="password">Confirm Password</label>
                                </div>
                                <div class="validation-note confirm-password-note" id="confirmpassword-validation" style="display: none;">Passwords do not match!</div>
                            </div>

                            <button type="submit" id="signUpButton" value="Sign Up" class="sign-btn">Sign Up</button>

                            <p class="text">
                                By signing up, I agree to the
                                <a href="#">Terms of Services</a> and
                                <a href="#">Privacy Policy</a>
                            </p>
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

<<<<<<< Updated upstream
=======
    <!-- Forgot Password Modal -->
    <div id="forgot-password-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.6); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background-color: white; padding: 2.5rem; border-radius: 2rem; max-width: 480px; width: 90%; max-height: 90vh; overflow-y: auto; position: relative; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);">
            <button id="close-forgot-modal" style="position: absolute; top: 1.2rem; right: 1.2rem; background: none; border: none; font-size: 1.8rem; cursor: pointer; color: #bbb; transition: 0.3s; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 50%;" onmouseover="this.style.color='#151111'; this.style.backgroundColor='#f5f5f5';" onmouseout="this.style.color='#bbb'; this.style.backgroundColor='transparent';">&times;</button>
            
            <div class="logo" style="margin-bottom: 1.5rem;">
                <img src="image/Lana.png" alt="quilana" />
                <h4>Quilana</h4>
            </div>
            
            <div class="heading" style="margin-bottom: 2rem;">
                <h2 id="forgot-modal-title" style="font-size: 1.8rem; font-weight: 600; color: #151111; margin-bottom: 0.5rem;">Forgot Password</h2>
                <h6 id="forgot-modal-subtitle" style="color: #bababa; font-weight: 400; font-size: 0.75rem;">Enter your account details to request a password reset</h6>
            </div>
            
            <!-- Request Form -->
            <div id="request-form-section">
                <p class="text" style="margin-bottom: 1.5rem; text-align: center; color: #bbb; font-size: 0.75rem;">An administrator will process your request after submission.</p>
                <div style="margin-bottom: 1.5rem; text-align: center;">
                    <a href="#" id="switch-to-view-link" class="toggle" style="font-size: 0.75rem;">Already have an approved request? Set your new password here</a>
                </div>
                <form id="request-password-form">
                    <div class="input-wrap">
                        <select id="request_user_type" name="user_type" class="input-field" required>
                            <option value="" disabled selected></option>
                            <option value="2">Faculty</option>
                            <option value="3">Student</option>
                        </select>
                        <label>Sign in as</label>
                    </div>
                    
                    <div class="input-wrap">
                        <input type="text" id="request_username" name="username" class="input-field" autocomplete="off" required />
                        <label>Username</label>
                    </div>
                    
                    <div class="input-wrap">
                        <input type="email" id="request_webmail" name="webmail" class="input-field" autocomplete="off" required />
                        <label>Webmail</label>
                    </div>
                    
                    <div class="input-wrap" id="request_faculty_number_container" style="display: none;">
                        <input type="text" id="request_faculty_number" name="faculty_number" class="input-field" autocomplete="off" />
                        <label>Faculty Number</label>
                    </div>
                    
                    <div class="input-wrap" id="request_student_number_container" style="display: none;">
                        <input type="text" id="request_student_number" name="student_number" class="input-field" autocomplete="off" />
                        <label>Student Number</label>
                    </div>
                    
                    <div id="request-message" style="margin-bottom: 1rem; padding: 0.75rem; border-radius: 0.5rem; display: none; font-size: 0.75rem;"></div>
                    
                    <button type="submit" id="request-submit-btn" class="sign-btn">Request Password Change</button>
                </form>
            </div>
            
            <!-- Set Password Section (after admin approval) -->
            <div id="show-password-section" style="display: none;">
                <p class="text" style="margin-bottom: 1.5rem; text-align: center; color: #bbb; font-size: 0.75rem;">Your password reset request has been approved. Enter your credentials and set your new password.</p>
                <form id="show-password-form">
                    <div class="input-wrap">
                        <select id="show_user_type" name="user_type" class="input-field" required>
                            <option value="" disabled selected></option>
                            <option value="2">Faculty</option>
                            <option value="3">Student</option>
                        </select>
                        <label>Sign in as</label>
                    </div>
                    
                    <div class="input-wrap">
                        <input type="text" id="show_username" name="username" class="input-field" autocomplete="off" required />
                        <label>Username</label>
                    </div>
                    
                    <div class="input-wrap">
                        <input type="email" id="show_webmail" name="webmail" class="input-field" autocomplete="off" required />
                        <label>Webmail</label>
                    </div>
                    
                    <div class="input-wrap" id="show_faculty_number_container" style="display: none;">
                        <input type="text" id="show_faculty_number" name="faculty_number" class="input-field" autocomplete="off" />
                        <label>Faculty Number</label>
                    </div>
                    
                    <div class="input-wrap" id="show_student_number_container" style="display: none;">
                        <input type="text" id="show_student_number" name="student_number" class="input-field" autocomplete="off" />
                        <label>Student Number</label>
                    </div>
                    
                    <div class="input-wrap">
                        <input type="password" id="show_new_password" name="new_password" class="input-field" autocomplete="off" required />
                        <label>New Password</label>
                    </div>
                    
                    <div class="input-wrap">
                        <input type="password" id="show_confirm_password" name="confirm_password" class="input-field" autocomplete="off" required />
                        <label>Confirm New Password</label>
                    </div>
                    
                    <div id="show-password-message" style="margin-bottom: 1rem; padding: 0.75rem; border-radius: 0.5rem; display: none; font-size: 0.75rem;"></div>
                    
                    <button type="submit" id="show-password-submit-btn" class="sign-btn">Set New Password</button>
                </form>
            </div>
        </div>
    </div>

>>>>>>> Stashed changes
    <script src="assets/js/jquery-3.5.1.min.js"> </script>
    <script src="assets/js/sign_signup.js"></script>
    <script>
        // Forgot password modal functionality
        document.getElementById('forgot-password-link').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('forgot-password-modal').style.display = 'flex';
            // Reset to request form
            document.getElementById('request-form-section').style.display = 'block';
            document.getElementById('show-password-section').style.display = 'none';
            document.getElementById('forgot-modal-title').textContent = 'Forgot Password';
            document.getElementById('forgot-modal-subtitle').textContent = 'Enter your account details to request a password reset';
            document.getElementById('request-password-form').reset();
            document.getElementById('request-message').style.display = 'none';
        });

        document.getElementById('close-forgot-modal').addEventListener('click', function() {
            document.getElementById('forgot-password-modal').style.display = 'none';
        });

        document.getElementById('forgot-password-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });

        // Toggle faculty/student number fields for request form
        document.getElementById('request_user_type').addEventListener('change', function() {
            const userType = this.value;
            if (userType === '2') {
                document.getElementById('request_student_number_container').style.display = 'none';
                document.getElementById('request_faculty_number_container').style.display = 'block';
                document.getElementById('request_faculty_number').required = true;
                document.getElementById('request_student_number').required = false;
            } else if (userType === '3') {
                document.getElementById('request_faculty_number_container').style.display = 'none';
                document.getElementById('request_student_number_container').style.display = 'block';
                document.getElementById('request_student_number').required = true;
                document.getElementById('request_faculty_number').required = false;
            } else {
                document.getElementById('request_faculty_number_container').style.display = 'none';
                document.getElementById('request_student_number_container').style.display = 'none';
                document.getElementById('request_faculty_number').required = false;
                document.getElementById('request_student_number').required = false;
            }
            // Trigger active state for select
            if (userType !== '') {
                this.classList.add('active');
            }
        });

        // Toggle faculty/student number fields for show password form
        document.getElementById('show_user_type').addEventListener('change', function() {
            const userType = this.value;
            if (userType === '2') {
                document.getElementById('show_student_number_container').style.display = 'none';
                document.getElementById('show_faculty_number_container').style.display = 'block';
                document.getElementById('show_faculty_number').required = true;
                document.getElementById('show_student_number').required = false;
            } else if (userType === '3') {
                document.getElementById('show_faculty_number_container').style.display = 'none';
                document.getElementById('show_student_number_container').style.display = 'block';
                document.getElementById('show_student_number').required = true;
                document.getElementById('show_faculty_number').required = false;
            } else {
                document.getElementById('show_faculty_number_container').style.display = 'none';
                document.getElementById('show_student_number_container').style.display = 'none';
                document.getElementById('show_faculty_number').required = false;
                document.getElementById('show_student_number').required = false;
            }
            // Trigger active state for select
            if (userType !== '') {
                this.classList.add('active');
            }
        });

            // Request password reset form submission
        document.getElementById('request-password-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const userType = document.getElementById('request_user_type').value;
            const username = document.getElementById('request_username').value.trim();
            const webmail = document.getElementById('request_webmail').value.trim();
            const messageDiv = document.getElementById('request-message');
            const submitBtn = document.getElementById('request-submit-btn');
            
            let number = '';
            if (userType === '2') {
                number = document.getElementById('request_faculty_number').value.trim();
            } else if (userType === '3') {
                number = document.getElementById('request_student_number').value.trim();
            }
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting Request...';
            messageDiv.style.display = 'none';
            
            const formData = new FormData();
            formData.append('user_type', userType);
            formData.append('username', username);
            formData.append('webmail', webmail);
            if (userType === '2') {
                formData.append('faculty_number', number);
            } else if (userType === '3') {
                formData.append('student_number', number);
            }
            
            fetch('request_password_reset.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                messageDiv.style.display = 'block';
                if (data.success) {
                    messageDiv.style.backgroundColor = '#d4edda';
                    messageDiv.style.color = '#155724';
                    messageDiv.style.border = '1px solid #c3e6cb';
                    messageDiv.innerHTML = data.message || 'Password reset request submitted successfully! An administrator will process your request.<br><br><a href="#" id="switch-to-view" class="toggle" style="font-size: 0.7rem;">Click here to view your password after approval</a>';
                    
                    // Add click handler for switch link
                    const switchLink = document.getElementById('switch-to-view');
                    if (switchLink) {
                        switchLink.addEventListener('click', function(e) {
                            e.preventDefault();
                            switchToViewPasswordMode();
                        });
                    }
                    
                    document.getElementById('request-password-form').reset();
                } else {
                    messageDiv.style.backgroundColor = '#f8d7da';
                    messageDiv.style.color = '#721c24';
                    messageDiv.style.border = '1px solid #f5c6cb';
                    messageDiv.textContent = data.message || 'Failed to submit request. Please check your information.';
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Request Password Change';
                }
            })
            .catch(error => {
                messageDiv.style.display = 'block';
                messageDiv.style.backgroundColor = '#fee';
                messageDiv.style.color = '#c33';
                messageDiv.textContent = 'An error occurred. Please try again.';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Request Password Change';
            });
        });

        // Set password form submission (after admin approval)
        document.getElementById('show-password-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const userType = document.getElementById('show_user_type').value;
            const username = document.getElementById('show_username').value.trim();
            const webmail = document.getElementById('show_webmail').value.trim();
            const newPassword = document.getElementById('show_new_password').value;
            const confirmPassword = document.getElementById('show_confirm_password').value;
            const messageDiv = document.getElementById('show-password-message');
            const submitBtn = document.getElementById('show-password-submit-btn');
            
            // Validate password match
            if (newPassword !== confirmPassword) {
                messageDiv.style.display = 'block';
                messageDiv.style.backgroundColor = '#f8d7da';
                messageDiv.style.color = '#721c24';
                messageDiv.style.border = '1px solid #f5c6cb';
                messageDiv.textContent = 'Passwords do not match!';
                return;
            }
            
            // Validate password strength
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,}$/;
            if (!passwordRegex.test(newPassword)) {
                messageDiv.style.display = 'block';
                messageDiv.style.backgroundColor = '#f8d7da';
                messageDiv.style.color = '#721c24';
                messageDiv.style.border = '1px solid #f5c6cb';
                messageDiv.textContent = 'Password must be at least 8 characters with uppercase, lowercase, numbers, and special characters!';
                return;
            }
            
            let number = '';
            if (userType === '2') {
                number = document.getElementById('show_faculty_number').value.trim();
            } else if (userType === '3') {
                number = document.getElementById('show_student_number').value.trim();
            }
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Setting Password...';
            messageDiv.style.display = 'none';
            
            const formData = new FormData();
            formData.append('user_type', userType);
            formData.append('username', username);
            formData.append('webmail', webmail);
            formData.append('new_password', newPassword);
            if (userType === '2') {
                formData.append('faculty_number', number);
            } else if (userType === '3') {
                formData.append('student_number', number);
            }
            
            fetch('reset_password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                messageDiv.style.display = 'block';
                if (data.success) {
                    messageDiv.style.backgroundColor = '#d4edda';
                    messageDiv.style.color = '#155724';
                    messageDiv.style.border = '1px solid #c3e6cb';
                    messageDiv.textContent = data.message || 'Password set successfully! You can now sign in with your new password.';
                    document.getElementById('show-password-form').reset();
                    setTimeout(function() {
                        document.getElementById('forgot-password-modal').style.display = 'none';
                    }, 2000);
                } else {
                    messageDiv.style.backgroundColor = '#f8d7da';
                    messageDiv.style.color = '#721c24';
                    messageDiv.style.border = '1px solid #f5c6cb';
                    messageDiv.textContent = data.message || 'Failed to set password. Please check your information or wait for admin approval.';
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Set New Password';
                }
            })
            .catch(error => {
                messageDiv.style.display = 'block';
                messageDiv.style.backgroundColor = '#f8d7da';
                messageDiv.style.color = '#721c24';
                messageDiv.style.border = '1px solid #f5c6cb';
                messageDiv.textContent = 'An error occurred. Please try again.';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Set New Password';
            });
        });

        // Add button to switch to "Set Password" mode
        function switchToViewPasswordMode() {
            document.getElementById('request-form-section').style.display = 'none';
            document.getElementById('show-password-section').style.display = 'block';
            document.getElementById('forgot-modal-title').textContent = 'Set New Password';
            document.getElementById('forgot-modal-subtitle').textContent = 'Enter your credentials and set your new password';
            document.getElementById('show-password-form').reset();
            document.getElementById('show-password-message').style.display = 'none';
        }

        // Switch link click handler
        document.getElementById('switch-to-view-link').addEventListener('click', function(e) {
            e.preventDefault();
            switchToViewPasswordMode();
        });
    </script>
</body>
</html>