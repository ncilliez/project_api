<?php

    require_once("db_connect.php");
    $request_method = $_SERVER["REQUEST_METHOD"];

    switch ($request_method) {
        case 'GET':
            if (!empty($_GET["id"])) {
                $id = intval($_GET["id"]); 
                getCv($id);
            } else {
                getCv();
            }     
        break;

        case 'POST':
            
        break;
        
        default:
            header("HTTP/1.0 405 Method Not Allowed");
        break;
    }

    function getCv($id = null){
        $conn = getConnexion();
        if ($id === null) {
            $query = "SELECT c.id, f.path_fichier 
            FROM cv_table c 
            INNER JOIN fichier f ON c.id_fichier = f.id";
        } else {   
            $query = "SELECT c.id, f.path_fichier 
                      FROM cv_table c 
                      INNER JOIN fichier f ON c.id_fichier = f.id
                      WHERE c.id = :id";
        }
        $stmt = $conn->prepare($query);
        if (isset($id)) {
            $stmt->bindParam(':id', $id);
        }
        $stmt->execute();
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        sendJSON($response);
    }

    function sendJSON($result){
        header("Access-Control-Allow-origin: *");
        header("Content-Type: application/json; charset= UTF-8");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, X-Token-Auth, Authorization");
        header("Access-Control-Allow-Credentials: true");
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
         // echo json_encode($result, JSON_PRETTY_PRINT);
    }
    
?>