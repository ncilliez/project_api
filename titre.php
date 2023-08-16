<?php

require_once("db_connect.php");
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':     
        getTitre();
    break;

    case 'POST':
       
    break;
    
    default:
        header("HTTP/1.0 405 Method Not Allowed");
    break;
}

function getTitre(){
    $conn = getConnexion();

    $query = "SELECT t.id, t.titre, f.path_fichier FROM titre t INNER JOIN fichier f ON t.id_icone = f.id";
    $stmt = $conn->prepare($query);
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
}

?>
