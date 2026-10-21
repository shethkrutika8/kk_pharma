<?php
session_start();
if(!isset($_SESSION['users']))
    {
        ?>
        <script>
            window.location.href="login.php";
        </script>
        <?php

    }