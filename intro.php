<!DOCTYPE html>
<html lang="en">
<head>
    <title>Club Management Loader</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to external CSS -->
</head>
<body>
    <div class="pre-loader">
        <div class="loader-icon"></div>
        <div class="loader-text">Club Management System</div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            setTimeout(function () {
                document.querySelector(".pre-loader").classList.add("hidden");
                setTimeout(() => {
                    window.location.href = "index.php"; // Change this to the desired page
                }, 1000);
            }, 3000); // Adjust time in milliseconds (3000ms = 3 seconds)
        });
    </script>
</body>
</html>
