<?php

    function getConnexion(){
        return new PDO("mysql:host=localhost;dbname=cv;charset=utf8","root","");
    }

?>