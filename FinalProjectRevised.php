#!/usr/local/bin/php
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Kendrick's Website</title>
    <style type="text/css">

        html {
            margin: 0px;
            padding: 0px;
        }

        body {
            background: transparent url(http://demo.smooththemes.com/sonec/wp-content/uploads/2013/10/slider1.jpg) no-repeat center center fixed;
            margin: 0px;
            padding: 0px;
        }

        #userInformation, #createAccount {
            position: relative;
            width: 200px;
        }

        #gameboard {
            position: absolute;
            float: right;
            top: 10px;
            /*
            bottom: 310px;
            */
            left: 400px;
            /*height: 600px;
            width: 600px;*/
            border: solid black;
        }

    </style>
</head>

<body>
    <p id="atTop">
    </p>
    
    <form method="post" action="<? $_SERVER['PHP_SELF']; ?>" id="userInformation">
        <fieldset>
            Username:<input type="text" name="username">
            Password:<input type="password" name="password"></br>
            <input type="submit" value="Login">
        </fieldset>
    </form>
    <?
        //after pressing the login button, try to access the database
        try{
            $finalDatabase = new SQLite3('final_project.db');
        }
        catch(Exception $e) {
            echo "Error in connecting to database.  Message below: </br>";
            echo $e->getMessage();
            die("Script has terminated.");
        }

        //database variables
        $table = "userDatabase";
        $field1 = "username";
        $field2 = "password";
        //$field3 = "highscore";

        $val1 = trim($_POST['username']);
        $val2 = trim($_POST['password']);

        //if table doesn't exist, create the table
        $cmd = "CREATE TABLE IF NOT EXISTS " . $table . " ( " . $field1 . " TEXT, " . $field2 . " REAL, " . $field3 . " INTEGER ) ";
        $finalDatabase->query($cmd);

        //go through the database and check if the username + password combination is in it
        //$cmd = "SELECT * FROM " . $userDatabase . "WHERE " . $field1 . " = " . $val1 . " && " . $field2 . " = " . $val2;
        //$statement = $finalDatabase->prepare('SELECT * FROM userDatabase WHERE username = :username;');
        $statement = $finalDatabase->prepare('SELECT * FROM userDatabase WHERE username = :username AND password = :password;');
        $statement->bindValue(':username', $val1);
        $statement->bindValue(':password', $val2);
        $result = $statement->execute();

        //if the username + password combination is found, print out login successful and welcome message
        if($record = $result->fetchArray()) {
            echo "Login successful.";
            echo "<h2>Hi $val1!</h2>";
        }
        //else, failed login
        else {
            echo "Incorrect username/password.";
        }

        //close the database when done
        $finalDatabase->close();
    ?>
    <form method="post" action="<? $_SERVER['PHP_SELF']; ?>" id="createAccount">
        <fieldset>
            Username:<input type="text" name="newUsername">
            Password:<input type="password" name="newPassword"></br>
            <input type="button" value="Create Account">
        </fieldset>
    <?
        //try to open the database after pressing create account
        try{
            $finalDatabase = new SQLite3('final_project.db');
        }
        catch(Exception $e) {
            echo "Error in connecting to database.  Message below: </br>";
            echo $e->getMessage();
            die("Script has terminated.");
        }

        //database variables
        $table = "userDatabase";
        $field1 = "username";
        $field2 = "password";
        //$field3 = "highscore";

        $val1 = trim($_POST['username']);
        $val2 = trim($_POST['password']);

        //create the table of username and passwords if it doesnt already exist
        $cmd = "CREATE TABLE IF NOT EXISTS " . $table . " ( " . $field1 . " TEXT, " . $field2 . " REAL, " . $field3 . " INTEGER ) ";
        $finalDatabase->query($cmd);

        //since create account is pressed, add entry into the database
        $finalDatabase->query("INSERT INTO userDatabase(username, password) VALUES ('$val1', '$val2');");

        //$cmd = "SELECT * FROM " . $userDatabase . "WHERE " . $field1 . " = " . $val1 . " && " . $field2 . " = " . $val2;
        //$statement = $finalDatabase->prepare('SELECT * FROM userDatabase WHERE username = :username;');
        
        // $statement = $finalDatabase->prepare('SELECT * FROM userDatabase WHERE username = :username AND password = :password;');
        // $statement->bindValue(':username', $val1);
        // $statement->bindValue(':password', $val2);
        // $result = $statement->execute();

        // if($record = $result->fetchArray()) {
        //     echo "Login successful.";
        // }
        // else {
        //     echo "Incorrect username/password.";
        // }

        //close the database
        $finalDatabase->close();
    ?>

    </form>

        <input type="button" onclick="play()" value="Play">
        <input type="button" onclick="quit()" value="Quit">

    <canvas id="gameboard" width=500 height=500></canvas>

    <p id="score">
    </p>

    <p id="gameStatus">
    </p>

<script>
    //canvas variables
    var canvas = document.getElementById("gameboard");
    var ctx = canvas.getContext("2d");

    // game variables
    var startingScore = 0;
    var continueAnimating = false;
    var score;

    // block variables
    var blockWidth = 20;
    var blockHeight = 20;
    var blockSpeed = 10;
    var block = {
        x: 0,
        y: canvas.height - blockHeight,
        width: blockWidth,
        height: blockHeight,
        blockSpeed: blockSpeed
    }

    // enemy variables
    var enemyWidth = 25;
    var enemyHeight = 25;
    var totalEnemies = 15;
    var enemies = [];
    for (var i = 0; i < totalEnemies; i++) {
        addEnemy();
    }

    function addEnemy() {
        var enemy = {
            width: enemyWidth,
            height: enemyHeight
        }
        resetEnemy(enemy);
        enemies.push(enemy);
    }

    // move the enemy to a random position near the top-of-canvas
    // assign the enemy a random speed
    function resetEnemy(enemy) {
        enemy.x = Math.random() * (canvas.width - enemyWidth);
        enemy.y = 15 + Math.random() * 30;
        enemy.speed = 0.8 + Math.random() * 1.5;
    }


    //left and right keypush event handlers
    function onKeyDown (event) {
        if (event.keyCode == 39) {
            block.x += block.blockSpeed;
            if (block.x >= canvas.width - block.width) {
                block.x = canvas.width - block.width;
            }
        } else if (event.keyCode == 37) {
            block.x -= block.blockSpeed;
            if (block.x <= 0) {
                block.x = 0;
            }
        }
    }


    function animate() {

        // request another animation frame

        if (continueAnimating) {
            requestAnimationFrame(animate);
        }

        // for each enemy
        // (1) check for collisions
        // (2) advance the enemy
        // (3) if the enemy falls below the canvas, reset that enemy

        for (var i = 0; i < enemies.length; i++) {

            var enemy = enemies[i];

            // test for enemy-block collision
            if (isColliding(enemy, block)) {
                // score -= 10;
                gameover();
            }

            // advance the enemy
            enemy.y += enemy.speed;

            // if the enemy is below the canvas,
            if (enemy.y > canvas.height) {
                resetEnemy(enemy);
            }

        }

        // redraw everything
        drawAll();

    }

    function gameover() {
        clearInterval(interval);
        window.removeEventListener("keydown", onKeyDown, true);
        document.getElementById("gameStatus").innerHTML = "<b>GAME OVER!</b>"

    }

    function isColliding(a, b) {
        return !(
        b.x > a.x + a.width || b.x + b.width < a.x || b.y > a.y + a.height || b.y + b.height < a.y);
    }

    function drawAll() {

        // clear the canvas
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // draw the background
        ctx.fillStyle = "ivory";
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // draw the block
        ctx.fillStyle = "skyblue";
        ctx.fillRect(block.x, block.y, block.width, block.height);
        ctx.strokeStyle = "lightgray";
        ctx.strokeRect(block.x, block.y, block.width, block.height);

        // draw all enemies
        for (var i = 0; i < enemies.length; i++) {
            var enemy = enemies[i];
            ctx.fillStyle = "red";
            ctx.fillRect(enemy.x, enemy.y, enemy.width, enemy.height);
        }
    }

    // button to start the game
    function play() {
        score = startingScore;
        block.x = 250;
        scorePoints();
        interval = setInterval(scorePoints, 200);
        for (var i = 0; i < enemies.length; i++) {
            resetEnemy(enemies[i]);
        }
        if (!continueAnimating) {
            continueAnimating = true;
            animate();
        }

        window.addEventListener("keydown", onKeyDown, true);

        document.getElementById("gameStatus").innerHTML = "";
    }

    function scorePoints() {
        score += 10;
        document.getElementById("score").innerHTML = "Score: " + score;
    }

    function quit() {
        gameover();
    }

     
</script>
</body>


</html>