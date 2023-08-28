<?php

    require_once("db_connect.php");
    $request_method = $_SERVER["REQUEST_METHOD"];

    switch ($request_method) {
        case 'GET':
            if (!empty($_GET["id"])) {
                $id = intval($_GET["id"]); 
                getCompetences($id);
            } else {
                getCompetences();
            }     
        break;

        case 'POST':
            
        break;
        
        default:
            header("HTTP/1.0 405 Method Not Allowed");
        break;
    }

    function getCompetences($id = null){
        $conn = getConnexion();
        if(isset($_GET['items'])){
            $items = $_GET['items'];
        }else{
            $items = 5;
        }
        if(isset($_GET['page'])){
            $page = ($_GET['page']-1)*$items;;
        }else{
            $page = 0;
        }
        $queryCount = "SELECT COUNT(*) AS total FROM competences";
        $stmtCount = $conn->prepare($queryCount);
        $stmtCount->execute();
        $totalCount = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
        $stmtCount->closeCursor();
        // Calculer le nombre maximal de pages
        $pageMax = ceil($totalCount / $items);

        if ($id === null) {
            $query = "SELECT c.id, c.sous_categorie, c.details, f.path_fichier, ca.libelle
                      FROM competences c
                      INNER JOIN fichier f ON c.id_fichier = f.id
                      INNER JOIN categories ca ON c.id_categorie = ca.id
                      LIMIT :items 
                      OFFSET :page";
        } else {   
            $query = "SELECT c.id, c.sous_categorie, c.details, f.path_fichier, ca.libelle
                      FROM competences c
                      INNER JOIN fichier f ON c.id_fichier = f.id
                      INNER JOIN categories ca ON c.id_categorie = ca.id 
                      WHERE c.id = :id";
        }
        $stmt = $conn->prepare($query);
        if (isset($id)) {
            $stmt->bindParam(':id', $id);
        }else{
            if (isset($items)) {
                $stmt->bindParam(':items', $items, PDO::PARAM_INT);
            }
            if (isset($page)) {
                $stmt->bindParam(':page', $page, PDO::PARAM_INT);
            }
        }
        $stmt->execute();
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        $pagination = [];

        $pagination['totalpage'] = $pageMax;
        if($page > 0){
            $pagination['pageprevious'] = $page / $items;
        }
        if($page < ($pageMax - 1) * $items){
            $pagination['pagenext'] = ($page / $items) + 2;
        }

        $result = [
            'data' => $response,
            'pagination' => $pagination
        ];
        sendJSON($result);
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