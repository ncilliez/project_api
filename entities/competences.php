<?php

    require_once("../db_connect.php");
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

    function getCompetences($id = null) {
        $conn = getConnexion();
        $search = isset($_GET["search"]) ? $_GET["search"] : null;
    
        $items = isset($_GET['items']) ? max(1, intval($_GET['items'])) : 5;
        $page = isset($_GET['page']) ? max(0, intval($_GET['page']) - 1) * $items : 0;
    
        $queryCount = "SELECT COUNT(*) AS total FROM competences";
        $stmtCount = $conn->prepare($queryCount);
        $stmtCount->execute();
        $totalCount = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
        $stmtCount->closeCursor();
    
        $query = "SELECT c.id, p.nom, ca.libelle, c.sous_categorie, c.image_sous_categorie, c.details
                  FROM competences c
                  INNER JOIN categories ca ON c.id_categorie = ca.id
                  INNER JOIN profile p ON c.id_profile = p.id";
    
        if ($id === null) {
            if ($search !== null) {
                $query .= " WHERE sous_categorie LIKE :search OR details LIKE :search";
            }   
            $query .= " LIMIT :items OFFSET :page";
        } else {   
            $query .= " WHERE c.id = :id";
        }
        
        $stmt = $conn->prepare($query);
    
        if (isset($id)) {
            $stmt->bindParam(':id', $id);
        } else {
            $stmt->bindParam(':items', $items, PDO::PARAM_INT);
            $stmt->bindParam(':page', $page, PDO::PARAM_INT);
        }
    
        if ($search !== null) {
            $searchTerm = "%" . $search . "%";
            $stmt->bindParam(':search', $searchTerm);
        }
        
        $stmt->execute();
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
    
        $pageMax = ceil($totalCount/ $items);
        $pagination = [
            'totalPage' => $pageMax,
        ];
        
        if ($page > 0) {
            $pagination['pageprevious'] = $page / $items;
        }
        
        if ($page < ($pageMax - 1) * $items) {
            $pagination['pagenext'] = ($page / $items) + 2;
        }
    
        $result = [
            'data' => $response,
            'pagination' => $pagination,
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