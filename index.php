<?php
    include("src/config.php");
    session_start();
    if(isset($_SESSION["id"]) || isset($_COOKIE["id"])){
        header("Location: public/Admin/home.php");
    }

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $email = htmlspecialchars($_POST["email"]) ?? "";
        $password = htmlspecialchars($_POST["password"] ?? "");
        $remember = $_POST["remember"] ?? "";

        if(empty($email) || empty($password)){
            echo "<script>alert('Data yang di input belum lengkap');</script>";
        }


        $query = "SELECT * FROM t_admin WHERE f_email = '$email' AND f_password = '$password'";
        $result = mysqli_query($conn,$query);
        $row = $result->fetch_assoc();
        if($row > 0){
            $_SESSION["id"] = $row["f_id"];
            $username = $row["f_username"];
            
            if(!empty($remember)){
                setcookie("id",$_SESSION["id"],time()+3600, "/");
            }
            echo "<script>alert('Berhasil login ". $username ."');document.location.href='public/admin/home.php';</script>";
            exit;
        }else{
            echo "<script>alert('Email / Password salah');</script>";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
</head>
<body>
    <section class="bg-blue-300">
    <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
        <a href="#" class="flex items-center mb-6 text-2xl font-semibold text-gray-900">
            <img class="w-8 h-8 mr-2" src="https://flowbite.s3.amazonaws.com/blocks/marketing-ui/logo.svg" alt="logo">
            Kasir 71    
        </a>
        <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0">
            <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl">
                    Sign in to your account
                </h1>
                <form class="space-y-4 md:space-y-6" method="post">
                    <div class="relative">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 16">
                                <path d="m10.036 8.278 9.258-7.79A1.979 1.979 0 0 0 18 0H2A1.987 1.987 0 0 0 .641.541l9.395 7.737Z"/>
                                <path d="M11.241 9.817c-.36.275-.801.425-1.255.427-.428 0-.845-.138-1.187-.395L0 2.6V14a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V2.5l-8.759 7.317Z"/>
                            </svg>
                            </div>
                            <input type="text" name="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 " placeholder="name@flowbite.com">
                        </div>
                        <div class="flex relative">
                            <span class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17 10h-1V7a5 5 0 0 0-10 0v3H5a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2Zm-8-3a3 3 0 1 1 6 0v3H9Zm8 13H5v-8h12ZM12 14a1.5 1.5 0 0 1 1.5 1.5c0 .6-.34 1.1-.85 1.35v1.15a.65.65 0 1 1-1.3 0v-1.15a1.5 1.5 0 0 1 .65-2.85Z"/>
                            </svg>
                            </span>
                            <input type="password" id="password" name="password" class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5" placeholder="Masukkan password">
                            <button type="button" id="togglePassword" class="absolute inset-y-0 right-2 flex items-center px-2 text-gray-500">
                                <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 3C5.455 3 1.733 6.403.625 10c1.108 3.597 4.83 7 9.375 7s8.267-3.403 9.375-7C18.267 6.403 14.545 3 10 3zm0 12c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6zm0-2a4 4 0 110-8 4 4 0 010 8z"/>
                                </svg>
                            </button>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input name="remember" aria-describedby="remember" type="checkbox" class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-primary-300">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="remember" class="text-gray-500 ">Remember me</label>
                                </div>
                            </div>
                            <a href="forgotpw.php" class="text-sm font-medium text-primary-600 hover:underline">Forgot password?</a>
                        </div>
                        <a>
                            <button type="submit" class="w-full text-black bg-blue-300 hover:bg-blue-500 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Sign in</button>
                        </a>
                        <!-- <p class="text-sm font-light text-gray-500">
                            Don’t have an account yet? <a href="#" class="font-medium text-primary-600 hover:underline">Sign up</a>
                        </p> -->
                    </div>
                </form>
            </div>
        </div>
    </div>
    </section>
    
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.setAttribute("d", "M10 3C5.455 3 1.733 6.403.625 10c1.108 3.597 4.83 7 9.375 7s8.267-3.403 9.375-7C18.267 6.403 14.545 3 10 3zm0 12c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6zm0-2a4 4 0 110-8 4 4 0 010 8z");
            } else {
                passwordField.type = "password";
                eyeIcon.setAttribute("d", "M2.458 10c1.292-3.228 4.684-6 7.542-6s6.25 2.772 7.542 6c-1.292 3.228-4.684 6-7.542 6s-6.25-2.772-7.542-6zM10 8a2 2 0 110 4 2 2 0 010-4z");
            }
        });

    </script>
</body>
</html>