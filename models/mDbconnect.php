<?php
    class clsConnect{
        public function connect(){
            $host = "localhost";
            $user = "root";
            $pass = "";
            $db = "webhenho";
            return mysqli_connect($host, $user, $pass, $db);
        }
        public function disconnect($conn){
            $conn->close();
        }
    }

?>